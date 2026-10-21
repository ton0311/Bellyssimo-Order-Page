<?php

namespace Tests\Feature;

use SimpleXMLElement;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_sitemap_lists_only_the_public_canonical_pages(): void
    {
        $sitemap = simplexml_load_file(public_path('sitemap.xml'));

        $this->assertInstanceOf(SimpleXMLElement::class, $sitemap);

        $locations = [];

        foreach ($sitemap->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertSame([
            'https://tonosbellyssimo.online/',
            'https://tonosbellyssimo.online/our-belly',
            'https://tonosbellyssimo.online/about-faqs',
            'https://tonosbellyssimo.online/order',
        ], $locations);
    }

    public function test_robots_file_advertises_the_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($robots);
        $this->assertStringContainsString(
            'Sitemap: https://tonosbellyssimo.online/sitemap.xml',
            $robots,
        );
    }
}
