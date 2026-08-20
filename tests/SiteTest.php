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
    public function testMainPageIsAvailable(): void
    {
        $styleVersion = filemtime(public_path('css/style.css'));
        $scriptVersion = filemtime(public_path('js/script.js'));

        self::assertIsInt($styleVersion);
        self::assertIsInt($scriptVersion);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="viewport" content="width=device-width, initial-scale=1"', false)
            ->assertSee("/css/style.css?v={$styleVersion}", false)
            ->assertDontSee('/css/print.css', false)
            ->assertSee('<dialog id="content"', false)
            ->assertSee("/js/script.js?v={$scriptVersion}", false)
            ->assertDontSee('jquery', false);
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
                $content = $this->get($path)->assertOk()->getContent();
                $canonicalElement = "<link rel=\"canonical\" href=\"{$canonicalUrl}\">";

                self::assertSame(1, substr_count($content, 'rel="canonical"'), $path);
                self::assertStringContainsString($canonicalElement, $content, $path);
            }
        } finally {
            config(['app.url' => $originalUrl]);
        }
    }

    public function testStaticPagesUseTheExpectedTypography(): void
    {
        $violations = [];

        foreach (['/' => 'О сайте', '/author' => 'Примечания', '/project' => 'Примечания'] as $path => $notesLabel) {
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
            $visibleText = '';

            foreach ($xpath->query('//*[@id="page-content"] | //footer[@id="footer"]') as $node) {
                $visibleText .= $node->textContent;
            }

            self::collectStaticTypographyViolations($violations, $visibleText, $path);
            self::collectSupplementaryContentViolations($violations, $xpath, $path, $notesLabel);
        }

        self::assertSame([], $violations);
    }

    public function testPrintStylesAreOnlyLoadedForPrintablePages(): void
    {
        $printVersion = filemtime(public_path('css/print.css'));

        self::assertIsInt($printVersion);

        foreach (['/different/two-monkeys', '/author', '/project'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee(
                    "<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/print.css?v={$printVersion}\" media=\"print\" />",
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

    public function testAccessiblePageAndNavigationSemanticsAreRendered(): void
    {
        $poems = app(PoemCatalog::class)->poems();
        $firstPoem = $poems[0];
        $secondPoem = $poems[1];

        $this->get('/')
            ->assertOk()
            ->assertSee('<a class="skip-link" href="#page-content">Перейти к основному содержанию</a>', false)
            ->assertSee('<span class="visually-hidden"> · </span>', false)
            ->assertSee('<span class="book-title visually-hidden-mobile">Стихотворения</span>', false)
            ->assertSee('<article id="page-content" class="page" tabindex="-1">', false)
            ->assertSee('<nav aria-label="Постраничная навигация">', false)
            ->assertSee('aria-current="page" aria-label="Текущая страница — Обложка"', false)
            ->assertSee(
                "aria-label=\"Вислава Шимборская. Обложка — перейти к стихотворению «{$firstPoem['title']}»\"",
                false,
            );

        $this->get("/{$firstPoem['section']}/{$firstPoem['slug']}")
            ->assertOk()
            ->assertSee(
                "<span aria-current=\"page\" aria-label=\"Текущая страница 1 — {$firstPoem['title']}\">1</span>",
                false,
            )
            ->assertSee(
                "title=\"Страница 2 — {$secondPoem['title']}\" aria-label=\"Страница 2 — {$secondPoem['title']}\"",
                false,
            )
            ->assertSee(
                "<span class=\"active\" aria-current=\"page\" aria-label=\"Текущая страница — {$firstPoem['title']}\">",
                false,
            );

        $this->get('/author')
            ->assertOk()
            ->assertSee('aria-current="page" aria-label="Текущая страница — Об авторе"', false);

        $this->get('/project')
            ->assertOk()
            ->assertSee('aria-current="page" aria-label="Текущая страница — О проекте"', false);

        $this->get('/unknown')
            ->assertNotFound()
            ->assertSee('<article id="page-content" class="page error-page" tabindex="-1">', false);
    }

    public function testKeyboardShortcutPlaceholdersAreRenderedWithoutPlatformLabels(): void
    {
        $response = $this->get('/different/two-monkeys')->assertOk();

        $response
            ->assertSee('<span class="shortkey" data-shortcut="cover"></span>', false)
            ->assertSee('<span class="shortkey" data-shortcut="contents"></span>', false)
            ->assertDontSee('(ctrl +', false)
            ->assertDontSee('⌃⇧', false);
    }

    public function testCatalogPagesMeetContentAndTypographyContracts(): void
    {
        $violations = [];

        foreach (app(PoemCatalog::class)->poems() as $poem) {
            $path = "/{$poem['section']}/{$poem['slug']}";
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
            self::collectCatalogPageViolations($violations, $xpath, $path);
        }

        self::assertSame([], $violations);
    }

    public function testContentValidatorDetectsStructuralViolations(): void
    {
        $document = new DOMDocument;
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(<<<'HTML'
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
            HTML);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }
        $violations = [];

        self::collectCatalogPageViolations($violations, new DOMXPath($document), '/fixture');

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
        $response = $this->get('/')->assertOk();
        $document = new DOMDocument;
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            self::assertTrue($document->loadHTML($response->getContent()));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        $xpath = new DOMXPath($document);
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
        $requiredFragments = [
            '<body class="error-layout">',
            '<header id="bar">',
            '<footer id="footer">',
            '<div id="royklogo"',
            '<h2>404',
            'href="' . route('main') . '"',
        ];
        $forbiddenFragments = [
            '<nav id="leftbar"',
            '<ul id="pager"',
            '<dialog id="content"',
            '/js/script.js',
            'rel="canonical"',
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
            $content = $response->getContent();

            if ($response->getStatusCode() !== 404) {
                $violations[] = "{$path}: expected status 404, got {$response->getStatusCode()}";
            }

            foreach ($requiredFragments as $fragment) {
                if (!str_contains($content, $fragment)) {
                    $violations[] = "{$path}: missing {$fragment}";
                }
            }

            foreach ($forbiddenFragments as $fragment) {
                if (str_contains($content, $fragment)) {
                    $violations[] = "{$path}: unexpectedly contains {$fragment}";
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

        $firstPoem = $poems[0];
        $firstNavigation = $catalog->navigation($firstPoem['section'], $firstPoem['slug']);
        self::assertSame(0, $firstNavigation['currentIndex']);
        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($firstNavigation['items']));

        $middleIndex = intdiv($lastIndex, 2);
        $middlePoem = $poems[$middleIndex];
        $middleNavigation = $catalog->navigation($middlePoem['section'], $middlePoem['slug']);
        self::assertSame($middleIndex, $middleNavigation['currentIndex']);
        self::assertSame(
            range($middleIndex - 2, $middleIndex + 3),
            array_keys($middleNavigation['items']),
        );

        $lastPoem = $poems[$lastIndex];
        $lastNavigation = $catalog->navigation($lastPoem['section'], $lastPoem['slug']);
        self::assertSame($lastIndex, $lastNavigation['currentIndex']);
        self::assertSame(
            range($lastIndex - 5, $lastIndex),
            array_keys($lastNavigation['items']),
        );
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
            'static: copyright without non-breaking space' => '/©(?!\x{A0}\d)/u',
        ];

        foreach ($patterns as $rule => $pattern) {
            preg_match_all($pattern, $text, $matches);

            if ($matches[0] !== []) {
                $violations[$rule][] = "{$path}: " . implode(', ', array_unique($matches[0]));
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
