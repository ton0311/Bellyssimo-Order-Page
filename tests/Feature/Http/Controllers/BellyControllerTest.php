<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class BellyControllerTest extends TestCase
{
    public function test_our_belly_page_renders_package_showcase(): void
    {
        $response = $this->get(route('belly'));

        $response
            ->assertOk()
            ->assertViewIs('belly')
            ->assertSee('aria-label="Our Belly hero"', false)
            ->assertSee('Our Signature Lechon Belly Packages')
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('Bellyssimo Salo-Salo')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('₱2,000')
            ->assertSee('₱2,550')
            ->assertSee('₱2,950')
            ->assertSee('Spicy (+₱50)')
            ->assertSee('Make it Spicy')
            ->assertSee('All sizes')
            ->assertDontSee('aria-label="Spicy"', false)
            ->assertSee(asset('css/site-v3.css'), false)
            ->assertSee(asset('images/our-belly-hero-v3-800.jpg'), false)
            ->assertSee(asset('images/our-belly-hero-v3-1600.jpg'), false)
            ->assertSee(asset('images/lechon-barkadahan-3kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-salo-salo-4kg-480.jpg'), false)
            ->assertSee(asset('images/lechon-handaan-5kg-480.jpg'), false)
            ->assertDontSee('@tailwindcss/browser', false)
            ->assertSee('Ozamiz City, Misamis Occidental')
            ->assertSee('About &amp; FAQs', false)
            ->assertSee('0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false)
            ->assertSee('https://www.facebook.com/profile.php?id=61572029845712', false)
            ->assertSee(route('order'), false)
            ->assertSee(route('home'), false);
    }
}
