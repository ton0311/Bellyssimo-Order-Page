<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class AboutControllerTest extends TestCase
{
    public function test_about_page_renders_business_story_faqs_and_contact_details(): void
    {
        $response = $this->get(route('about'));

        $response
            ->assertOk()
            ->assertViewIs('about')
            ->assertSee('Beautifully crispy, dangerously juicy.')
            ->assertSee('established in 2026')
            ->assertSee(asset('css/site-v1.css'), false)
            ->assertSee(asset('images/about-belly-filipino-v2-800.jpg'), false)
            ->assertSee(asset('images/about-belly-filipino-v2-1600.jpg'), false)
            ->assertDontSee('@tailwindcss/browser', false)
            ->assertSee('The Bellyssimo Barkadahan')
            ->assertSee('Bellyssimo Salo-Salo')
            ->assertSee('Bellyssimo Handaan')
            ->assertSee('₱2,000')
            ->assertSee('₱2,550')
            ->assertSee('₱2,950')
            ->assertSee('Bellyssimo at a Glance')
            ->assertSee('3 Belly Sizes')
            ->assertSee('3kg, 4kg, 5kg')
            ->assertSee('Serves 8–18')
            ->assertSee('Generous feast')
            ->assertSee('Flavor Options')
            ->assertSee('Regular / Spicy')
            ->assertSee('For Celebrations')
            ->assertSee('Fiesta-ready')
            ->assertSee('Frequently Asked Questions')
            ->assertSee('Is Bellyssimo suitable for celebrations?')
            ->assertSee('Can I mix different package sizes and flavors?')
            ->assertSee('Is submitting the form an automatic confirmation?')
            ->assertSee('No payment is collected on this website.')
            ->assertSee('Perayra Village, Maningcol, Ozamiz City')
            ->assertSee('https://maps.app.goo.gl/3XZ87Ep9kWLDPzh47', false)
            ->assertSee('0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false)
            ->assertSee('https://www.facebook.com/profile.php?id=61572029845712', false)
            ->assertSee(route('order'), false)
            ->assertSee(route('home'), false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_shared_header_links_to_the_about_page(): void
    {
        $response = $this->get(route('belly'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('about').'"', false)
            ->assertSee('href="'.route('about').'#contact"', false)
            ->assertSee('Call Toño’s Bellyssimo at 0976-2271-228')
            ->assertSee('href="tel:+639762271228"', false);
    }
}
