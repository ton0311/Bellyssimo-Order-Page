<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderNotificationSetting;
use App\Services\OrderEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Resend\Client;
use Resend\Contracts\Transporter;
use Resend\Laravel\Facades\Resend;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;
use Resend\ValueObjects\Transporter\Payload;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_uses_the_published_template_and_an_idempotency_key(): void
    {
        config([
            'mail.from.address' => 'onboarding@resend.dev',
            'mail.from.name' => 'Toño’s Bellyssimo',
            'services.resend.key' => 're_test_key',
            'services.resend.templates.order_confirmation' => 'template-confirmation',
        ]);
        $transporter = new class implements Transporter
        {
            public ?Payload $payload = null;

            public function request(Payload $payload): array
            {
                $this->payload = $payload;

                return ['id' => 'email-test-123'];
            }
        };
        Resend::swap(new Client($transporter));
        $order = Order::factory()->make([
            'reference' => 'TB-20261018-TEST',
            'customer_name' => 'Ana Santos',
            'email' => 'ana@example.com',
            'preferred_date' => '2026-10-25',
            'preferred_time' => '12:00',
        ]);

        $emailId = app(OrderEmailService::class)->send(
            $order,
            OrderEmailService::CONFIRMATION,
            null,
            'order-confirmation-TB-20261018-TEST',
        );

        $this->assertSame('email-test-123', $emailId);
        $this->assertNotNull($transporter->payload);
        $request = $transporter->payload->toRequest(BaseUri::from('api.resend.test'), new Headers([]));
        $body = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('order-confirmation-TB-20261018-TEST', $request->getHeaderLine('Idempotency-Key'));
        $this->assertSame('template-confirmation', $body['template']['id']);
        $this->assertSame('Ana Santos', $body['template']['variables']['CUSTOMER_NAME']);
        $this->assertSame('TB-20261018-TEST', $body['template']['variables']['ORDER_REFERENCE']);
        $this->assertSame('₱2,000', $body['template']['variables']['ORDER_TOTAL']);
        $this->assertSame(['ana@example.com'], $body['to']);
    }

    public function test_missing_resend_configuration_is_logged_without_sending(): void
    {
        Log::spy();
        config([
            'services.resend.key' => null,
            'services.resend.templates.order_confirmation' => null,
        ]);
        $order = Order::factory()->make([
            'reference' => 'TB-20261018-NOCONFIG',
            'email' => 'ana@example.com',
        ]);

        $result = app(OrderEmailService::class)->send(
            $order,
            OrderEmailService::CONFIRMATION,
            null,
            'order-confirmation-TB-20261018-NOCONFIG',
        );

        $this->assertNull($result);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_declined_status_email_is_apologetic_and_says_the_order_did_not_push_through(): void
    {
        config([
            'mail.from.address' => 'orders@mail.tonosbellyssimo.online',
            'mail.from.name' => 'Toño’s Bellyssimo',
            'services.resend.key' => 're_test_key',
            'services.resend.templates.order_status' => 'template-status',
        ]);
        $transporter = new class implements Transporter
        {
            public ?Payload $payload = null;

            public function request(Payload $payload): array
            {
                $this->payload = $payload;

                return ['id' => 'email-status-declined'];
            }
        };
        Resend::swap(new Client($transporter));
        $order = Order::factory()->make([
            'reference' => 'TB-20261018-DECLINED',
            'customer_name' => 'Ana Santos',
            'email' => 'ana@example.com',
            'preferred_date' => '2026-10-25',
            'preferred_time' => '12:00',
        ]);

        $emailId = app(OrderEmailService::class)->send(
            $order,
            OrderEmailService::STATUS,
            'Declined',
            'order-status-TB-20261018-DECLINED',
        );

        $this->assertSame('email-status-declined', $emailId);
        $this->assertNotNull($transporter->payload);
        $request = $transporter->payload->toRequest(BaseUri::from('api.resend.test'), new Headers([]));
        $body = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $variables = $body['template']['variables'];

        $this->assertSame('Declined', $variables['ORDER_STATUS']);
        $this->assertStringContainsString('very sorry', $variables['STATUS_MESSAGE']);
        $this->assertStringContainsString('did not push through', $variables['STATUS_MESSAGE']);
        $this->assertStringContainsString('sincerely apologize', $variables['STATUS_MESSAGE']);
    }

    public function test_owner_notification_contains_the_order_summary_and_reply_to_customer(): void
    {
        config([
            'app.url' => 'https://tonosbellyssimo.online',
            'mail.from.address' => 'orders@mail.tonosbellyssimo.online',
            'mail.from.name' => 'Toño’s Bellyssimo',
            'services.resend.key' => 're_test_key',
        ]);
        OrderNotificationSetting::query()->findOrFail(1)->update([
            'recipient_emails' => ['owner@example.com', 'backup@example.com'],
        ]);
        $transporter = new class implements Transporter
        {
            public ?Payload $payload = null;

            public function request(Payload $payload): array
            {
                $this->payload = $payload;

                return ['id' => 'email-owner-123'];
            }
        };
        Resend::swap(new Client($transporter));
        $order = Order::factory()->make([
            'reference' => 'TB-20261018-OWNER',
            'customer_name' => 'Ana <Santos>',
            'contact_number' => '+639171234567',
            'email' => 'ana@example.com',
            'preferred_date' => '2026-10-25',
            'preferred_time' => '12:00',
            'notes' => '<script>alert("unsafe")</script>',
        ]);

        $emailId = app(OrderEmailService::class)->send(
            $order,
            OrderEmailService::OWNER_NOTIFICATION,
            null,
            'new-order-notification-TB-20261018-OWNER',
        );

        $this->assertSame('email-owner-123', $emailId);
        $this->assertNotNull($transporter->payload);
        $request = $transporter->payload->toRequest(BaseUri::from('api.resend.test'), new Headers([]));
        $body = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('new-order-notification-TB-20261018-OWNER', $request->getHeaderLine('Idempotency-Key'));
        $this->assertSame(['owner@example.com', 'backup@example.com'], $body['to']);
        $this->assertSame(['ana@example.com'], $body['reply_to']);
        $this->assertStringContainsString('New booking TB-20261018-OWNER', $body['subject']);
        $this->assertStringContainsString('Ana &lt;Santos&gt;', $body['html']);
        $this->assertStringNotContainsString('<script>alert("unsafe")</script>', $body['html']);
        $this->assertStringContainsString('/admin/orders', $body['html']);
        $this->assertStringContainsString('TB-20261018-OWNER', $body['text']);
    }

    public function test_owner_notification_is_skipped_when_no_recipient_is_configured(): void
    {
        Log::spy();
        config([
            'services.resend.key' => 're_test_key',
        ]);
        OrderNotificationSetting::query()->findOrFail(1)->update([
            'recipient_emails' => [],
        ]);
        $order = Order::factory()->make([
            'reference' => 'TB-20261018-NOOWNER',
            'email' => 'ana@example.com',
        ]);

        $result = app(OrderEmailService::class)->send(
            $order,
            OrderEmailService::OWNER_NOTIFICATION,
            null,
            'new-order-notification-TB-20261018-NOOWNER',
        );

        $this->assertNull($result);
        Log::shouldHaveReceived('warning')->once();
    }
}
