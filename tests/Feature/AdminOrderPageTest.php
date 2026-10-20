<?php

namespace Tests\Feature;

use App\Jobs\SendOrderEmail;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Services\OrderEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminOrderPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['admin.authenticated' => true]);
    }

    public function test_admin_order_tracker_renders(): void
    {
        Order::factory()->create([
            'reference' => 'TB-20261017-LIVE',
            'customer_name' => 'Ana Santos',
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertViewIs('admin.orders')
            ->assertSee('Order command center')
            ->assertSee('Track every Bellyssimo order')
            ->assertSee('What needs your attention today')
            ->assertSee('dashboard-grid overview-insights', false)
            ->assertSee('Customers for future reference')
            ->assertSee('Track kitchen items and supplies')
            ->assertSee('Revenue and order performance')
            ->assertSee('New order')
            ->assertSee('Pending confirmation')
            ->assertSee('Confirmed')
            ->assertSee('Completed')
            ->assertSee('Cancelled')
            ->assertSee('Delete order')
            ->assertSee('Down payment amount')
            ->assertSee('Rider hand-off')
            ->assertSee('Copy rider details')
            ->assertDontSee('data-inspector-tab', false)
            ->assertSee('Delete customer details?')
            ->assertSee('Live order feed')
            ->assertSee('TB-20261017-LIVE')
            ->assertSee('Ana Santos')
            ->assertSee(route('admin.schedule'), false)
            ->assertSee(asset('css/admin-orders.css'), false)
            ->assertSee(asset('js/admin-orders.js'), false);
    }

    public function test_admin_can_delete_an_order_without_deleting_the_customer_record(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Ana Santos',
            'contact_number' => '+639171234567',
        ]);
        $order = Order::factory()->create([
            'reference' => 'TB-DELETE-ME',
            'customer_name' => $customer->name,
            'contact_number' => $customer->contact_number,
        ]);

        $this->deleteJson(route('admin.orders.destroy', $order))
            ->assertOk()
            ->assertJsonPath('message', 'Order TB-DELETE-ME was deleted.');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_admin_can_delete_customer_details_without_deleting_order_history(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Ana Santos',
            'contact_number' => '+639171234567',
        ]);
        $order = Order::factory()->create([
            'customer_name' => $customer->name,
            'contact_number' => $customer->contact_number,
        ]);

        $this->deleteJson(route('admin.customers.destroy', $customer))
            ->assertOk()
            ->assertJsonPath('message', 'Customer details for Ana Santos were deleted.');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Ana Santos',
            'contact_number' => '+639171234567',
        ]);
    }

    public function test_roasting_schedule_displays_orders_in_the_selected_month(): void
    {
        $scheduledOrder = Order::factory()->create([
            'customer_name' => 'October Celebration',
            'preferred_date' => now()->startOfMonth()->addDays(20)->toDateString(),
            'preferred_time' => '11:30',
            'quantity' => 2,
        ]);
        Order::factory()->create([
            'customer_name' => 'November Celebration',
            'preferred_date' => now()->addMonth()->startOfMonth()->toDateString(),
        ]);
        $declinedOrder = Order::factory()->create([
            'customer_name' => 'Declined Celebration',
            'preferred_date' => now()->startOfMonth()->addDays(21)->toDateString(),
            'status' => 'Declined',
        ]);
        $cancelledOrder = Order::factory()->create([
            'customer_name' => 'Cancelled Celebration',
            'preferred_date' => now()->startOfMonth()->addDays(22)->toDateString(),
            'status' => 'Cancelled',
        ]);

        $response = $this->get(route('admin.schedule', [
            'month' => now()->format('Y-m'),
        ]));

        $response
            ->assertOk()
            ->assertViewIs('admin.schedule')
            ->assertSee('Roasting schedule')
            ->assertSee('Monthly production plan')
            ->assertSee('October Celebration')
            ->assertSee($scheduledOrder->reference)
            ->assertDontSee('November Celebration')
            ->assertDontSee($declinedOrder->reference)
            ->assertDontSee($cancelledOrder->reference)
            ->assertViewHas('totalOrders', 1)
            ->assertSee(asset('css/admin-schedule.css'), false)
            ->assertSee(asset('js/admin-schedule.js'), false);
    }

    public function test_admin_can_update_an_order_status(): void
    {
        $order = Order::factory()->create();

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Confirmed',
        ])->assertOk()
            ->assertJsonPath('order.status', 'Confirmed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Confirmed',
        ]);
    }

    public function test_admin_can_edit_customer_order_details_without_changing_items_or_total(): void
    {
        $order = Order::factory()->create([
            'customer_name' => 'Old Customer',
            'contact_number' => '+639171111111',
            'email' => 'old@example.com',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '10:00',
            'fulfillment' => 'Pickup',
            'delivery_location' => null,
            'payment_method' => 'Cash',
            'notes' => null,
            'order_total' => 2550,
        ]);
        $originalItems = $order->items;

        $this->patchJson(route('admin.orders.update', $order), [
            'customer' => 'Ana Santos',
            'phone' => '09181234567',
            'email' => 'ANA@EXAMPLE.COM',
            'schedule' => now()->addDays(3)->format('Y-m-d\T15:30'),
            'fulfillment' => 'Delivery',
            'delivery_location' => 'Carmen Annex, Ozamiz City',
            'payment_method' => 'GCash',
            'note' => 'Call before delivery.',
        ])->assertOk()
            ->assertJsonPath('message', 'Order details updated.')
            ->assertJsonPath('order.customer', 'Ana Santos')
            ->assertJsonPath('order.phone', '+639181234567')
            ->assertJsonPath('order.email', 'ana@example.com')
            ->assertJsonPath('order.fulfillment_method', 'Delivery')
            ->assertJsonPath('order.delivery_location', 'Carmen Annex, Ozamiz City')
            ->assertJsonPath('order.payment_method', 'GCash')
            ->assertJsonPath('order.total', 2550);

        $order->refresh();
        $this->assertSame($originalItems, $order->items);
        $this->assertSame(2550, $order->order_total);
        $this->assertDatabaseHas('customers', [
            'name' => 'Ana Santos',
            'contact_number' => '+639181234567',
            'email' => 'ana@example.com',
        ]);
    }

    public function test_editing_an_order_requires_a_delivery_location_for_delivery(): void
    {
        $order = Order::factory()->create();

        $this->patchJson(route('admin.orders.update', $order), [
            'customer' => 'Ana Santos',
            'phone' => '09181234567',
            'email' => null,
            'schedule' => now()->addDay()->format('Y-m-d\TH:i'),
            'fulfillment' => 'Delivery',
            'delivery_location' => '',
            'payment_method' => 'Cash',
            'note' => null,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('delivery_location');
    }

    public function test_status_change_queues_a_customer_email_when_an_address_is_available(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'email' => 'ana@example.com',
            'status' => 'Pending confirmation',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Confirmed',
        ])->assertOk();

        Queue::assertPushed(SendOrderEmail::class, 1);
        Queue::assertPushed(SendOrderEmail::class, function (SendOrderEmail $job) use ($order): bool {
            return $job->orderId === $order->getKey()
                && $job->kind === OrderEmailService::CONFIRMED
                && $job->status === 'Confirmed'
                && str_starts_with($job->idempotencyKey, 'order-status-'.$order->reference.'-');
        });
    }

    public function test_admin_can_decline_an_order_and_queue_a_customer_email(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'email' => 'ana@example.com',
            'status' => 'Pending confirmation',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Declined',
        ])->assertOk()
            ->assertJsonPath('order.status', 'Declined');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Declined',
        ]);
        Queue::assertPushed(SendOrderEmail::class, function (SendOrderEmail $job) use ($order): bool {
            return $job->orderId === $order->getKey()
                && $job->kind === OrderEmailService::DECLINED
                && $job->status === 'Declined';
        });
    }

    public function test_confirming_a_gcash_order_queues_confirmation_and_payment_emails(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'email' => 'ana@example.com',
            'status' => 'Pending confirmation',
            'payment_method' => 'GCash',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Confirmed',
        ])->assertOk();

        Queue::assertPushed(SendOrderEmail::class, 2);
        Queue::assertPushed(SendOrderEmail::class, fn (SendOrderEmail $job): bool => $job->kind === OrderEmailService::CONFIRMED);
        Queue::assertPushed(SendOrderEmail::class, fn (SendOrderEmail $job): bool => $job->kind === OrderEmailService::GCASH_PAYMENT);
    }

    public function test_other_status_changes_do_not_queue_a_customer_email(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'email' => 'ana@example.com',
            'status' => 'Confirmed',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Cancelled',
        ])->assertOk();

        Queue::assertNotPushed(SendOrderEmail::class);
    }

    public function test_unchanged_status_does_not_queue_a_customer_email(): void
    {
        Queue::fake();
        $order = Order::factory()->create([
            'email' => 'ana@example.com',
            'status' => 'Confirmed',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Confirmed',
        ])->assertOk();

        Queue::assertNotPushed(SendOrderEmail::class);
    }

    public function test_completing_an_order_automatically_marks_it_paid(): void
    {
        $order = Order::factory()->create([
            'status' => 'Confirmed',
            'payment_status' => 'Unpaid',
        ]);

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'Completed',
        ])->assertOk()
            ->assertJsonPath('order.status', 'Completed')
            ->assertJsonPath('order.payment', 'Paid');

        $order->refresh();

        $this->assertSame('Completed', $order->status);
        $this->assertSame('Paid', $order->payment_status);
        $this->assertSame(2000, $order->amount_paid);
        $this->assertNotNull($order->completed_at);
    }

    public function test_admin_can_record_a_down_payment_and_see_the_remaining_balance(): void
    {
        $order = Order::factory()->create([
            'order_total' => 2000,
            'amount_paid' => 0,
            'payment_status' => 'Unpaid',
        ]);

        $this->patchJson(route('admin.orders.payment', $order), [
            'amount_paid' => 750,
        ])->assertOk()
            ->assertJsonPath('message', 'Payment amount saved.')
            ->assertJsonPath('order.amount_paid', 750)
            ->assertJsonPath('order.remaining_balance', 1250)
            ->assertJsonPath('order.payment', 'Deposit received');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'amount_paid' => 750,
            'payment_status' => 'Deposit received',
        ]);
    }

    public function test_down_payment_cannot_exceed_the_order_total(): void
    {
        $order = Order::factory()->create([
            'order_total' => 2000,
            'amount_paid' => 0,
        ]);

        $this->patchJson(route('admin.orders.payment', $order), [
            'amount_paid' => 2001,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('amount_paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'amount_paid' => 0,
        ]);
    }

    public function test_completed_order_payment_cannot_be_reduced(): void
    {
        $order = Order::factory()->create([
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'order_total' => 2000,
            'amount_paid' => 2000,
            'completed_at' => now(),
        ]);

        $this->patchJson(route('admin.orders.payment', $order), [
            'amount_paid' => 750,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('amount_paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'amount_paid' => 2000,
            'payment_status' => 'Paid',
        ]);
    }

    public function test_legacy_progress_statuses_are_rejected(): void
    {
        $order = Order::factory()->create();

        $this->patchJson(route('admin.orders.status', $order), [
            'status' => 'In preparation',
        ])->assertUnprocessable();
    }

    public function test_admin_can_add_a_manual_order(): void
    {
        Queue::fake();
        $response = $this->postJson(route('admin.orders.store'), [
            'customer' => 'Mario Cruz',
            'phone' => '09171234567',
            'email' => 'Mario@Example.com',
            'schedule' => now()->addDay()->format('Y-m-d\TH:i'),
            'package' => 'salo-salo',
            'flavor' => 'Spicy',
            'fulfillment' => 'Delivery',
            'delivery_location' => 'Carmen Annex, Ozamiz City',
            'payment' => 'Deposit received',
            'payment_method' => 'GCash',
            'note' => 'Call before delivery.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('order.customer', 'Mario Cruz')
            ->assertJsonPath('order.total', 2600)
            ->assertJsonPath('order.payment_method', 'GCash')
            ->assertJsonPath('order.status', 'Pending confirmation');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Mario Cruz',
            'order_total' => 2600,
            'delivery_location' => 'Carmen Annex, Ozamiz City',
            'payment_method' => 'GCash',
            'contact_number' => '+639171234567',
            'email' => 'mario@example.com',
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Mario Cruz',
            'contact_number' => '+639171234567',
            'email' => 'mario@example.com',
        ]);
        Queue::assertPushed(SendOrderEmail::class, fn (SendOrderEmail $job): bool => $job->kind === OrderEmailService::CONFIRMATION);
    }

    public function test_customers_with_the_same_contact_number_are_recorded_separately_by_name(): void
    {
        $baseOrder = [
            'phone' => '09181234567',
            'schedule' => now()->addDay()->format('Y-m-d\TH:i'),
            'package' => 'barkadahan',
            'flavor' => 'Regular',
            'fulfillment' => 'Pickup',
            'payment' => 'Unpaid',
            'payment_method' => 'Cash',
        ];

        $this->postJson(route('admin.orders.store'), [
            ...$baseOrder,
            'customer' => 'Juan Dela Cruz',
        ])->assertCreated();

        $this->postJson(route('admin.orders.store'), [
            ...$baseOrder,
            'customer' => 'Maria Santos',
        ])->assertCreated();

        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseHas('customers', [
            'name' => 'Juan Dela Cruz',
            'contact_number' => '+639181234567',
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Maria Santos',
            'contact_number' => '+639181234567',
        ]);

        $this->get(route('admin.dashboard').'#customers')
            ->assertOk()
            ->assertSee('Juan Dela Cruz')
            ->assertSee('Maria Santos');
    }

    public function test_customer_booking_appears_in_admin_dashboard(): void
    {
        $this->post(route('order.confirm'), [
            'items' => [
                ['package' => 'barkadahan', 'flavor' => 'Spicy'],
                ['package' => 'handaan', 'flavor' => 'Regular'],
            ],
            'fulfillment' => 'Pickup',
            'delivery_location' => '',
            'payment_method' => 'Cash',
            'first_name' => 'Customer',
            'last_name' => 'Booking Test',
            'contact_number' => '9171234567',
            'email' => 'customer@example.com',
            'preferred_date' => now('Asia/Manila')->addDays(3)->toDateString(),
            'preferred_time' => '12:00',
            'notes' => 'Mixed package order.',
        ]);

        $this->post(route('order.place'))
            ->assertRedirect(route('order.thank-you'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Customer Booking Test')
            ->assertSee('2 bellies')
            ->assertViewHas('orders', function ($orders): bool {
                return $orders->first()['flavor'] === 'Mixed · 1 spicy, 1 regular'
                    && $orders->first()['payment_method'] === 'Cash';
            });

        $this->assertDatabaseHas('customers', [
            'name' => 'Customer Booking Test',
            'contact_number' => '+639171234567',
        ]);
    }

    public function test_admin_can_add_and_update_an_inventory_item(): void
    {
        $response = $this->postJson(route('admin.inventory.store'), [
            'name' => 'Pork belly',
            'category' => 'Ingredient',
            'quantity' => 24,
            'unit' => 'kg',
            'low_stock_threshold' => 8,
            'unit_cost' => 320,
            'notes' => 'Cold storage',
        ]);

        $response->assertCreated()
            ->assertJsonPath('item.name', 'Pork belly');

        $item = InventoryItem::query()->firstOrFail();

        $this->patchJson(route('admin.inventory.update', $item), [
            'name' => 'Pork belly',
            'category' => 'Ingredient',
            'quantity' => 6,
            'unit' => 'kg',
            'low_stock_threshold' => 8,
            'unit_cost' => 325,
            'notes' => 'Reorder this week',
        ])->assertOk()
            ->assertJsonPath('item.quantity', '6.00');

        $this->get(route('admin.dashboard').'#inventory')
            ->assertOk()
            ->assertSee('Pork belly')
            ->assertSee('Low stock');
    }

    public function test_reports_count_only_completed_and_paid_orders_as_revenue(): void
    {
        Order::factory()->create([
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'order_total' => 2950,
            'completed_at' => now(),
        ]);
        Order::factory()->create([
            'status' => 'Pending confirmation',
            'payment_status' => 'Paid',
            'order_total' => 2550,
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('dashboard', function (array $dashboard): bool {
                return $dashboard['total_revenue'] === 2950
                    && $dashboard['completed'] === 1
                    && $dashboard['pending'] === 1;
            });
    }

    public function test_customer_directory_uses_persistent_customer_records(): void
    {
        Customer::factory()->create([
            'name' => 'Repeat Customer',
            'contact_number' => '+639762271228',
            'email' => 'repeat@example.com',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Email address')
            ->assertSee('Repeat Customer')
            ->assertSee('+639762271228')
            ->assertSee('repeat@example.com');
    }
}
