<?php

namespace Tests\Feature;

use App\Jobs\SendOrderEmail;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Services\OrderEmailService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
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
            ->assertSee('0976-2271-228')
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

    public function test_admin_can_replace_order_items_using_post_method_override(): void
    {
        $order = Order::factory()->create([
            'amount_paid' => 500,
            'payment_status' => 'Deposit received',
            'status' => 'Confirmed',
        ]);

        $this->post(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'salo-salo', 'flavor' => 'Spicy'],
            ['package' => 'handaan', 'flavor' => 'Regular'],
        ]) + ['_method' => 'PATCH'], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('order.total', 5550)
            ->assertJsonPath('order.amount_paid', 500)
            ->assertJsonPath('order.remaining_balance', 5050)
            ->assertJsonPath('order.status', 'Confirmed')
            ->assertJsonPath('order.items.0.package_key', 'salo-salo')
            ->assertJsonPath('order.items.0.flavor', 'Spicy')
            ->assertJsonPath('order.items.1.package_key', 'handaan');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id, 'quantity' => 2, 'package_subtotal' => 5500,
            'spicy_subtotal' => 50, 'order_total' => 5550, 'amount_paid' => 500,
        ]);
        $audit = $order->audits()->where('event', 'details_updated')->sole();
        $this->assertSame(2000, $audit->before_values['order_total']);
        $this->assertSame(5550, $audit->after_values['order_total']);
    }

    public function test_removing_items_recalculates_quantity_and_marks_an_exact_payment_paid(): void
    {
        $order = Order::factory()->create([
            'items' => array_fill(0, 2, Order::factory()->make()->items[0]),
            'quantity' => 2, 'package_subtotal' => 4000, 'order_total' => 4000,
            'amount_paid' => 2000, 'payment_status' => 'Deposit received',
        ]);

        $this->patchJson(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'barkadahan', 'flavor' => 'Regular'],
        ]))->assertOk()->assertJsonPath('order.payment', 'Paid')
            ->assertJsonPath('order.remaining_balance', 0);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'quantity' => 1, 'order_total' => 2000, 'amount_paid' => 2000]);
    }

    public function test_increasing_a_paid_order_total_preserves_the_payment_and_updates_the_balance(): void
    {
        $order = Order::factory()->create(['amount_paid' => 2000, 'payment_status' => 'Paid']);

        $this->patchJson(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'handaan', 'flavor' => 'Spicy'],
        ]))->assertOk()->assertJsonPath('order.payment', 'Deposit received')
            ->assertJsonPath('order.remaining_balance', 1000);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_total' => 3000, 'amount_paid' => 2000]);
    }

    public function test_item_edits_cannot_reduce_the_total_below_the_recorded_payment(): void
    {
        $order = Order::factory()->create(['order_total' => 3000, 'amount_paid' => 2500]);
        $original = $order->fresh()->getAttributes();
        $this->patchJson(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'barkadahan', 'flavor' => 'Spicy'],
        ]))->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertSame($original, $order->fresh()->getAttributes());
    }

    public function test_completed_orders_cannot_change_total_until_reopened(): void
    {
        $order = Order::factory()->create(['status' => 'Completed', 'payment_status' => 'Paid', 'amount_paid' => 2000]);

        $this->patchJson(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'handaan', 'flavor' => 'Regular'],
        ]))->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_total' => 2000, 'amount_paid' => 2000, 'status' => 'Completed']);
    }

    public function test_unchanged_item_selection_preserves_an_existing_custom_price(): void
    {
        $order = Order::factory()->create(['package_subtotal' => 1900, 'order_total' => 1900]);
        $originalItems = $order->items;

        $this->patchJson(route('admin.orders.update', $order), $this->itemEditData([
            ['package' => 'barkadahan', 'flavor' => 'Regular'],
        ]))->assertOk()->assertJsonPath('order.total', 1900);
        $this->assertSame($originalItems, $order->fresh()->items);
    }

    public function test_invalid_item_selections_leave_the_order_unchanged(): void
    {
        $order = Order::factory()->create();
        $original = $order->fresh()->getAttributes();
        $cases = [
            [],
            [['package' => 'fake', 'flavor' => 'Regular']],
            [['package' => 'barkadahan', 'flavor' => 'fake']],
            [['package' => 'barkadahan']],
            [['package' => 'barkadahan', 'flavor' => 'Regular', 'price' => 1]],
            array_fill(0, 21, ['package' => 'barkadahan', 'flavor' => 'Regular']),
        ];
        foreach ($cases as $items) {
            $this->patchJson(route('admin.orders.update', $order), $this->itemEditData($items))
                ->assertUnprocessable();
            $this->assertSame($original, $order->fresh()->getAttributes());
        }
    }

    /** @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function itemEditData(array $items): array
    {
        return [
            'customer' => 'Local Test Customer', 'phone' => '9171234567',
            'schedule' => '2026-11-01T12:00', 'fulfillment' => 'Pickup',
            'payment_method' => 'Cash', 'items' => $items,
        ];
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

    public function test_completing_an_order_queues_one_thank_you_email_and_repeated_saves_do_not_resend(): void
    {
        Bus::fake([SendOrderEmail::class]);
        $order = Order::factory()->create([
            'status' => 'Confirmed',
            'email' => 'customer@example.com',
        ]);

        $this->patchJson(route('admin.orders.status', $order), ['status' => 'Completed'])
            ->assertOk()->assertJsonPath('order.status', 'Completed')->assertJsonPath('order.payment', 'Paid');
        $this->patchJson(route('admin.orders.status', $order), ['status' => 'Completed'])->assertOk();

        Bus::assertDispatchedAfterResponse(SendOrderEmail::class, 1);
        Bus::assertDispatchedAfterResponse(SendOrderEmail::class, fn (SendOrderEmail $job): bool => $job->orderId === $order->id
            && $job->kind === OrderEmailService::COMPLETED
            && $job->status === 'Completed'
            && str_starts_with($job->idempotencyKey, 'order-status-'.$order->reference.'-completed-'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'Completed', 'payment_status' => 'Paid', 'amount_paid' => 2000]);
    }

    public function test_completing_an_order_without_email_does_not_queue_a_thank_you(): void
    {
        Queue::fake();
        $order = Order::factory()->create(['status' => 'Confirmed', 'email' => null]);

        $this->patchJson(route('admin.orders.status', $order), ['status' => 'Completed'])
            ->assertOk()->assertJsonPath('order.status', 'Completed');

        Queue::assertNothingPushed();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'Completed']);
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

    public function test_reports_total_recorded_collections_including_deposits_across_order_statuses(): void
    {
        $this->freezeTime();
        Order::factory()->create([
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'order_total' => 2950,
            'amount_paid' => 2950,
            'completed_at' => now(),
        ]);
        Order::factory()->create([
            'status' => 'Pending confirmation',
            'payment_status' => 'Paid',
            'order_total' => 2550,
            'amount_paid' => 2550,
        ]);
        Order::factory()->create([
            'status' => 'Confirmed',
            'payment_status' => 'Deposit received',
            'amount_paid' => 1000,
        ]);
        Order::factory()->create([
            'status' => 'Cancelled',
            'payment_status' => 'Deposit received',
            'amount_paid' => 500,
        ]);
        Order::factory()->create([
            'status' => 'Declined',
            'payment_status' => 'Deposit received',
            'amount_paid' => 300,
        ]);
        Order::factory()->create(['status' => 'Confirmed']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('dashboard', function (array $dashboard): bool {
                return $dashboard['total_revenue'] === 7300
                    && $dashboard['month_revenue'] === 7300
                    && $dashboard['monthly']->last()['revenue'] === 7300
                    && $dashboard['average_order'] === 2950
                    && $dashboard['completed'] === 1
                    && $dashboard['pending'] === 1;
            })
            ->assertSee('Total collected includes recorded deposits and full payments')
            ->assertSee('Collections by booking month');
    }

    public function test_monthly_collections_use_booking_month_in_manila_without_month_end_overflow(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-31 12:00:00', 'Asia/Manila'));
        Order::factory()->create([
            'amount_paid' => 1000,
            'submitted_at' => '2026-09-30 16:30:00+00:00',
            'preferred_date' => '2026-11-05',
        ]);
        Order::factory()->create([
            'amount_paid' => 500,
            'submitted_at' => '2026-09-30 15:30:00+00:00',
        ]);
        Order::factory()->create([
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'amount_paid' => 2000,
            'submitted_at' => '2026-04-01 00:00:00+00:00',
            'completed_at' => now(),
        ]);

        $response = $this->get(route('admin.dashboard'))->assertOk();
        $dashboard = $response->viewData('dashboard');

        $this->assertSame(3500, $dashboard['total_revenue']);
        $this->assertSame(1000, $dashboard['month_revenue']);
        $this->assertSame(['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'], $dashboard['monthly']->pluck('label')->all());
        $this->assertSame([0, 0, 0, 0, 500, 1000], $dashboard['monthly']->pluck('revenue')->all());
    }

    public function test_chart_includes_manila_month_boundaries_and_keeps_all_time_totals(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-26 12:00:00', 'Asia/Manila'));
        Order::factory()->create(['amount_paid' => 100, 'submitted_at' => '2026-04-30 15:59:59+00:00']);
        Order::factory()->create(['amount_paid' => 200, 'submitted_at' => '2026-04-30 16:00:00+00:00']);
        Order::factory()->create(['amount_paid' => 300, 'submitted_at' => '2026-10-31 15:59:59+00:00']);
        Order::factory()->create(['amount_paid' => 400, 'submitted_at' => '2026-10-31 16:00:00+00:00']);

        $response = $this->getJson(route('admin.dashboard.summary'))->assertOk();

        $response
            ->assertJsonPath('dashboard.total_orders', 4)
            ->assertJsonPath('dashboard.total_revenue', 1000)
            ->assertJsonPath('dashboard.month_revenue', 300)
            ->assertJsonPath('dashboard.monthly.0.revenue', 200)
            ->assertJsonPath('dashboard.monthly.0.orders', 1)
            ->assertJsonPath('dashboard.monthly.5.revenue', 300)
            ->assertJsonPath('dashboard.monthly.5.orders', 1);
    }

    public function test_reports_include_collections_beyond_the_latest_50_displayed_orders(): void
    {
        $this->freezeTime();
        Order::factory()->create(['amount_paid' => 1000, 'submitted_at' => now()->subDay()]);
        Order::factory()->count(50)->create();

        $response = $this->get(route('admin.dashboard'))->assertOk();

        $this->assertCount(50, $response->viewData('orders'));
        $this->assertSame(51, $response->viewData('ordersPagination')['total']);
        $this->assertSame(1000, $response->viewData('dashboard')['total_revenue']);
    }

    public function test_reports_recalculate_after_recording_and_clearing_a_payment(): void
    {
        $this->freezeTime();
        $order = Order::factory()->create(['status' => 'Confirmed']);

        $this->patchJson(route('admin.orders.payment', $order), ['amount_paid' => 1000])
            ->assertOk()->assertJsonPath('order.amount_paid', 1000);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'amount_paid' => 1000]);
        $response = $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(1000, $response->viewData('dashboard')['total_revenue']);

        $this->patchJson(route('admin.orders.payment', $order), ['amount_paid' => 0])
            ->assertOk()->assertJsonPath('order.amount_paid', 0);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'amount_paid' => 0]);
        $response = $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(0, $response->viewData('dashboard')['total_revenue']);
        $this->assertSame(0, $response->viewData('dashboard')['month_revenue']);
    }

    public function test_csv_export_streams_all_matching_orders_not_only_the_loaded_page(): void
    {
        Order::factory()->count(50)->create(['status' => 'Pending confirmation']);
        Order::factory()->create([
            'reference' => 'TB-EXPORT-CONFIRMED',
            'customer_name' => 'Export Match',
            'status' => 'Confirmed',
        ]);

        $response = $this->get(route('admin.orders.export', [
            'q' => 'Export Match',
            'status' => 'Confirmed',
        ]));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('TB-EXPORT-CONFIRMED', $response->streamedContent());
        $this->assertStringNotContainsString('Pending confirmation', $response->streamedContent());
    }

    public function test_dashboard_data_endpoints_require_admin_authentication(): void
    {
        $this->withSession(['admin.authenticated' => false]);

        foreach (['admin.orders.data', 'admin.orders.export', 'admin.customers.data', 'admin.dashboard.summary', 'admin.reports.data'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_order_pages_do_not_repeat_orders_with_equal_submission_timestamps(): void
    {
        $this->freezeTime();
        $orders = Order::factory()->count(51)->create();

        $first = $this->getJson(route('admin.orders.data'))->assertOk();
        $second = $this->getJson(route('admin.orders.data', ['page' => 2]))->assertOk();

        $this->assertSame($orders->last()->id, $first->json('orders.0.database_id'));
        $this->assertSame($orders->first()->id, $second->json('orders.0.database_id'));
        $this->assertSame([], array_intersect(
            array_column($first->json('orders'), 'database_id'),
            array_column($second->json('orders'), 'database_id'),
        ));
    }

    public function test_upcoming_filter_includes_future_bookings_beyond_next_week(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-24 10:00:00', 'Asia/Manila'));
        Order::factory()->create(['reference' => 'TB-TODAY', 'preferred_date' => '2026-10-24']);
        Order::factory()->create(['reference' => 'TB-FUTURE', 'preferred_date' => '2026-11-24']);

        $this->getJson(route('admin.orders.data', ['date' => 'upcoming']))
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', 'TB-FUTURE');
    }

    public function test_customer_totals_combine_name_case_variants_and_exclude_incomplete_orders(): void
    {
        Customer::factory()->create(['name' => 'Ana Santos', 'contact_number' => '+639171234567']);
        Order::factory()->create([
            'customer_name' => 'Ana Santos', 'contact_number' => '+639171234567',
            'status' => 'Completed', 'payment_status' => 'Paid', 'order_total' => 2000,
        ]);
        Order::factory()->create([
            'customer_name' => 'ANA SANTOS', 'contact_number' => '+639171234567',
            'status' => 'Completed', 'payment_status' => 'Paid', 'order_total' => 2950,
        ]);
        Order::factory()->create([
            'customer_name' => 'Ana Santos', 'contact_number' => '+639171234567',
            'status' => 'Confirmed', 'amount_paid' => 1000,
        ]);

        $this->getJson(route('admin.customers.data'))
            ->assertOk()
            ->assertJsonPath('customers.0.orders', 3)
            ->assertJsonPath('customers.0.completed_orders', 2)
            ->assertJsonPath('customers.0.lifetime_value', 4950);
    }

    public function test_customer_pages_are_bounded_and_searchable(): void
    {
        $this->freezeTime();
        Customer::factory()->count(50)->create(['last_order_at' => now()->subDay()]);
        Customer::factory()->create(['name' => 'Unique Match', 'last_order_at' => now()]);

        $this->getJson(route('admin.customers.data'))
            ->assertOk()->assertJsonCount(50, 'customers')->assertJsonPath('pagination.total', 51);
        $this->getJson(route('admin.customers.data', ['page' => 2]))
            ->assertOk()->assertJsonCount(1, 'customers');
        $this->getJson(route('admin.customers.data', ['q' => 'Unique Match']))
            ->assertOk()->assertJsonCount(1, 'customers')->assertJsonPath('customers.0.name', 'Unique Match');
    }

    public function test_inventory_update_accepts_post_method_override_and_clears_unit_cost(): void
    {
        $item = InventoryItem::factory()->create(['unit_cost' => 125.50]);

        $this->post(route('admin.inventory.update', $item), [
            '_method' => 'PATCH',
            'name' => $item->name, 'category' => $item->category,
            'quantity' => 12.5, 'unit' => $item->unit, 'low_stock_threshold' => 3,
            'unit_cost' => '',
        ])->assertOk();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 12.5, 'unit_cost' => null]);
    }

    public function test_customer_directory_uses_persistent_customer_records(): void
    {
        Customer::factory()->create([
            'name' => 'Repeat Customer',
            'contact_number' => '+639762271228',
            'email' => 'repeat@example.com',
        ]);
        Order::factory()->create([
            'customer_name' => 'Repeat Customer',
            'contact_number' => '+639762271228',
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'order_total' => 2950,
        ]);

        $this->getJson(route('admin.customers.data', ['q' => 'Repeat']))
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('customers.0.name', 'Repeat Customer')
            ->assertJsonPath('customers.0.phone', '+639762271228')
            ->assertJsonPath('customers.0.email', 'repeat@example.com')
            ->assertJsonPath('customers.0.orders', 1)
            ->assertJsonPath('customers.0.completed_orders', 1)
            ->assertJsonPath('customers.0.lifetime_value', 2950);
    }

    public function test_order_data_endpoint_filters_and_paginates_on_the_server(): void
    {
        $this->freezeTime();
        Order::factory()->count(50)->create([
            'status' => 'Pending confirmation',
            'submitted_at' => now()->subMinute(),
        ]);
        Order::factory()->create([
            'reference' => 'TB-FILTER-MATCH',
            'customer_name' => 'Searchable Customer',
            'status' => 'Confirmed',
            'submitted_at' => now(),
        ]);

        $this->getJson(route('admin.orders.data', [
            'q' => 'Searchable',
            'status' => 'Confirmed',
        ]))
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('pagination.per_page', 50)
            ->assertJsonPath('orders.0.id', 'TB-FILTER-MATCH');

        $this->getJson(route('admin.orders.data'))
            ->assertOk()
            ->assertJsonCount(50, 'orders')
            ->assertJsonPath('pagination.total', 51)
            ->assertJsonPath('pagination.last_page', 2);
    }

    public function test_summary_endpoint_returns_collections_from_all_orders(): void
    {
        Order::factory()->create([
            'status' => 'Cancelled',
            'payment_status' => 'Deposit received',
            'amount_paid' => 750,
        ]);
        Order::factory()->create([
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'amount_paid' => 2000,
            'order_total' => 2000,
        ]);

        $this->getJson(route('admin.dashboard.summary'))
            ->assertOk()
            ->assertJsonPath('dashboard.total_orders', 2)
            ->assertJsonPath('dashboard.total_revenue', 2750)
            ->assertJsonPath('dashboard.completed', 1)
            ->assertJsonPath('dashboard.cancelled', 1);
    }

    public function test_report_data_counts_package_mix_without_embedding_it_in_initial_html(): void
    {
        Order::factory()->count(3)->create();

        $this->getJson(route('admin.reports.data'))
            ->assertOk()
            ->assertJsonPath('package_mix.0.weight', '3 kg')
            ->assertJsonPath('package_mix.0.count', 3);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Package demand loads when you open Reports.')
            ->assertDontSee('"package_mix"', false);
    }
}
