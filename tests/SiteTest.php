<?php

namespace Tests;

use App\PoemCatalog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SiteTest extends TestCase
{
    public function testMainPageIsAvailable(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="viewport" content="width=device-width, initial-scale=1"', false)
            ->assertSee('/css/style.css', false)
            ->assertDontSee('/css/print.css', false)
            ->assertSee('<dialog id="content"', false)
            ->assertSee('/js/script.js', false)
            ->assertDontSee('jquery', false);
    }

    public function testPrintStylesAreOnlyLoadedForPrintablePages(): void
    {
        foreach (['/different/two-monkeys', '/author', '/project'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee(
                    '<link rel="stylesheet" type="text/css" href="/css/print.css" media="print" />',
                    false,
                );
        }

        $this->get('/')
            ->assertOk()
            ->assertDontSee('/css/print.css', false);

        $this->get('/unknown')
            ->assertNotFound()
            ->assertDontSee('/css/print.css', false);

        self::assertFileExists(public_path('css/print.css'));
    }

    public function testContentsDialogUsesCatalogOrderInTwoColumns(): void
    {
        $response = $this->get('/')->assertOk();

        self::assertSame(
            2,
            substr_count($response->getContent(), '<ul class="contents-column">'),
        );

        $response->assertSeeInOrder([
            'data-section="different"',
            'data-section="text"',
            'data-section="semicolon"',
            'data-section="moment"',
        ], false);
    }

    public function testStaticPagesAreAvailable(): void
    {
        $this->get('/project')
            ->assertOk()
            ->assertSee('Вислава Шимборская. Избранное в переводах Асара Эппеля');
        $this->get('/author')->assertOk();
    }

    public function testPublicPagesDoNotStartSessions(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeaderMissing('set-cookie');

        $this->get('/different/two-monkeys')
            ->assertOk()
            ->assertHeaderMissing('set-cookie');
    }

    public function testUnusedStorageRoutesAreDisabled(): void
    {
        self::assertNull(Route::getRoutes()->getByName('storage.local'));
        self::assertNull(Route::getRoutes()->getByName('storage.local.upload'));
    }

    public function testPoemPageIsAvailable(): void
    {
        $this->get('/different/two-monkeys')->assertOk();
        $this->get('/moment/in-abundance')
            ->assertOk()
            ->assertSee('В преизбытке')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('W zatrzęsieniu');

        $this->get('/moment/clouds')
            ->assertOk()
            ->assertSee('Облака')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Chmury');

        $this->get('/moment/negative')
            ->assertOk()
            ->assertSee('Негатив')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Negatyw');

        $this->get('/moment/telephone-receiver')
            ->assertOk()
            ->assertSee('Телефонная трубка')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Słuchawka');

        $this->get('/moment/three-striking-words')
            ->assertOk()
            ->assertSee('Три поразительных слова')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Trzy słowa najdziwniejsze');

        $this->get('/moment/silence-of-plants')
            ->assertOk()
            ->assertSee('Молчание растений')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Milczenie roślin');

        $this->get('/moment/plato-or-why')
            ->assertOk()
            ->assertSee('Платон, или зачем')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Platon, czyli dlaczego');

        $this->get('/moment/little-girl-pull-tablecloth')
            ->assertOk()
            ->assertSee('Маленькая девочка стаскивает скатерть')
            ->assertSeeInOrder([
                'Перевод Натальи Астафьевой',
                'Mała dziewczynka ściąga obrus',
                'Перевод Асара Эппеля',
            ]);

        $this->get('/moment/from-memories')
            ->assertOk()
            ->assertSee('Из воспоминаний')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Ze wspomnień');

        $this->get('/moment/puddle')
            ->assertOk()
            ->assertSee('Лужа')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Kałuża');

        $this->get('/moment/first-love')
            ->assertOk()
            ->assertSee('Первая любовь')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Pierwsza miłość');

        $this->get('/moment/about-soul')
            ->assertOk()
            ->assertSee('Кое-что о душе')
            ->assertSeeInOrder([
                'Перевод Асара Эппеля (Иностранная литература 2000, №8)',
                'Trochę o duszy',
                'Душой обзаводятся по временам.',
                'Перевод Асара Эппеля (Избранное. Текст, 2007)',
            ]);

        $this->get('/moment/early-hour')
            ->assertOk()
            ->assertSee('Спозаранку')
            ->assertSee('Перевод Асара Эппеля')
            ->assertSee('Wczesna godzina');

        $this->get('/moment/in-park')
            ->assertOk()
            ->assertSee('В парке')
            ->assertSeeInOrder([
                'Перевод Натальи Астафьевой',
                'W parku',
                'такая облу-у-упленная?',
                'Перевод Асара Эппеля',
            ]);
    }

    public function testMovedPoemRedirectsPermanently(): void
    {
        $this->get('/different/little-girl-pull-tablecloth')
            ->assertStatus(301)
            ->assertRedirect('/moment/little-girl-pull-tablecloth');

        $this->get('/different/about-soul')
            ->assertStatus(301)
            ->assertRedirect('/moment/about-soul');

        $this->get('/different/in-park')
            ->assertStatus(301)
            ->assertRedirect('/moment/in-park');
    }

    public function testEverySectionRedirectsToItsFirstPoem(): void
    {
        $catalog = app(PoemCatalog::class);

        foreach ($catalog->sections() as $sectionSlug => $section) {
            $firstPoem = $section['poems'][0];

            $this->get(route('section', ['section' => $sectionSlug], false))
                ->assertRedirect(route('poem', [
                    'section' => $sectionSlug,
                    'slug' => $firstPoem['slug'],
                ]));
        }
    }

    public function testUnknownAndMismatchedPagesReturnNotFound(): void
    {
        foreach (['/unknown', '/different/unknown', '/semicolon/two-monkeys'] as $path) {
            $this->get($path)
                ->assertNotFound()
                ->assertSee('<body class="error-layout">', false)
                ->assertSee('<header id="bar">', false)
                ->assertSee('<footer id="footer">', false)
                ->assertSee('<div id="royklogo"', false)
                ->assertSee('<h2>404 — Страница не найдена</h2>', false)
                ->assertSee('Такой страницы здесь нет — возможно, адрес изменился или в нём опечатка.')
                ->assertSee('Вернуться на обложку')
                ->assertDontSee('<nav id="leftbar"', false)
                ->assertDontSee('<ul id="pager"', false)
                ->assertDontSee('<dialog id="content"', false)
                ->assertDontSee('/js/script.js', false);
        }
    }

    public function testUnknownJsonPageKeepsLaravelJsonResponse(): void
    {
        $this->getJson('/unknown')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['message'])
            ->assertDontSee('Страница не найдена');
    }

    public function testCatalogMatchesPoemViews(): void
    {
        $catalog = app(PoemCatalog::class);
        $catalogPaths = [];

        self::assertSame(
            ['different', 'text', 'semicolon', 'moment'],
            array_keys($catalog->sections()),
        );

        foreach ($catalog->sections() as $sectionSlug => $section) {
            self::assertNotSame('', $sectionSlug);
            self::assertNotSame('', $section['title']);
            self::assertNotEmpty($section['poems']);

            foreach ($section['poems'] as $poem) {
                self::assertNotSame('', $poem['slug']);
                self::assertNotSame('', $poem['title']);
                $catalogPaths[] = "{$sectionSlug}/{$poem['slug']}";
            }
        }

        self::assertCount(55, $catalogPaths);
        self::assertCount(55, array_unique($catalogPaths));

        $viewPaths = [];

        foreach (File::allFiles(resource_path('views/poems')) as $view) {
            $relativePath = str_replace('\\', '/', $view->getRelativePathname());
            $viewPaths[] = substr($relativePath, 0, -strlen('.blade.php'));
        }

        sort($catalogPaths);
        sort($viewPaths);

        self::assertSame($catalogPaths, $viewPaths);

        $lastPoem = $catalog->poems()[54];
        self::assertSame('moment', $lastPoem['section']);
        self::assertSame('in-park', $lastPoem['slug']);
    }

    public function testNavigationKeepsItsWindowAroundTheCurrentPoem(): void
    {
        $catalog = app(PoemCatalog::class);

        $coverNavigation = $catalog->navigation();
        self::assertNull($coverNavigation['currentIndex']);
        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($coverNavigation['items']));

        $middleNavigation = $catalog->navigation('semicolon', 'absence');
        self::assertSame(23, $middleNavigation['currentIndex']);
        self::assertSame([21, 22, 23, 24, 25, 26], array_keys($middleNavigation['items']));

        $momentNavigation = $catalog->navigation('moment', 'moment');
        self::assertSame(40, $momentNavigation['currentIndex']);
        self::assertSame([38, 39, 40, 41, 42, 43], array_keys($momentNavigation['items']));

        $lastNavigation = $catalog->navigation('moment', 'in-park');
        self::assertSame(54, $lastNavigation['currentIndex']);
        self::assertSame([49, 50, 51, 52, 53, 54], array_keys($lastNavigation['items']));
    }

    public function testSitemapCanBeGeneratedFromCatalog(): void
    {
        $path = public_path('sitemap.xml');
        $originalUrl = config('app.url');
        File::delete($path);

        try {
            config(['app.url' => 'https://example.test']);

            self::assertSame(0, Artisan::call('sitemap:generate'));
            self::assertFileExists($path);

            $xml = File::get($path);
            self::assertSame(58, substr_count($xml, '<url>'));
            self::assertSame(58, substr_count($xml, '<loc>'));
            self::assertStringContainsString('https://example.test/', $xml);
            self::assertStringContainsString('https://example.test/author', $xml);
            self::assertStringContainsString('https://example.test/project', $xml);
            self::assertStringNotContainsString(
                'https://example.test/different/little-girl-pull-tablecloth',
                $xml,
            );
            self::assertStringNotContainsString(
                'https://example.test/different/about-soul',
                $xml,
            );
            self::assertStringNotContainsString(
                'https://example.test/different/in-park',
                $xml,
            );
            self::assertStringNotContainsString('<lastmod>', $xml);
            self::assertStringNotContainsString('<priority>', $xml);

            foreach (app(PoemCatalog::class)->poems() as $poem) {
                self::assertStringContainsString(
                    "https://example.test/{$poem['section']}/{$poem['slug']}",
                    $xml,
                );
            }
        } finally {
            File::delete($path);
            config(['app.url' => $originalUrl]);
        }
    }

    public function testSitemapRejectsInvalidAppUrl(): void
    {
        $path = public_path('sitemap.xml');
        $originalUrl = config('app.url');
        File::delete($path);

        try {
            config(['app.url' => 'not-a-url']);

            self::assertSame(1, Artisan::call('sitemap:generate'));
            self::assertFileDoesNotExist($path);
        } finally {
            config(['app.url' => $originalUrl]);
        }
    }
}
