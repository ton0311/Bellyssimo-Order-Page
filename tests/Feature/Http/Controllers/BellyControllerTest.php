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
            ->assertSee(asset('images/our-belly-hero-v3.png'), false)
            ->assertSee(asset('images/lechon-barkadahan-3kg.png'), false)
            ->assertSee(asset('images/lechon-salo-salo-4kg.png'), false)
            ->assertSee(asset('images/lechon-handaan-5kg.png'), false)
            ->assertSee('Ozamiz City, Misamis Occidental')
            ->assertSee('About &amp; FAQs', false)
            ->assertSee('0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false)
            ->assertSee('https://www.facebook.com/profile.php?id=61572029845712', false)
            ->assertSee(route('order'), false)
            ->assertSee(route('home'), false);
    }
}
