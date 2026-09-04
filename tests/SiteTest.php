<?php

namespace Tests;

use App\PoemCatalog;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

class SiteTest extends TestCase
{
    private const HOME_DESCRIPTION = 'Сайт, посвящённый польской поэтессе Виславе Шимборской, — лауреату Нобелевской премии 1996 года. Представлены сборники Двоеточие, Мгновение и другие стихотворения и проза в разных переводах и на польском языке';

    private const PILOT_DESCRIPTIONS = [
        '/different/utopia' => '«Утопия» — стихотворение Виславы Шимборской. Пять русских переводов, в том числе Андрея Базилевского и Натальи Астафьевой, а также польский оригинал «Utopia».',
        '/different/cat-in-empty-apartment' => '«Кот в пустой квартире» — стихотворение Виславы Шимборской в переводе Натальи Астафьевой.',
        '/different/soliloquy-for-cassandra' => '«Монолог для Кассандры» — стихотворение Виславы Шимборской. Русские переводы Виктора Коркия и Асара Эппеля, а также польский оригинал «Monolog dla Kasandry».',
    ];

    public function testMainPageIsAvailable(): void
    {
        $fontVersion = filemtime(public_path('css/fonts.css'));
        $styleVersion = filemtime(public_path('css/style.css'));
        $scriptVersion = filemtime(public_path('js/script.js'));

        self::assertIsInt($fontVersion);
        self::assertIsInt($styleVersion);
        self::assertIsInt($scriptVersion);

        $xpath = self::htmlXPath($this->get('/')->assertOk()->getContent(), '/');

        foreach ([
            '//meta[@name="viewport"][@content="width=device-width, initial-scale=1"]',
            "//link[@rel=\"stylesheet\"][@href=\"/css/fonts.css?v={$fontVersion}\"]",
            "//link[@rel=\"stylesheet\"][@href=\"/css/style.css?v={$styleVersion}\"]",
            '//dialog[@id="content"]',
            "//script[@src=\"/js/script.js?v={$scriptVersion}\"]",
        ] as $query) {
            self::assertSame(1, $xpath->query($query)->length, $query);
        }

        self::assertSame(0, $xpath->query('//link[starts-with(@href, "/css/print.css")]')->length);
        self::assertSame(0, $xpath->query('//script[contains(translate(@src, "JQUERY", "jquery"), "jquery")]')->length);
    }

    public function testReadingFontsAreLocalVersionedAssets(): void
    {
        $fontCssPath = public_path('css/fonts.css');
        $fontCss = File::get($fontCssPath);
        $fontVersion = filemtime($fontCssPath);

        self::assertIsInt($fontVersion);
        $fontCss = preg_replace('~/\*.*?\*/~s', '', $fontCss);
        self::assertGreaterThan(0, preg_match_all('/@font-face\s*\{([^}]+)\}/i', $fontCss, $faces));
        $availableFaces = [];
        $fontDirectories = [];

        foreach ($faces[1] as $face) {
            preg_match_all('/(?:^|;)\s*([a-z-]+)\s*:\s*([^;]+)/i', $face, $declarations, PREG_SET_ORDER);
            $properties = [];

            foreach ($declarations as $declaration) {
                $properties[strtolower($declaration[1])] = trim($declaration[2], " \t\r\n\"'");
            }

            self::assertSame('swap', $properties['font-display'] ?? null, $face);
            $availableFaces[] = [
                $properties['font-family'] ?? '',
                $properties['font-style'] ?? 'normal',
                $properties['font-weight'] ?? '400',
            ];
            self::assertGreaterThan(0, preg_match_all(
                '~url\(\s*["\']?([^"\'()\s]+)["\']?\s*\)~i',
                $properties['src'] ?? '',
                $urls,
            ), $face);

            foreach ($urls[1] as $fontUrl) {
                self::assertMatchesRegularExpression('~^/(?!/)[^?#]+\.woff2(?:[?#].*)?$~', $fontUrl);
                $fontPath = public_path(ltrim(parse_url($fontUrl, PHP_URL_PATH), '/'));
                self::assertFileExists($fontPath, $fontUrl);
                self::assertSame('wOF2', substr(File::get($fontPath), 0, 4), $fontUrl);
                $fontDirectories[] = dirname($fontPath);
            }
        }

        foreach ([['PT Serif', 'normal', '400'], ['PT Serif', 'italic', '400'], ['PT Serif', 'normal', '700']] as $face) {
            self::assertContains($face, $availableFaces, implode(' ', $face));
        }

        foreach (array_unique($fontDirectories) as $directory) {
            self::assertFileExists("{$directory}/OFL.txt");
            self::assertFileExists("{$directory}/SOURCE.txt");
        }

        $fontStylesheet = "/css/fonts.css?v={$fontVersion}";

        foreach (['/' => 200, '/unknown' => 404] as $path => $status) {
            $xpath = self::htmlXPath($this->get($path)->assertStatus($status)->getContent(), $path);
            self::assertSame(1, $xpath->query("//link[@rel=\"stylesheet\"][@href=\"{$fontStylesheet}\"]")->length);
        }
    }

    public function testIndexablePagesDeclareCanonicalUrls(): void
    {
        $originalUrl = config('app.url');

        try {
            config(['app.url' => 'https://example.test/']);

            $pages = [
                '/?utm_source=test' => 'https://example.test/',
                '/author?utm_source=test' => 'https://example.test/author',
                '/project' => 'https://example.test/project',
            ];

            foreach (app(PoemCatalog::class)->poems() as $poem) {
                $path = "/{$poem['section']}/{$poem['slug']}";
                $pages[$path] = "https://example.test{$path}";
            }

            foreach ($pages as $path => $canonicalUrl) {
                $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
                $canonicals = $xpath->query('//head/link[@rel="canonical"]');

                self::assertSame(1, $canonicals->length, $path);
                self::assertSame($canonicalUrl, $canonicals->item(0)->getAttribute('href'), $path);
            }
        } finally {
            config(['app.url' => $originalUrl]);
        }
    }

