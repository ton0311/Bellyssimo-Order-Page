<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderEmailService;
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
}
