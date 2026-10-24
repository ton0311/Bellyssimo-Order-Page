<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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
}
