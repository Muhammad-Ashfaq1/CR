<?php

namespace Tests\Feature\Pwa;

use Tests\TestCase;

/**
 * Offline page + PWA wiring tests.
 *
 * Verifies that the PWA installability prerequisites and offline fallback
 * behave according to the standard PWA contract.
 */
class OfflinePageTest extends TestCase
{
    public function test_the_offline_page_is_a_branded_card(): void
    {
        $this->get('/offline.html')
            ->assertOk()
            ->assertSee('You are Offline', false)
            ->assertSee('Construction Ready', false)
            ->assertSee('Retry Connection', false);
    }

    public function test_the_offline_document_does_not_depend_on_external_network(): void
    {
        $html = $this->get('/offline.html')->getContent();

        // Should not have external stylesheets or webfonts that fail offline
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('vendor/css/core.css', $html);
    }

    public function test_the_manifest_is_served_with_the_webmanifest_mime(): void
    {
        $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertSee('Construction Ready', false)
            ->assertSee('"display": "standalone"', false)
            ->assertSee('#f59e0b', false);
    }

    public function test_the_service_worker_falls_back_to_offline_html_and_never_caches_pages(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        $this->assertNotFalse($sw);
        $this->assertStringContainsString("OFFLINE_URL = '/offline.html'", $sw);
        $this->assertStringContainsString("request.mode === 'navigate'", $sw);
        $this->assertStringContainsString('Never cache HTML', $sw);
        $this->assertStringContainsString('cr-static-', $sw);
    }

    public function test_the_login_page_registers_the_service_worker_and_links_manifest(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('serviceWorker.register', false)
            ->assertSee('/manifest.webmanifest', false);
    }
}
