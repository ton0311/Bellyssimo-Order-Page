<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\SendOrderEmail;
use App\Services\OrderEmailService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_page_renders_packages_and_booking_request_form(): void
    {
        $response = $this->get(route('order'));

        $this->assertSame(6, substr_count($response->getContent(), 'style="color: #b91c1c;"'));

        $response
            ->assertOk()
            ->assertViewIs('order')
            ->assertSee('Book Your Belly')
            ->assertSee('aria-label="Booking hero"', false)
            ->assertSee(asset('css/site-v3.css'), false)
            ->assertSee(asset('images/order-hero-filipino-v2-800.jpg'), false)
            ->assertSee(asset('images/order-hero-filipino-v2-1600.jpg'), false)
            ->assertSee('fetchpriority="high"', false)
            ->assertDontSee('@tailwindcss/browser', false)
            ->assertSee('Explore the Packages')
            ->assertDontSee('class="text-xs font-medium text-[#221a16]">Ozamiz City</span>', false)
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('Bellyssimo Salo-Salo')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('₱2,000')
            ->assertSee('₱2,550')
            ->assertSee('₱2,950')
            ->assertSee('Your belly orders')
            ->assertSee('Package variety')
            ->assertSee('Add Another Belly')
            ->assertSee('Pickup or delivery')
            ->assertSee('Payment option')
            ->assertSee('GCash')
            ->assertSee('GCash payment details will be sent to your email after your booking is confirmed.')
            ->assertSee('First name')
            ->assertSee('Last name')
            ->assertSee('Do not include the first 0. Enter the 10-digit mobile number starting with 9.')
            ->assertDontSee('delivery rider may call this number')
            ->assertSee('Email address')
            ->assertSee('Your booking confirmation and future status updates will be sent here.')
            ->assertSee('>+63<', false)
            ->assertSee('Preferred pickup or delivery time')
            ->assertSee('Select the time you want to pick up your order or receive the delivery. Available from 11:00 AM to 7:00 PM.')
            ->assertSee('Please book at least 48 hours before your preferred pickup or delivery schedule.')
            ->assertSee('Delivery fee will be shouldered by the customer.')
            ->assertDontSee('The delivery fee depends on this location')
            ->assertSee('value="11:00"', false)
            ->assertSee('>11:00 AM</option>', false)
            ->assertSee('value="19:00"', false)
            ->assertSee('>7:00 PM</option>', false)
            ->assertDontSee('<option value="10:30">', false)
            ->assertDontSee('<option value="19:30">', false)
            ->assertSee('Perayra Village, Maningcol, Ozamiz City')
            ->assertSee('https://maps.app.goo.gl/3XZ87Ep9kWLDPzh47', false)
            ->assertSee('Review Booking Request')
            ->assertSee('action="'.route('order.confirm').'"', false)
            ->assertSee('name="items[${index}][package]"', false)
            ->assertSee('name="items[${index}][flavor]"', false)
            ->assertDontSee('Your feast')
            ->assertDontSee('Mixed Belly Order')
            ->assertDontSee('id="summary-items"', false)
            ->assertSee('Booking request only')
            ->assertSee('Toño’s Bellyssimo logo')
            ->assertSee(asset('images/lechon-barkadahan-3kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-salo-salo-4kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-handaan-5kg-480.jpg'), false)
            ->assertSee('Ozamiz City, Misamis Occidental')
            ->assertSee(route('belly'), false)
            ->assertSee(route('home'), false)
            ->assertSee('Call Toño’s Bellyssimo at 0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false)
            ->assertSee('https://www.facebook.com/profile.php?id=61572029845712', false);
    }

    public function test_valid_order_redirects_to_confirmation_with_exact_breakdown(): void
    {
        $response = $this->post(route('order.confirm'), $this->validOrderPayload());

        $response
            ->assertRedirect(route('order.confirmation'))
            ->assertSessionHas('order.draft', function (array $order): bool {
                return $order['items'][0]['package']['name'] === 'The Bellyssimo Barkadahan'
                    && $order['items'][0]['flavor'] === 'Spicy'
                    && $order['items'][0]['line_total'] === 2050
                    && $order['items'][1]['package']['name'] === 'Bellyssimo Handaan'
                    && $order['items'][1]['flavor'] === 'Regular'
                    && $order['items'][1]['line_total'] === 2950
                    && $order['quantity'] === 2
                    && $order['package_subtotal'] === 4950
                    && $order['spicy_subtotal'] === 50
                    && $order['order_total'] === 5000
                    && $order['fulfillment'] === 'Pickup'
                    && $order['payment_method'] === 'GCash'
                    && $order['customer_name'] === 'Juan Dela Cruz'
                    && $order['contact_number'] === '+639171234567'
                    && $order['email'] === 'juan@example.com';
            });

        $this->get(route('order.confirmation'))
            ->assertOk()
            ->assertViewIs('order-confirmation')
            ->assertSee('Confirm Your Booking Request')
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('#1 The Bellyssimo Barkadahan 3 kg')
            ->assertSee('Belly Order #1')
            ->assertSee('Spicy')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('#2 Bellyssimo Handaan 5 kg')
            ->assertSee('Belly Order #2')
            ->assertSee('Regular')
            ->assertSee('₱4,950')
            ->assertSee('₱5,000')
            ->assertSee('Payment method')
            ->assertSee('GCash')
            ->assertSee('juan@example.com')
            ->assertSee('Perayra Village, Maningcol, Ozamiz City')
            ->assertSee('Place Booking Request')
            ->assertSee('action="'.route('order.place').'"', false);
    }

    public function test_placing_reviewed_order_redirects_to_thank_you_page(): void
    {
        Queue::fake();
        $this->travelTo(CarbonImmutable::parse('2026-10-17 07:57:00', 'UTC'));

        $this->post(route('order.confirm'), $this->validOrderPayload());

        $response = $this->post(route('order.place'));

        $response
            ->assertRedirect(route('order.thank-you'))
            ->assertSessionMissing('order.draft')
            ->assertSessionHas('order.placed');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Juan Dela Cruz',
            'contact_number' => '+639171234567',
            'email' => 'juan@example.com',
            'quantity' => 2,
            'order_total' => 5000,
            'status' => 'Pending confirmation',
            'payment_method' => 'GCash',
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
        ]);
        Queue::assertPushed(SendOrderEmail::class, function (SendOrderEmail $job): bool {
            return $job->kind === OrderEmailService::CONFIRMATION
                && $job->status === null
                && str_starts_with($job->idempotencyKey, 'order-confirmation-TB-20261017-');
        });
        Queue::assertPushed(SendOrderEmail::class, function (SendOrderEmail $job): bool {
            return $job->kind === OrderEmailService::OWNER_NOTIFICATION
                && $job->status === null
                && str_starts_with($job->idempotencyKey, 'new-order-notification-TB-20261017-');
        });
        Queue::assertPushed(SendOrderEmail::class, 2);

        $this->get(route('order.thank-you'))
            ->assertOk()
            ->assertViewIs('order-thank-you')
            ->assertSee('Your booking request has been placed successfully.')
            ->assertSee('Booking reference')
            ->assertSee('Submitted October 17, 2026 • 3:57 PM')
            ->assertSee('PHT')
            ->assertSee('Philippine Standard Time (Asia/Manila)')
            ->assertSee('Pending Availability Confirmation')
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('₱5,000')
            ->assertSee('GCash')
            ->assertSee('juan@example.com')
            ->assertSee('What happens next?')
            ->assertSee('Availability Review')
            ->assertSee('Email Update')
            ->assertSee('your GCash QR and payment instructions will also be sent by email')
            ->assertSee('Preparation &amp; Fulfillment', false)
            ->assertDontSee('Direct Contact')
            ->assertSee('Call 0976-2271-228')
            ->assertSee('id="copy-booking-reference"', false)
            ->assertSee('Copy booking reference')
            ->assertDontSee('Print / Save Request')
            ->assertSee('No payment is collected on this website');
    }

    public function test_delivery_requires_a_location(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'fulfillment' => 'Delivery',
            'delivery_location' => '',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'delivery_location' => 'Please enter the delivery location so the fee can be confirmed.',
            ]);
    }

    public function test_payment_method_must_be_cash_or_gcash(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'payment_method' => 'Credit Card',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'payment_method' => 'Please choose Cash or GCash as your payment option.',
            ]);
    }

    public function test_each_belly_requires_a_valid_package_and_flavor_choice(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'items' => [
                ['package' => 'barkadahan', 'flavor' => 'Spicy'],
                ['package' => 'not-a-package'],
            ],
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'items.1.package' => 'Please choose a valid Bellyssimo package.',
                'items.1.flavor' => 'Please choose Regular or Spicy for every belly in your order.',
            ]);
    }

    public function test_confirmation_escapes_customer_supplied_content(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'first_name' => '<script>alert("unsafe")</script>',
        ]);

        $this->post(route('order.confirm'), $payload);

        $this->get(route('order.confirmation'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("unsafe")</script>', false);
    }

    public function test_contact_number_must_be_a_valid_ph_mobile_number(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'contact_number' => '8123456789',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'contact_number' => 'Enter a valid Philippine mobile number after +63, beginning with 9.',
            ]);
    }

    public function test_email_must_be_valid(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'email' => 'not-an-email',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'email' => 'Please enter a valid email address.',
            ]);
    }

    public function test_preferred_time_must_be_within_booking_hours(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'preferred_time' => '19:30',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'preferred_time' => 'Please choose a preferred time between 11:00 AM and 7:00 PM.',
            ]);
    }

    public function test_booking_must_be_at_least_48_hours_before_the_schedule(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-18 12:00:00', 'Asia/Manila'));
        $payload = array_merge($this->validOrderPayload(), [
            'preferred_date' => '2026-10-20',
            'preferred_time' => '11:30',
        ]);

        $this->from(route('order'))
            ->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order'))
            ->assertSessionHasErrors([
                'preferred_date' => 'Bookings must be placed at least 48 hours before the preferred pickup or delivery time.',
            ]);
    }

    public function test_booking_exactly_48_hours_before_the_schedule_is_allowed(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-18 12:00:00', 'Asia/Manila'));
        $payload = array_merge($this->validOrderPayload(), [
            'preferred_date' => '2026-10-20',
            'preferred_time' => '12:00',
        ]);

        $this->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order.confirmation'))
            ->assertSessionHasNoErrors();
    }

    public function test_pasted_local_ph_number_is_normalized_to_plus_63(): void
    {
        $payload = array_merge($this->validOrderPayload(), [
            'contact_number' => '0917 123 4567',
        ]);

        $this->post(route('order.confirm'), $payload)
            ->assertRedirect(route('order.confirmation'))
            ->assertSessionHas('order.draft.contact_number', '+639171234567');
    }

    public function test_confirmation_and_thank_you_require_an_order_session(): void
    {
        $this->get(route('order.confirmation'))->assertRedirect(route('order'));
        $this->get(route('order.thank-you'))->assertRedirect(route('order'));
    }

    /** @return array<string, mixed> */
    private function validOrderPayload(): array
    {
        return [
            'items' => [
                ['package' => 'barkadahan', 'flavor' => 'Spicy'],
                ['package' => 'handaan', 'flavor' => 'Regular'],
            ],
            'fulfillment' => 'Pickup',
            'delivery_location' => '',
            'payment_method' => 'GCash',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'contact_number' => '9171234567',
            'email' => 'Juan@Example.com',
            'preferred_date' => now('Asia/Manila')->addDays(3)->toDateString(),
            'preferred_time' => '12:00',
            'notes' => 'Please have it ready at noon.',
        ];
    }
}
