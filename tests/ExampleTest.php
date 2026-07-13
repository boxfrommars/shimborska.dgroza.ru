<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ExampleTest extends TestCase
{
    public function testMainPageIsAvailable(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/css/style.css', false);
    }

    public function testStaticPagesAreAvailable(): void
    {
        $this->get('/project')->assertOk();
        $this->get('/author')->assertOk();
    }

    public function testPoemPageIsAvailable(): void
    {
        $this->get('/different/two-monkeys')->assertOk();
    }

    public function testSectionRedirectsToItsFirstPoem(): void
    {
        $this->get('/different')
            ->assertRedirect(route('poem', [
                'parent' => 'different',
                'title' => 'two-monkeys',
            ]));
    }

    public function testUnknownPagesReturnNotFound(): void
    {
        $this->get('/unknown')->assertNotFound();
        $this->get('/different/unknown')->assertNotFound();
    }

    public function testSitemapCanBeGenerated(): void
    {
        $path = public_path('sitemap.xml');
        File::delete($path);

        try {
            config(['app.url' => 'https://example.test', 'app.https' => true]);

            self::assertSame(0, Artisan::call('sitemap:generate'));
            self::assertFileExists($path);

            $xml = File::get($path);
            self::assertStringContainsString('<urlset', $xml);
            self::assertStringContainsString('https://example.test/different/two-monkeys', $xml);
        } finally {
            File::delete($path);
        }
    }
}