    public function testIndexablePagesExposeExpectedTitlesAndSeoDescriptions(): void
    {
        $pages = [
            '/' => [
                'title' => 'Вислава Шимборская. Стихотворения',
                'description' => self::HOME_DESCRIPTION,
            ],
            '/author' => [
                'title' => 'Вислава Шимборская. Об авторе',
                'description' => null,
            ],
            '/project' => [
                'title' => 'Вислава Шимборская. О проекте',
                'description' => null,
            ],
        ];

        foreach (app(PoemCatalog::class)->poems() as $poem) {
            $path = "/{$poem['section']}/{$poem['slug']}";
            $pages[$path] = [
                'title' => "Вислава Шимборская. {$poem['title']}",
                'description' => self::PILOT_DESCRIPTIONS[$path] ?? null,
            ];
        }

        foreach ($pages as $path => $expected) {
            $content = $this->get($path)->assertOk()->getContent();
            $document = new DOMDocument;
            $previousUseInternalErrors = libxml_use_internal_errors(true);

            try {
                self::assertTrue($document->loadHTML($content), $path);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previousUseInternalErrors);
            }

            $xpath = new DOMXPath($document);
            $titles = $xpath->query('//head/title');
            $descriptions = $xpath->query('//head/meta[@name="description"]');

            self::assertSame(1, $titles->length, "{$path}: title count");
            self::assertSame($expected['title'], $titles->item(0)?->textContent, "{$path}: title");

            if ($expected['description'] === null) {
                self::assertSame(0, $descriptions->length, "{$path}: description count");

                continue;
            }

            self::assertSame(1, $descriptions->length, "{$path}: description count");
            self::assertSame(
                $expected['description'],
                $descriptions->item(0)?->attributes?->getNamedItem('content')?->nodeValue,
                "{$path}: description",
            );
        }
    }

    public function testCatalogNormalizesOptionalSeoDescriptions(): void
    {
        $catalog = app(PoemCatalog::class);
        $rawDescriptions = [];

        foreach ($catalog->sections() as $sectionSlug => $section) {
            foreach ($section['poems'] as $poem) {
                if (!array_key_exists('description', $poem)) {
                    continue;
                }

                self::assertIsString($poem['description']);
                self::assertNotSame('', trim($poem['description']));
                $rawDescriptions["/{$sectionSlug}/{$poem['slug']}"] = $poem['description'];
            }
        }

        $expectedDescriptions = self::PILOT_DESCRIPTIONS;
        ksort($rawDescriptions);
        ksort($expectedDescriptions);

        self::assertSame($expectedDescriptions, $rawDescriptions);

        foreach ($catalog->poems() as $poem) {
            $path = "/{$poem['section']}/{$poem['slug']}";

            self::assertArrayHasKey('description', $poem, $path);
            self::assertSame(self::PILOT_DESCRIPTIONS[$path] ?? null, $poem['description'], $path);
            self::assertSame($poem, $catalog->find($poem['section'], $poem['slug']), $path);
        }
    }

    public function testStaticPagesUseTheExpectedTypography(): void
    {
        $violations = [];

        foreach (['/' => 'О сайте', '/author' => 'Примечания', '/project' => 'Примечания'] as $path => $notesLabel) {
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            $visibleText = '';

            foreach ($xpath->query('//*[@id="page-content"] | //footer[@id="footer"]') as $node) {
                $visibleText .= $node->textContent;
            }

            self::collectStaticTypographyViolations($violations, $visibleText, $path);
            self::collectSupplementaryContentViolations($violations, $xpath, $path, $notesLabel);
            $notes = $xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " notabene ")]');

            if ($path === '/') {
                self::assertSame(1, $notes->length, 'The main page must retain its site note.');
            }

            foreach ($notes as $index => $note) {
                self::collectNarrowNoteTypographyViolations($violations, $note->textContent, "{$path}, notes {$index}");
            }
        }

        self::assertSame([], $violations);
    }

    public function testBladeViewsUseUtf8CharactersForVisibleTypography(): void
    {
        $allowedEntities = [
            '&nbsp;',
            '&thinsp;',
            '&amp;',
            '&lt;',
            '&gt;',
            '&quot;',
            '&apos;',
        ];
        $violations = [];

        foreach (File::allFiles(resource_path('views')) as $view) {
            if (!str_ends_with($view->getFilename(), '.blade.php')) {
                continue;
            }

            $source = File::get($view->getPathname());
            preg_match_all(
                '/&(?:#(?:\d+|[xX][0-9A-Fa-f]+)|[A-Za-z][A-Za-z0-9]+);/',
                $source,
                $matches,
            );
            $disallowedEntities = array_values(array_diff(array_unique($matches[0]), $allowedEntities));

            if ($disallowedEntities === []) {
                continue;
            }

            $relativePath = str_replace('\\', '/', $view->getRelativePathname());
            $violations[$relativePath] = $disallowedEntities;
        }

        self::assertSame([], $violations);
    }

    public function testPrintStylesAreOnlyLoadedForPrintablePages(): void
    {
        $printVersion = filemtime(public_path('css/print.css'));

        self::assertIsInt($printVersion);

        foreach (['/different/two-monkeys', '/author', '/project'] as $path) {
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            $styles = $xpath->query('//link[starts-with(@href, "/css/print.css")]');
            self::assertSame(1, $styles->length, $path);
            self::assertSame('stylesheet', $styles->item(0)->getAttribute('rel'), $path);
            self::assertSame('print', $styles->item(0)->getAttribute('media'), $path);
            self::assertSame("/css/print.css?v={$printVersion}", $styles->item(0)->getAttribute('href'), $path);
        }

        foreach (['/' => 200, '/unknown' => 404] as $path => $status) {
            $xpath = self::htmlXPath($this->get($path)->assertStatus($status)->getContent(), $path);
            self::assertSame(0, $xpath->query('//link[starts-with(@href, "/css/print.css")]')->length, $path);
        }

        self::assertFileExists(public_path('css/print.css'));
    }

    public function testAccessiblePageAndNavigationSemanticsAreRendered(): void
    {
        $poems = app(PoemCatalog::class)->poems();
        $firstPoem = $poems[0];
        $secondPoem = $poems[1];

        $xpath = self::htmlXPath($this->get('/')->assertOk()->getContent(), '/');

        foreach ([
            '//a[contains(concat(" ", normalize-space(@class), " "), " skip-link ")][@href="#page-content"][normalize-space(.)="Перейти к основному содержанию"]',
            '//h1/span[contains(concat(" ", normalize-space(@class), " "), " visually-hidden ")][normalize-space(.)="·"]',
            '//h1/span[contains(concat(" ", normalize-space(@class), " "), " book-title ")][contains(concat(" ", normalize-space(@class), " "), " visually-hidden-mobile ")][normalize-space(.)="Стихотворения"]',
            '//article[@id="page-content"][@tabindex="-1"][contains(concat(" ", normalize-space(@class), " "), " page ")]',
            '//nav[@aria-label="Постраничная навигация"]',
            '//*[@id="pager"]//*[@aria-current="page"][@aria-label="Текущая страница — Обложка"]',
        ] as $query) {
            self::assertSame(1, $xpath->query($query)->length, $query);
        }

        $coverLink = $xpath->query('//*[@id="page-content"]//a[img]');
        self::assertSame(1, $coverLink->length);
        self::assertSame(
            "Вислава Шимборская. Обложка — перейти к стихотворению «{$firstPoem['title']}»",
            $coverLink->item(0)->getAttribute('aria-label'),
        );

        $path = "/{$firstPoem['section']}/{$firstPoem['slug']}";
        $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
        $current = $xpath->query('//*[@id="pager"]//span[@aria-current="page"]');
        self::assertSame(1, $current->length);
        self::assertSame('1', trim($current->item(0)->textContent));
        self::assertSame("Текущая страница 1 — {$firstPoem['title']}", $current->item(0)->getAttribute('aria-label'));

        $nextUrl = route('poem', ['section' => $secondPoem['section'], 'slug' => $secondPoem['slug']]);
        $next = $xpath->query("//*[@id=\"pager\"]//a[@href=\"{$nextUrl}\"]");
        self::assertSame(1, $next->length);
        self::assertSame("Страница 2 — {$secondPoem['title']}", $next->item(0)->getAttribute('title'));
        self::assertSame("Страница 2 — {$secondPoem['title']}", $next->item(0)->getAttribute('aria-label'));

        $contentsCurrent = $xpath->query('//dialog[@id="content"]//span[@aria-current="page"][contains(concat(" ", normalize-space(@class), " "), " active ")]');
        self::assertSame(1, $contentsCurrent->length);
        self::assertSame("Текущая страница — {$firstPoem['title']}", $contentsCurrent->item(0)->getAttribute('aria-label'));

        foreach (['/author' => 'Об авторе', '/project' => 'О проекте'] as $path => $title) {
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            self::assertSame(1, $xpath->query("//*[@aria-current=\"page\"][@aria-label=\"Текущая страница — {$title}\"]")->length, $path);
        }

        $xpath = self::htmlXPath($this->get('/unknown')->assertNotFound()->getContent(), '/unknown');
        self::assertSame(1, $xpath->query('//article[@id="page-content"][@tabindex="-1"][contains(concat(" ", normalize-space(@class), " "), " page ")][contains(concat(" ", normalize-space(@class), " "), " error-page ")]')->length);
    }

    public function testKeyboardShortcutPlaceholdersAreRenderedWithoutPlatformLabels(): void
    {
        $xpath = self::htmlXPath($this->get('/different/two-monkeys')->assertOk()->getContent());

        foreach (['cover', 'contents'] as $shortcut) {
            $placeholders = $xpath->query("//span[@data-shortcut=\"{$shortcut}\"][contains(concat(' ', normalize-space(@class), ' '), ' shortkey ')]");
            self::assertSame(1, $placeholders->length, $shortcut);
            self::assertSame('', trim($placeholders->item(0)->textContent), $shortcut);
        }

        $text = $xpath->query('//body')->item(0)->textContent;
        self::assertStringNotContainsString('(ctrl +', $text);
        self::assertStringNotContainsString('⌃⇧', $text);
    }

    public function testCatalogPagesMeetContentAndTypographyContracts(): void
    {
        $violations = [];

        foreach (app(PoemCatalog::class)->poems() as $poem) {
            $path = "/{$poem['section']}/{$poem['slug']}";
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            self::collectCatalogPageViolations($violations, $xpath, $path);
        }

        self::assertSame([], $violations);
    }

    public function testIllustrationViewerUsesProgressiveEnhancement(): void
    {
        $xpath = self::htmlXPath($this->get('/different/atlantis')->assertOk()->getContent(), '/different/atlantis');
        $links = $xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " illustrations ")]//a[@data-illustration]');
        self::assertGreaterThan(0, $links->length);

        foreach ($links as $link) {
            self::assertNotSame('', trim($link->getAttribute('href')), 'The enlargement must work as an ordinary link.');
            self::assertSame(1, $xpath->query('.//img[@src]', $link)->length);
            $thumbnail = $xpath->query('.//img', $link)->item(0);
            self::assertNotSame('', trim($thumbnail->getAttribute('id')));
            self::assertNotSame('', trim($thumbnail->getAttribute('alt')));
            self::assertSame($thumbnail->getAttribute('id'), $link->getAttribute('aria-describedby'));
            self::assertNotSame('', trim($link->getAttribute('aria-label')));
            self::assertStringNotContainsString($thumbnail->getAttribute('alt'), $link->getAttribute('aria-label'));
            self::assertNotSame('', trim($link->getAttribute('data-illustration-title')));
            self::assertNotSame($thumbnail->getAttribute('alt'), $link->getAttribute('data-illustration-title'));
            self::assertSame('', trim($link->textContent), 'The link uses aria-label without duplicate hidden text.');
        }

        self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"][@aria-labelledby="illustration-title"][not(@open)]')->length);
        self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"]//*[@id="illustration-title"]')->length);
        self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"]//button[@type="button"][@aria-label="Закрыть изображение"][@autofocus]')->length);
        self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"]//*[@role="status"]')->length);
        self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"]//*[@role="alert"]//a')->length);
        self::assertSame(0, $xpath->query('//dialog[@id="illustration-dialog"]//img')->length, 'The full image must not be requested before opening.');
        $captions = $xpath->query('//dialog[@id="illustration-dialog"]//*[contains(concat(" ", normalize-space(@class), " "), " illustration-caption ")]');
        self::assertSame(1, $captions->length);
        self::assertSame('', trim($captions->item(0)->textContent), 'Captions are copied from the illustration, not duplicated in Blade.');
        self::assertSame(0, $xpath->query('./*', $captions->item(0))->length);
    }

    public function testIllustrationViewerDoesNotChangeOtherImageLinks(): void
    {
        foreach (['/different/two-monkeys', '/moment/ball'] as $path) {
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            self::assertSame(1, $xpath->query('//dialog[@id="illustration-dialog"]')->length, $path);
            self::assertSame(0, $xpath->query('//*[@data-illustration]')->length, $path);
        }

        foreach (['/', '/project', '/author', '/moment/everything'] as $path) {
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            self::assertSame(0, $xpath->query('//*[@id="illustration-dialog"]')->length, $path);
            self::assertSame(0, $xpath->query('//*[@data-illustration]')->length, $path);
        }

        $xpath = self::htmlXPath($this->get('/unknown')->assertNotFound()->getContent(), '/unknown');
        self::assertSame(0, $xpath->query('//*[@id="illustration-dialog"]')->length);
        $xpath = self::htmlXPath($this->get('/')->assertOk()->getContent(), '/');
        $coverUrl = route('poem', ['section' => 'different', 'slug' => 'two-monkeys']);
        self::assertSame(1, $xpath->query("//*[@id=\"page-content\"]//a[img][@href=\"{$coverUrl}\"][@aria-label][not(@data-illustration)]")->length);
    }

    public function testNarrowNoteTypographyValidatorDetectsBreakableGroups(): void
    {
        $violations = [];

        self::collectNarrowNoteTypographyViolations(
            $violations,
            'Текст в колонке, 2001 года, 12 до н. э.',
            '/fixture, notes 0',
        );

        self::assertSame([], array_diff([
            'notes: breakable short service word',
            'notes: breakable year designation',
            'notes: breakable era abbreviation',
        ], array_keys($violations)));
    }

    public function testContentValidatorDetectsStructuralViolations(): void
    {
        $xpath = self::htmlXPath(<<<'HTML'
            <!doctype html>
            <html lang="ru">
            <head><meta charset="utf-8"></head>
            <body>
                <article id="page-content">
                    <h2>Тест</h2>
                    <div class="poem"><p>Русское слово powabуw.</p><a id="tonote001" href="#note999" role="doc-noteref">1</a></div>
                    <div class="poem"><h3>Polski tekst</h3><p>Polski tekst.</p></div>
                    <div class="poem" lang="pl"><h3>Drugi tekst</h3><p>Drugi tekst.</p></div>
                </article>
                <aside class="notabene" aria-label="Примечания"></aside>
                <aside class="illustrations" aria-label="Иллюстрации"><img src="/images/missing.jpg" alt=""></aside>
            </body>
            </html>
            HTML, '/fixture');
        $violations = [];

        self::collectCatalogPageViolations($violations, $xpath, '/fixture');

        self::assertSame([], array_diff([
            'common: mixed alphabets in a word',
            'structure: Polish version position',
            'language: missing lang=pl',
            'notes: missing target',
            'landmarks: empty aside',
            'images: empty alt',
        ], array_keys($violations)));
    }

    public function testContentsDialogUsesCatalogOrderInTwoColumns(): void
    {
        $xpath = self::htmlXPath($this->get('/')->assertOk()->getContent(), '/');
        $columns = $xpath->query('//div[@id="contents-wrap"]/ul[contains(concat(" ", normalize-space(@class), " "), " contents-column ")]');

        self::assertSame(
            2,
            $columns->length,
        );

        $sectionsByColumn = [];

        foreach ($columns as $column) {
            $sectionSlugs = [];

            foreach ($xpath->query('./li[@data-section]', $column) as $section) {
                $sectionSlugs[] = $section->getAttribute('data-section');
            }

            $sectionsByColumn[] = $sectionSlugs;
        }

        $sectionSlugs = array_keys(app(PoemCatalog::class)->sections());
        self::assertSame([
            array_slice($sectionSlugs, 0, 1),
            array_slice($sectionSlugs, 1),
        ], $sectionsByColumn);
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

        $this->get('/different/picture-september-11')
            ->assertStatus(301)
            ->assertRedirect('/moment/picture-september-11');

        $this->get('/different/ball')
            ->assertStatus(301)
            ->assertRedirect('/moment/ball');

        $this->get('/different/note')
            ->assertStatus(301)
            ->assertRedirect('/moment/note');
    }

    public function testSuccessfulHtmlPathsRedirectTrailingSlashPermanently(): void
    {
        $canonicalPaths = ['/author', '/project'];

        foreach (app(PoemCatalog::class)->poems() as $poem) {
            $canonicalPaths[] = "/{$poem['section']}/{$poem['slug']}";
        }

        foreach ($canonicalPaths as $path) {
            $response = $this->requestWithRawUri('GET', "{$path}/");

            self::assertSame(301, $response->getStatusCode(), $path);
            $response->assertRedirect($path);
        }

        foreach (array_keys(app(PoemCatalog::class)->sections()) as $sectionSlug) {
            $this->requestWithRawUri('GET', "/{$sectionSlug}/")
                ->assertStatus(301)
                ->assertRedirect("/{$sectionSlug}");
        }

        $this->requestWithRawUri('GET', '/different/little-girl-pull-tablecloth/')
            ->assertStatus(301)
            ->assertRedirect('/different/little-girl-pull-tablecloth');

        $this->requestWithRawUri('HEAD', '/author/')
            ->assertStatus(301)
            ->assertRedirect('/author');

        $this->requestWithRawUri('GET', '/author/?utm_source=test&source=smoke')
            ->assertStatus(301)
            ->assertRedirect('/author?utm_source=test&source=smoke');

        $this->get('/')->assertOk();
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
        $requiredElements = [
            '//body[contains(concat(" ", normalize-space(@class), " "), " error-layout ")]',
            '//header[@id="bar"]',
            '//footer[@id="footer"]',
            '//*[@id="royklogo"]',
            '//h2[starts-with(normalize-space(.), "404")]',
            '//a[@href="' . route('main') . '"]',
        ];
        $forbiddenElements = [
            '//nav[@id="leftbar"]',
            '//*[@id="pager"]',
            '//dialog[@id="content"]',
            '//script[starts-with(@src, "/js/script.js")]',
            '//link[@rel="canonical"]',
            '//meta[@name="description"]',
        ];
        $violations = [];

        foreach ([
            '/unknown',
            '/unknown/',
            '/different/unknown',
            '/different/unknown/',
            '/semicolon/two-monkeys',
            '/semicolon/two-monkeys/',
        ] as $path) {
            $response = str_ends_with($path, '/')
                ? $this->requestWithRawUri('GET', $path)
                : $this->get($path);
            $xpath = self::htmlXPath($response->getContent(), $path);

            if ($response->getStatusCode() !== 404) {
                $violations[] = "{$path}: expected status 404, got {$response->getStatusCode()}";
            }

            foreach ($requiredElements as $query) {
                if ($xpath->query($query)->length === 0) {
                    $violations[] = "{$path}: missing {$query}";
                }
            }

            foreach ($forbiddenElements as $query) {
                if ($xpath->query($query)->length !== 0) {
                    $violations[] = "{$path}: unexpectedly contains {$query}";
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testUnknownJsonPageKeepsLaravelJsonResponse(): void
    {
        foreach (['/unknown', '/unknown/'] as $path) {
            $response = str_ends_with($path, '/')
                ? $this->requestWithRawUri('GET', $path, [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                ])
                : $this->getJson($path);

            $response
                ->assertNotFound()
                ->assertHeader('content-type', 'application/json')
                ->assertJsonStructure(['message']);
        }
    }

    public function testCatalogMatchesPoemViews(): void
    {
        $catalog = app(PoemCatalog::class);
        $catalogPaths = [];
        $metadataViolations = [];

        self::assertSame(
            ['different', 'semicolon', 'moment', 'text'],
            array_keys($catalog->sections()),
        );

        foreach ($catalog->sections() as $sectionSlug => $section) {
            if ($sectionSlug === '') {
                $metadataViolations[] = 'Section slug is empty';
            }

            if ($section['title'] === '') {
                $metadataViolations[] = "{$sectionSlug}: section title is empty";
            }

            if ($section['poems'] === []) {
                $metadataViolations[] = "{$sectionSlug}: section has no poems";
            }

            foreach ($section['poems'] as $poem) {
                if ($poem['slug'] === '') {
                    $metadataViolations[] = "{$sectionSlug}: poem slug is empty";
                }

                if ($poem['title'] === '') {
                    $metadataViolations[] = "{$sectionSlug}/{$poem['slug']}: poem title is empty";
                }

                $catalogPaths[] = "{$sectionSlug}/{$poem['slug']}";
            }
        }

        self::assertSame([], $metadataViolations);
        self::assertSame(count($catalogPaths), count(array_unique($catalogPaths)));

        $viewPaths = [];

        foreach (File::allFiles(resource_path('views/poems')) as $view) {
            $relativePath = str_replace('\\', '/', $view->getRelativePathname());
            $viewPaths[] = substr($relativePath, 0, -strlen('.blade.php'));
        }

        sort($catalogPaths);
        sort($viewPaths);

        self::assertSame($catalogPaths, $viewPaths);
    }

    public function testNavigationKeepsItsWindowAroundTheCurrentPoem(): void
    {
        $catalog = app(PoemCatalog::class);
        $poems = $catalog->poems();
        $lastIndex = count($poems) - 1;

        $coverNavigation = $catalog->navigation();
        self::assertNull($coverNavigation['currentIndex']);
        self::assertSame(array_keys(array_slice($poems, 0, 6, true)), array_keys($coverNavigation['items']));
        self::assertSame(array_keys(array_slice($poems, 0, 5, true)), array_keys($coverNavigation['compactItems']));

        $firstPoem = $poems[0];
        $firstNavigation = $catalog->navigation($firstPoem['section'], $firstPoem['slug']);
        self::assertSame(0, $firstNavigation['currentIndex']);
        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($firstNavigation['items']));
        self::assertSame([0, 1, 2, 3, 4], array_keys($firstNavigation['compactItems']));

        $middleIndex = intdiv($lastIndex, 2);
        $middlePoem = $poems[$middleIndex];
        $middleNavigation = $catalog->navigation($middlePoem['section'], $middlePoem['slug']);
        self::assertSame($middleIndex, $middleNavigation['currentIndex']);
        self::assertSame(
            range($middleIndex - 2, $middleIndex + 3),
            array_keys($middleNavigation['items']),
        );
        self::assertSame(
            range($middleIndex - 2, $middleIndex + 2),
            array_keys($middleNavigation['compactItems']),
        );

        $lastPoem = $poems[$lastIndex];
        $lastNavigation = $catalog->navigation($lastPoem['section'], $lastPoem['slug']);
        self::assertSame($lastIndex, $lastNavigation['currentIndex']);
        self::assertSame(
            range($lastIndex - 5, $lastIndex),
            array_keys($lastNavigation['items']),
        );
        self::assertSame(
            range($lastIndex - 4, $lastIndex),
            array_keys($lastNavigation['compactItems']),
        );
    }

    public function testPagerMarksOnlyTheItemOutsideTheCompactWindow(): void
    {
        $catalog = app(PoemCatalog::class);
        $poems = $catalog->poems();

        foreach ([$poems[0], $poems[intdiv(count($poems) - 1, 2)], $poems[array_key_last($poems)]] as $poem) {
            $path = "/{$poem['section']}/{$poem['slug']}";
            $xpath = self::htmlXPath($this->get($path)->assertOk()->getContent(), $path);
            $navigation = $catalog->navigation($poem['section'], $poem['slug']);
            $expectedExtraPages = array_map(
                static fn (int $index): int => $index + 1,
                array_keys(array_diff_key($navigation['items'], $navigation['compactItems'])),
            );
            $extraPages = [];

            foreach ($xpath->query('//*[@id="pager"]/li[contains(concat(" ", normalize-space(@class), " "), " pager-compact-extra ")]') as $item) {
                $extraPages[] = (int) trim($item->textContent);
                self::assertSame(0, $xpath->query('.//*[@aria-current="page"]', $item)->length, "{$path}: current page is hidden");
            }

            self::assertSame($expectedExtraPages, $extraPages, $path);
            $current = $xpath->query('//*[@id="pager"]//*[@aria-current="page"]');
            self::assertSame(1, $current->length, $path);
            self::assertSame((string) ($navigation['currentIndex'] + 1), trim($current->item(0)->textContent), $path);
            self::assertSame(
                'Текущая страница ' . ($navigation['currentIndex'] + 1) . " — {$poem['title']}",
                $current->item(0)->getAttribute('aria-label'),
                $path,
            );
        }
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
            $document = new DOMDocument;
            self::assertTrue($document->loadXML($xml));
            $xpath = new DOMXPath($document);
            $actualUrls = [];

            foreach ($xpath->query('//*[local-name()="url"]/*[local-name()="loc"]') as $location) {
                $actualUrls[] = $location->textContent;
            }

            $expectedUrls = [
                'https://example.test/',
                'https://example.test/author',
                'https://example.test/project',
            ];

            foreach (app(PoemCatalog::class)->poems() as $poem) {
                $expectedUrls[] = "https://example.test/{$poem['section']}/{$poem['slug']}";
            }

            sort($actualUrls);
            sort($expectedUrls);

            self::assertSame($expectedUrls, $actualUrls);
            self::assertSame([
                'lastmod' => 0,
                'priority' => 0,
            ], [
                'lastmod' => $xpath->query('//*[local-name()="lastmod"]')->length,
                'priority' => $xpath->query('//*[local-name()="priority"]')->length,
            ]);
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

    private static function htmlXPath(string $html, string $context = ''): DOMXPath
    {
        $document = new DOMDocument;
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            self::assertTrue($document->loadHTML($html), $context);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        return new DOMXPath($document);
    }

    /**
     * @param  array<string, mixed>  $server
     */
    private function requestWithRawUri(string $method, string $uri, array $server = []): TestResponse
    {
        $kernel = $this->app->make(HttpKernel::class);
        $url = rtrim((string) config('app.url'), '/') . $uri;
        $request = Request::create($url, $method, server: $server);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return TestResponse::fromBaseResponse($response, $request);
    }

    private static function collectCatalogPageViolations(
        array &$violations,
        DOMXPath $xpath,
        string $path,
    ): void {
        $pageContents = $xpath->query('//*[@id="page-content"]');

        if ($pageContents->length !== 1) {
            $violations['structure: page content'][] = "{$path}: expected 1 #page-content, got {$pageContents->length}";
        }

        $pageContent = $pageContents->item(0);

        if ($pageContent === null) {
            return;
        }

        $headings = $xpath->query('./h2', $pageContent);

        if ($headings->length !== 1) {
            $violations['structure: page h2'][] = "{$path}: expected 1 direct h2, got {$headings->length}";
        }

        foreach ($headings as $headingIndex => $heading) {
            self::collectTypographyViolations(
                $violations,
                $heading->textContent,
                'ru',
                "{$path}, h2 {$headingIndex}",
            );
        }

        $versions = $xpath->query(
            './div['
            . 'contains(concat(" ", normalize-space(@class), " "), " poem ")'
            . ' or contains(concat(" ", normalize-space(@class), " "), " text ")'
            . ']',
            $pageContent,
        );

        if ($versions->length === 0) {
            $violations['structure: content versions'][] = "{$path}: no poem or text versions";
        }

        $polishVersionIndexes = [];

        foreach ($versions as $versionIndex => $version) {
            $class = $version->attributes?->getNamedItem('class')?->nodeValue ?? '';
            $language = $version->attributes?->getNamedItem('lang')?->nodeValue ?? '';
            $text = $version->textContent;
            $headings3 = $xpath->query('./h3', $version);
            $isPoem = preg_match('/(^|\s)poem(\s|$)/', $class) === 1;

            if ($versionIndex === 0 && $isPoem && $headings3->length !== 0) {
                $violations['structure: first poem heading'][] = "{$path}: first poem version must use the page h2";
            }

            if ($versionIndex > 0 && ($isPoem || $language === 'pl') && $headings3->length === 0) {
                $violations['structure: subsequent version heading'][] = "{$path}: version {$versionIndex} has no direct h3";
            }

            $hasLatin = preg_match('/\p{Latin}/u', $text) === 1;
            $hasCyrillic = preg_match('/\p{Cyrillic}/u', $text) === 1;
            $isPolishByScript = $hasLatin && !$hasCyrillic;

            if ($isPolishByScript && $language !== 'pl') {
                $violations['language: missing lang=pl'][] = "{$path}: Latin-only version {$versionIndex}";
            }

            if (!$isPolishByScript && $language === 'pl') {
                $violations['language: unexpected lang=pl'][] = "{$path}: version {$versionIndex} contains Cyrillic or no Latin text";
            }

            if ($language !== '' && $language !== 'pl') {
                $violations['language: unsupported declaration'][] = "{$path}: version {$versionIndex} declares {$language}";
            }

            if ($language === 'pl') {
                $polishVersionIndexes[] = $versionIndex;
            }

            self::collectTypographyViolations(
                $violations,
                $text,
                $language === 'pl' ? 'pl' : 'ru',
                "{$path}, version {$versionIndex}",
            );
        }

        if ($polishVersionIndexes !== [] && $polishVersionIndexes !== [1]) {
            $violations['structure: Polish version position'][] = "{$path}: indexes " . implode(', ', $polishVersionIndexes);
        }

        foreach ($xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " notabene ")]') as $notesIndex => $notes) {
            self::collectTypographyViolations(
                $violations,
                $notes->textContent,
                'ru',
                "{$path}, notes {$notesIndex}",
            );
            self::collectNarrowNoteTypographyViolations(
                $violations,
                $notes->textContent,
                "{$path}, notes {$notesIndex}",
            );
        }

        self::collectNoteViolations($violations, $xpath, $path);
        self::collectSupplementaryContentViolations($violations, $xpath, $path, 'Примечания');
    }

    private static function collectNoteViolations(
        array &$violations,
        DOMXPath $xpath,
        string $path,
    ): void {
        $noterefIds = [];

        foreach ($xpath->query('//*[@role="doc-noteref"]') as $noteref) {
            $id = $noteref->attributes?->getNamedItem('id')?->nodeValue ?? '';

            if (preg_match('/^tonote(\d{3})$/', $id, $matches) !== 1) {
                $violations['notes: invalid noteref ID'][] = "{$path}: {$id}";

                continue;
            }

            $noteId = $matches[1];
            $noterefIds[] = $noteId;

            if (($noteref->attributes?->getNamedItem('href')?->nodeValue ?? '') !== "#note{$noteId}") {
                $violations['notes: invalid target href'][] = "{$path}: {$id}";
            }

            if ($xpath->query("//*[@id=\"note{$noteId}\" and @role=\"doc-footnote\"]")->length !== 1) {
                $violations['notes: missing target'][] = "{$path}: note{$noteId}";
            }
        }

        $footnoteIds = [];
        $backlinkIds = [];

        foreach ($xpath->query('//*[@role="doc-footnote"]') as $footnote) {
            $id = $footnote->attributes?->getNamedItem('id')?->nodeValue ?? '';

            if (preg_match('/^note(\d{3})$/', $id, $matches) !== 1) {
                $violations['notes: invalid footnote ID'][] = "{$path}: {$id}";

                continue;
            }

            $noteId = $matches[1];
            $footnoteIds[] = $noteId;

            if (($footnote->attributes?->getNamedItem('tabindex')?->nodeValue ?? '') !== '-1') {
                $violations['notes: missing footnote tabindex'][] = "{$path}: {$id}";
            }

            $backlinks = $xpath->query(
                './/a[contains(concat(" ", normalize-space(@class), " "), " note-backlink ")]',
                $footnote,
            );

            if ($backlinks->length !== 1) {
                $violations['notes: backlink count'][] = "{$path}: {$id} has {$backlinks->length}";
            }

            foreach ($backlinks as $backlink) {
                $href = $backlink->attributes?->getNamedItem('href')?->nodeValue ?? '';

                if (preg_match('/^#tonote(\d{3})$/', $href, $backlinkMatches) === 1) {
                    $backlinkIds[] = $backlinkMatches[1];
                } else {
                    $violations['notes: invalid backlink href'][] = "{$path}: {$id}";
                }

                if (trim($backlink->attributes?->getNamedItem('aria-label')?->nodeValue ?? '') === '') {
                    $violations['notes: missing backlink label'][] = "{$path}: {$id}";
                }

                if (!str_ends_with($backlink->previousSibling?->textContent ?? '', "\u{00A0}")) {
                    $violations['notes: breakable backlink'][] = "{$path}: {$id}";
                }
            }

            if ($xpath->query(
                './p[last()]//a['
                . 'contains(concat(" ", normalize-space(@class), " "), " note-backlink ")'
                . " and @href=\"#tonote{$noteId}\"]",
                $footnote,
            )->length !== 1) {
                $violations['notes: backlink placement'][] = "{$path}: {$id}";
            }
        }

        sort($noterefIds);
        sort($footnoteIds);
        sort($backlinkIds);

        if ($noterefIds !== $footnoteIds || $noterefIds !== $backlinkIds) {
            $violations['notes: unmatched IDs'][] = "{$path}: noterefs="
                . implode(',', $noterefIds)
                . '; footnotes=' . implode(',', $footnoteIds)
                . '; backlinks=' . implode(',', $backlinkIds);
        }
    }

    private static function collectSupplementaryContentViolations(
        array &$violations,
        DOMXPath $xpath,
        string $path,
        string $notesLabel,
    ): void {
        foreach ($xpath->query(
            '//aside['
            . 'contains(concat(" ", normalize-space(@class), " "), " illustrations ")'
            . ' or contains(concat(" ", normalize-space(@class), " "), " notabene ")'
            . ']',
        ) as $aside) {
            $class = $aside->attributes?->getNamedItem('class')?->nodeValue ?? '';
            $isIllustrations = preg_match('/(^|\s)illustrations(\s|$)/', $class) === 1;
            $expectedLabel = $isIllustrations ? 'Иллюстрации' : $notesLabel;

            if (($aside->attributes?->getNamedItem('aria-label')?->nodeValue ?? '') !== $expectedLabel) {
                $violations['landmarks: accessible label'][] = "{$path}: {$class}";
            }

            if (trim($aside->textContent) === '' && $xpath->query('.//*', $aside)->length === 0) {
                $violations['landmarks: empty aside'][] = "{$path}: {$class}";
            }
        }

        foreach ($xpath->query(
            '//*[@id="page-content"]//img'
            . ' | //aside[contains(concat(" ", normalize-space(@class), " "), " illustrations ")]//img',
        ) as $image) {
            $alt = $image->attributes?->getNamedItem('alt')?->nodeValue;
            $src = $image->attributes?->getNamedItem('src')?->nodeValue ?? '';

            if ($alt === null || trim($alt) === '') {
                $violations['images: empty alt'][] = "{$path}: {$src}";
            }

            if ($src === '') {
                $violations['images: missing src'][] = $path;

                continue;
            }

            $srcPath = parse_url($src, PHP_URL_PATH);

            if (is_string($srcPath) && str_starts_with($srcPath, '/')) {
                $localPath = public_path(ltrim($srcPath, '/'));

                if (!File::exists($localPath)) {
                    $violations['images: missing local file'][] = "{$path}: {$srcPath}";
                }
            }
        }

        foreach ($xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " illustrations ")]//a[@data-illustration]') as $link) {
            $href = $link->getAttribute('href');
            $images = $xpath->query('.//img', $link);

            if (!str_starts_with($href, '/images/full/') || !str_ends_with($href, '.webp') || $images->length !== 1) {
                $violations['images: invalid enlargement link'][] = "{$path}: {$href}";

                continue;
            }

            $thumbnail = $images->item(0);

            if (trim($link->getAttribute('aria-label')) === ''
                || trim($link->getAttribute('data-illustration-title')) === ''
                || trim($thumbnail->getAttribute('id')) === ''
                || $link->getAttribute('aria-describedby') !== $thumbnail->getAttribute('id')) {
                $violations['images: invalid enlargement accessibility'][] = "{$path}: {$href}";
            }

            $fullPath = public_path(ltrim($href, '/'));

            if (!File::isFile($fullPath)) {
                $violations['images: missing full image'][] = "{$path}: {$href}";

                continue;
            }

            $fullSize = getimagesize($fullPath);
            $thumbnailPath = public_path(ltrim($images->item(0)->getAttribute('src'), '/'));
            $thumbnailSize = File::isFile($thumbnailPath) ? getimagesize($thumbnailPath) : false;

            if ($fullSize === false || $thumbnailSize === false || $fullSize[2] !== IMAGETYPE_WEBP
                || max($fullSize[0], $fullSize[1]) > 1600
                || $fullSize[0] <= $thumbnailSize[0] || $fullSize[1] <= $thumbnailSize[1]) {
                $violations['images: invalid full image dimensions or format'][] = "{$path}: {$href}";
            }
        }
    }

    private static function collectStaticTypographyViolations(
        array &$violations,
        string $text,
        string $path,
    ): void {
        $patterns = [
            'static: number without non-breaking space' => '/№(?!\x{A0}\d)/u',
            'static: invalid numeric range dash' => '/\d(?:-|—)\d/u',
            'static: spaced numeric range' => '/\d[\x{20}\x{A0}]+[—–-][\x{20}\x{A0}]+\d/u',
            'static: volume without non-breaking space' => '/Т\.(?!\x{A0}\d)/u',
            'static: abbreviation without non-breaking space' => '/\bпольск\.(?!\x{A0}\p{L})/u',
            'static: day without non-breaking month' => '/\b[0-3]?\d\x{20}(?:января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\b/u',
            'static: month without non-breaking year' => '/\b(?:января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря|январе|феврале|марте|апреле|мае|июне|июле|августе|сентябре|октябре|ноябре|декабре)\x{20}\d{4}\b/u',
            'static: issue year without non-breaking space' => '/\p{L}\x{20}\d{4},\x{A0}№/u',
            'static: breakable space before em dash' => '/\S\x{20}—(?=\s)/u',
            'static: copyright without non-breaking space' => '/©(?!\x{A0}\d)/u',
        ];

        foreach ($patterns as $rule => $pattern) {
            preg_match_all($pattern, $text, $matches);

            if ($matches[0] !== []) {
                $violations[$rule][] = "{$path}: " . implode(', ', array_unique($matches[0]));
            }
        }
    }

    private static function collectNarrowNoteTypographyViolations(
        array &$violations,
        string $text,
        string $context,
    ): void {
        $patterns = [
            'notes: breakable short service word' => '/(?<![\p{L}\p{M}-])(?:а|в|и|к|о|с|у|во|до|за|из|на|не|но|об|от|по|со)[\x{20}\t\r\n]+(?=[\p{L}\p{M}\d«„(])/iu',
            'notes: breakable year designation' => '/\b\d{4}(?:–\d{4})?[\x{20}\t\r\n]+(?:г\.|гг\.|года)(?=[\s.,)])/u',
            'notes: breakable era abbreviation' => '/\b(?:до[\x{20}\t\r\n]+н\.|н\.[\x{20}\t\r\n]+э\.)/u',
        ];

        foreach ($patterns as $rule => $pattern) {
            $result = preg_match_all($pattern, $text, $matches);

            if ($result === false) {
                $violations[$rule][] = "{$context}: pattern could not be evaluated";

                continue;
            }

            if ($matches[0] !== []) {
                $violations[$rule][] = "{$context}: " . implode(', ', array_unique($matches[0]));
            }
        }
    }

    private static function collectTypographyViolations(
        array &$violations,
        string $text,
        string $language,
        string $context,
    ): void {
        $recordMatches = static function (
            string $rule,
            string $pattern,
            string $subject,
        ) use (&$violations, $context): void {
            $result = preg_match_all($pattern, $subject, $matches);

            if ($result === false) {
                $violations[$rule][] = "{$context}: pattern could not be evaluated";

                return;
            }

            if ($matches[0] === []) {
                return;
            }

            $renderedMatches = array_map(
                static fn (string $match): string => str_replace(
                    ["\r", "\n", "\t"],
                    ['\\r', '\\n', '\\t'],
                    $match,
                ),
                array_values(array_unique($matches[0])),
            );
            $violations[$rule][] = "{$context}: " . implode(', ', $renderedMatches);
        };

        $recordUnbalancedQuotes = static function (
            string $rule,
            string $openingQuote,
            string $closingQuote,
        ) use (&$violations, $context, $text): void {
            $openingCount = substr_count($text, $openingQuote);
            $closingCount = substr_count($text, $closingQuote);

            if ($openingCount !== $closingCount) {
                $violations[$rule][] = "{$context}: {$openingQuote}={$openingCount}, {$closingQuote}={$closingCount}";
            }
        };

        $recordMatches('common: spaced ASCII hyphen', '/[\x{20}\x{A0}]-[\x{20}\x{A0}]/u', $text);
        $recordMatches('common: dash inside a word', '/\p{L}[—–]\p{L}/u', $text);
        $recordMatches('common: invalid numeric range dash', '/\d(?:-|—)\d/u', $text);
        $recordMatches('common: spaced numeric range', '/\d[\x{20}\x{A0}]+[—–-][\x{20}\x{A0}]+\d/u', $text);
        $recordMatches('common: three periods instead of ellipsis', '/\.\.\./u', $text);
        $recordMatches('common: straight double quote', '/"/u', $text);
        $recordMatches('common: space before punctuation', '/[\x{20}\x{A0}]+[,;:!?]/u', $text);
        $recordMatches(
            'common: control or invisible character',
            '/[\x{00}-\x{08}\x{0B}\x{0C}\x{0E}-\x{1F}\x{7F}-\x{9F}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FEFF}]/u',
            $text,
        );
        $recordMatches(
            'common: unexpected combining mark',
            '/\p{M}/u',
            str_replace('что́', 'что', $text),
        );
        $recordMatches(
            'common: mixed alphabets in a word',
            '/(?=[\p{L}\p{M}]*\p{Cyrillic})(?=[\p{L}\p{M}]*\p{Latin})[\p{L}\p{M}]+/u',
            $text,
        );
        $recordUnbalancedQuotes('common: unbalanced guillemets', '«', '»');

        if ($language === 'pl') {
            $recordMatches('pl: em dash instead of en dash', '/(^|\s)—(?=\s|$)/u', $text);
            $recordMatches('pl: breakable space before en dash', '/\S\x{20}–(?=\s)/u', $text);
            $recordMatches('pl: breakable space after initial en dash', '/(^|\n)[\x{20}\t]*–\x{20}/u', $text);
            $recordMatches('pl: Russian nested closing quote', '/“/u', $text);
            $recordUnbalancedQuotes('pl: unbalanced Polish quotes', '„', '”');

            return;
        }

        $recordMatches('ru: en dash instead of em dash', '/(^|\s)–(?=\s|$)/u', $text);
        $recordMatches('ru: breakable space before em dash', '/\S\x{20}—(?=\s)/u', $text);
        $recordMatches('ru: breakable space after initial em dash', '/(^|\n)[\x{20}\t]*—\x{20}/u', $text);
        $recordMatches('ru: Polish closing quote', '/”/u', $text);
        $recordUnbalancedQuotes('ru: unbalanced nested quotes', '„', '“');
    }
}
