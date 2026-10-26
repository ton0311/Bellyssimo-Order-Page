<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderEmailDelivery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->withSession([
            'admin.authenticated' => true,
            'admin.login' => 'operations-admin',
        ]);
    }

    public function test_admin_status_and_payment_changes_are_audited(): void
    {
        $order = Order::factory()->create([
            'status' => 'Pending confirmation',
            'payment_status' => 'Unpaid',
            'amount_paid' => 0,
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Confirmed',
        ])->assertOk();
        $this->patchJson(route('admin.orders.payment', $order), [
            'amount_paid' => 500,
        ])->assertOk();

        $this->assertDatabaseHas('order_audits', [
            'order_id' => $order->getKey(),
            'order_reference' => $order->reference,
            'event' => 'status_updated',
            'actor_type' => 'admin',
            'actor_identifier' => 'operations-admin',
        ]);
        $this->assertDatabaseHas('order_audits', [
            'order_id' => $order->getKey(),
            'event' => 'payment_updated',
        ]);
    }

    public function test_deleting_an_order_preserves_its_audit_history(): void
    {
        $order = Order::factory()->create();

        $this->deleteJson(route('admin.orders.destroy', $order))->assertOk();

        $this->assertDatabaseHas('order_audits', [
            'order_id' => null,
            'order_reference' => $order->reference,
            'event' => 'deleted',
        ]);
    }

    public function test_post_delete_removes_the_order_and_preserves_audit_and_email_history(): void
    {
        $order = Order::factory()->create();
        $delivery = OrderEmailDelivery::query()->create([
            'order_id' => $order->id, 'order_reference' => $order->reference,
            'email_kind' => 'confirmation', 'idempotency_key' => 'local-delete-test',
            'status' => 'delivered',
        ]);

        $this->post(route('admin.orders.destroy', $order), ['_method' => 'DELETE'], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('message', "Order {$order->reference} was deleted.");

        $this->assertModelMissing($order);
        $this->assertDatabaseHas('order_audits', ['order_reference' => $order->reference, 'event' => 'deleted', 'order_id' => null]);
        $this->assertSame(null, $delivery->fresh()->order_id);
        $this->assertSame('delivered', $delivery->fresh()->status);
    }

    public function test_failed_deletion_does_not_leave_a_false_deleted_audit_entry(): void
    {
        $order = Order::factory()->create();
        Schema::create('test_order_locks', function (Blueprint $table): void {
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
        });
        DB::table('test_order_locks')->insert(['order_id' => $order->id]);

        try {
            $this->post(route('admin.orders.destroy', $order), ['_method' => 'DELETE'], ['Accept' => 'application/json'])
                ->assertServerError();
            $this->assertModelExists($order);
            $this->assertDatabaseMissing('order_audits', ['order_reference' => $order->reference, 'event' => 'deleted']);
        } finally {
            Schema::dropIfExists('test_order_locks');
        }
    }

    public function test_applying_the_pending_audit_migration_restores_order_deletion(): void
    {
        $order = Order::factory()->create();
        $migration = require database_path('migrations/2026_10_20_032911_create_order_audits_table.php');
        $migration->down();

        try {
            $this->deleteJson(route('admin.orders.destroy', $order))->assertServerError();
            $this->assertModelExists($order);
        } finally {
            $migration->up();
        }

        $this->deleteJson(route('admin.orders.destroy', $order))->assertOk();
        $this->assertModelMissing($order);
        $this->assertDatabaseHas('order_audits', [
            'order_id' => null,
            'order_reference' => $order->reference,
            'event' => 'deleted',
        ]);
    }
}
