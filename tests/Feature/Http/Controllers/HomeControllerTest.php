<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    public function test_homepage_renders_bellyssimo_booking_content(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertViewIs('home')
            ->assertSee('Toño’s Bellyssimo')
            ->assertSee('Beautifully crispy, dangerously juicy')
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('Bellyssimo Salo-Salo')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('₱2,000')
            ->assertSee('₱2,550')
            ->assertSee('₱2,950')
            ->assertSee('3 Belly Sizes')
            ->assertSee('3kg, 4kg, 5kg')
            ->assertSee('Serves 8–18')
            ->assertSee('Generous feast')
            ->assertSee('Flavor Options')
            ->assertSee('Regular / Spicy')
            ->assertSee('For Celebrations')
            ->assertSee('Fiesta-ready')
            ->assertSee('Starts at ₱2,000')
            ->assertSee('🌶️')
            ->assertSee('size-10 shrink-0 rounded-full', false)
            ->assertSee('aria-label="Toño’s Bellyssimo hero"', false)
            ->assertSee('Please book at least 48 hours before your preferred pickup or delivery.')
            ->assertSee(asset('css/site-v2.css'), false)
            ->assertSee(asset('images/home-hero-filipino-v2-800.jpg'), false)
            ->assertSee(asset('images/home-hero-filipino-v2-1600.jpg'), false)
            ->assertSee(asset('images/lechon-barkadahan-3kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-salo-salo-4kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-handaan-5kg-480.jpg'), false)
            ->assertSee('fetchpriority="high"', false)
            ->assertDontSee('@tailwindcss/browser', false)
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertSee(route('belly'), false)
            ->assertSee(route('order'), false)
            ->assertSee('0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false)
            ->assertSee('https://www.facebook.com/profile.php?id=61572029845712', false);

        $this->assertSame(3, substr_count($response->getContent(), '>🌶️</span>'));
    }

    public function test_valid_business_type_returns_success_response(): void
    {
        $response = $this->post(route('generate'), [
            'business_type' => 'Coffee shop',
        ]);

        $response
            ->assertOk()
            ->assertContent('Generated successfully');
    }

    public function test_missing_business_type_returns_validation_error(): void
    {
        $response = $this
            ->from(route('home'))
            ->post(route('generate'), []);

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('business_type');
    }
}
