<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_confirms_the_application_and_database_are_available(): void
    {
        $this->getJson(route('health'))
            ->assertOk()
            ->assertExactJson([
                'status' => 'healthy',
                'checks' => [
                    'application' => true,
                    'database' => true,
                ],
            ]);
    }

    public function test_health_check_returns_service_unavailable_when_the_database_fails(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->andThrow(new RuntimeException('Sensitive database details'));

        $this->getJson(route('health'))
            ->assertServiceUnavailable()
            ->assertExactJson([
                'status' => 'unhealthy',
                'checks' => [
                    'application' => true,
                    'database' => false,
                ],
            ])
            ->assertDontSee('Sensitive database details');
    }
}
