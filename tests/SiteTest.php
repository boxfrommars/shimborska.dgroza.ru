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
        $this->get('/')
            ->assertOk()
            ->assertSee('<a class="skip-link" href="#page-content">Перейти к основному содержанию</a>', false)
            ->assertSee('<span class="visually-hidden"> · </span>', false)
            ->assertSee('<span class="book-title visually-hidden-mobile">Стихотворения</span>', false)
            ->assertSee('<article id="page-content" class="page" tabindex="-1">', false)
            ->assertSee('<nav aria-label="Постраничная навигация">', false)
            ->assertSee('aria-current="page" aria-label="Текущая страница — Обложка"', false)
            ->assertSee(
                'aria-label="Вислава Шимборская. Обложка — перейти к стихотворению «Две обезьяны»"',
                false,
            );

        $this->get('/different/two-monkeys')
            ->assertOk()
            ->assertSee(
                '<span aria-current="page" aria-label="Текущая страница 1 — Две обезьяны">1</span>',
                false,
            )
            ->assertSee(
                'title="Страница 2 — Похвала снам" aria-label="Страница 2 — Похвала снам"',
                false,
            )
            ->assertSee(
                '<span class="active" aria-current="page" aria-label="Текущая страница — Две обезьяны">',
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

    public function testPolishOriginalsDeclareTheirLanguage(): void
    {
        $paths = [
            '/different/allegro-ma-non-troppo',
            '/different/arrival',
            '/different/atlantis',
            '/different/authors-evening',
            '/different/autotomy',
            '/different/born',
            '/different/buffo',
            '/different/commemoration',
            '/different/dealings-with-the-dead',
            '/different/discovery',
            '/different/elegiac-arithmetic',
            '/different/epitaph',
            '/different/first-picture-of-hitler',
            '/different/four-in-the-morning',
            '/different/hilarity',
            '/different/im-too-close',
            '/different/impression-of-the-theater',
            '/different/in-any-event',
            '/different/in-honor-of-my-sister',
            '/different/joy-of-writing',
            '/different/large-number',
            '/different/lesson',
            '/different/letters-of-the-dead',
            '/different/life-while-you-wait',
            '/different/lizard-skeleton',
            '/different/lots-wife',
            '/different/male-beauty-contest',
            '/different/medieval-miniature',
            '/different/memory-at-last',
            '/different/pi',
            '/different/prospect',
            '/different/pursuit',
            '/different/rubens-women',
            '/different/seen-from-above',
            '/different/shadow',
            '/different/soliloquy-for-cassandra',
            '/different/station',
            '/different/tarsier',
            '/different/torture',
            '/different/two-monkeys',
            '/different/under-one-small-star',
            '/different/unexpected-meeting',
            '/different/utopia',
            '/different/vietnam',
            '/moment/about-soul',
            '/moment/ball',
            '/moment/clouds',
            '/moment/contribution-to-statistics',
            '/moment/early-hour',
            '/moment/everything',
            '/moment/first-love',
            '/moment/from-memories',
            '/moment/in-abundance',
            '/moment/in-park',
            '/moment/list',
            '/moment/little-girl-pull-tablecloth',
            '/moment/negative',
            '/moment/note',
            '/moment/plato-or-why',
            '/moment/picture-september-11',
            '/moment/puddle',
            '/moment/return-baggage',
            '/moment/silence-of-plants',
            '/moment/some-people',
            '/moment/telephone-receiver',
            '/moment/three-striking-words',
            '/text/poet-and-world',
        ];

        foreach ($paths as $path) {
            $response = $this->get($path)->assertOk();

            self::assertSame(1, substr_count($response->getContent(), 'lang="pl"'), $path);
        }

        $this->get('/text/literary-mail')
            ->assertOk()
            ->assertDontSee('lang="pl"', false);
    }

    public function testNotesHaveBidirectionalAccessibleLinks(): void
    {
        $pages = [
            '/different/first-picture-of-hitler' => 1,
            '/different/people-on-bridge' => 1,
            '/different/praise-dreams' => 1,
            '/different/soliloquy-for-cassandra' => 1,
            '/different/two-monkeys' => 1,
            '/moment/ball' => 1,
            '/semicolon/conversation-with-atropos' => 1,
            '/semicolon/repechage' => 1,
            '/text/literary-mail' => 3,
        ];

        foreach ($pages as $path => $expectedCount) {
            $response = $this->get($path)->assertOk();
            $content = $response->getContent();

            self::assertSame($expectedCount, substr_count($content, 'role="doc-noteref"'), $path);
            self::assertSame($expectedCount, substr_count($content, 'role="doc-footnote"'), $path);
            self::assertSame($expectedCount, substr_count($content, 'class="note-backlink"'), $path);

            for ($index = 1; $index <= $expectedCount; $index++) {
                $id = str_pad((string) $index, 3, '0', STR_PAD_LEFT);

                $response
                    ->assertSee("id=\"tonote{$id}\" href=\"#note{$id}\" role=\"doc-noteref\"", false)
                    ->assertSee("id=\"note{$id}\" role=\"doc-footnote\" tabindex=\"-1\"", false)
                    ->assertSee("href=\"#tonote{$id}\" aria-label=\"Вернуться к месту примечания\"", false);

                self::assertMatchesRegularExpression(
                    "/<p\\b[^>]*>(?:(?!<\\/p>).)*<a class=\"note-backlink\" href=\"#tonote{$id}\"[^>]*>↩<\\/a>\\s*<\\/p>/su",
                    $content,
                    $path,
                );
            }
        }
    }

    public function testEmptyComplementaryLandmarksAreNotRendered(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<aside class="notabene" aria-label="О сайте">', false)
            ->assertDontSee('<aside class="notabene" aria-label="Примечания">', false);

        $this->get('/moment/clouds')
            ->assertOk()
            ->assertDontSee('<aside class="illustrations"', false)
            ->assertDontSee('<aside class="notabene"', false);

        $this->get('/text/literary-mail')
            ->assertOk()
            ->assertDontSee('<aside class="illustrations"', false)
            ->assertSee('<aside class="notabene" aria-label="Примечания">', false);

        $this->get('/different/two-monkeys')
            ->assertOk()
            ->assertSee('<aside class="illustrations" aria-label="Иллюстрации">', false)
            ->assertSee('<aside class="notabene" aria-label="Примечания">', false);

        foreach (['/author', '/project'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('<aside class="illustrations"', false)
                ->assertDontSee('<aside class="notabene"', false);
        }
    }

    public function testCorrectedAlternativeTextIsRendered(): void
    {
        $this->get('/different/first-picture-of-hitler')
            ->assertOk()
            ->assertSee('alt="Адольф Гитлер в возрасте 12 лет"', false)
            ->assertDontSee('alt="кассандра" src="/images/younghitler.jpg"', false);

        $this->get('/different/two-monkeys')
            ->assertOk()
            ->assertSee('alt="Автопортрет Питера Брейгеля" src="/images/breigel.jpg"', false);
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

    public function testNewPoemsAreAppendedAfterTheExistingDifferentSection(): void
    {
        $different = app(PoemCatalog::class)->sections()['different']['poems'];

        self::assertCount(51, $different);
        self::assertSame([
            'two-monkeys',
            'praise-dreams',
            'soliloquy-for-cassandra',
            'shadow',
            'utopia',
            'torture',
            'impression-of-the-theater',
            'reality',
            'im-too-close',
            'first-picture-of-hitler',
            'elegiac-arithmetic',
            'in-honor-of-my-sister',
            'station',
            'ballad',
            'terrorist-he-looks',
            'road-elegy',
            'people-on-bridge',
            'cat-in-empty-apartment',
        ], array_column(array_slice($different, 0, 18), 'slug'));
        self::assertSame('atlantis', $different[18]['slug']);

        $atlantisUrl = route('poem', ['section' => 'different', 'slug' => 'atlantis']);
        $catUrl = route('poem', ['section' => 'different', 'slug' => 'cat-in-empty-apartment']);

        $this->get('/different/cat-in-empty-apartment')
            ->assertOk()
            ->assertSee(
                "title=\"Страница 19 — Атлантида\" aria-label=\"Страница 19 — Атлантида\" href=\"{$atlantisUrl}\"",
                false,
            );

        $this->get('/different/atlantis')
            ->assertOk()
            ->assertSee(
                "title=\"Страница 18 — Кот в пустой квартире\" aria-label=\"Страница 18 — Кот в пустой квартире\" href=\"{$catUrl}\"",
                false,
            );
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

        $this->get('/moment/contribution-to-statistics')
            ->assertOk()
            ->assertSeeInOrder([
                'Дополнительно к статистике',
                'Перевод Асара Эппеля',
                'Przyczynek do statystyki',
            ]);

        $this->get('/moment/some-people')
            ->assertOk()
            ->assertSeeInOrder([
                'Какие-то люди',
                'Перевод Асара Эппеля',
                'Jacyś ludzie',
            ]);

        $this->get('/moment/picture-september-11')
            ->assertOk()
            ->assertSeeInOrder([
                'Перевод Натальи Астафьевой',
                'Fotografia z 11 września',
                'Сфотографированное 11 сентября',
                'Перевод Асара Эппеля',
            ]);

        $this->get('/moment/return-baggage')
            ->assertOk()
            ->assertSeeInOrder([
                'Обратный багаж',
                'Перевод Асара Эппеля',
                'Bagaż powrotny',
            ]);

        $this->get('/moment/ball')
            ->assertOk()
            ->assertSeeInOrder([
                'Перевод Асара Эппеля',
                'Bal',
            ]);

        $this->get('/moment/note')
            ->assertOk()
            ->assertSeeInOrder([
                'Перевод Натальи Астафьевой',
                'Notatka',
                'Запись',
                'Перевод Асара Эппеля',
            ]);

        $this->get('/moment/list')
            ->assertOk()
            ->assertSeeInOrder([
                'Список',
                'Перевод Асара Эппеля',
                'Spis',
            ]);

        $this->get('/moment/everything')
            ->assertOk()
            ->assertSeeInOrder([
                'Всё',
                'Перевод Асара Эппеля',
                'Wszystko',
            ]);

        $this->get('/text/poet-and-world')
            ->assertOk()
            ->assertSeeInOrder([
                'Нобелевская лекция 1996&nbsp;года',
                'Перевод с&nbsp;польского Ксении&nbsp;Старосельской',
                'Poeta i świat',
            ], false)
            ->assertDontSee('От&nbsp;переводчика', false);
    }

    public function testScannedPoemsContainTheOriginalAndEppelTranslationInTheExpectedOrder(): void
    {
        $newPoems = [
            'atlantis' => ['Атлантида', 'Atlantyda'],
            'buffo' => ['Буффонада', 'Buffo'],
            'commemoration' => ['Увековечение', 'Upamiętnienie'],
            'four-in-the-morning' => ['Четыре часа утра', 'Czwarta nad ranem'],
            'lesson' => ['Урок', 'Lekcja'],
            'unexpected-meeting' => ['Внезапная встреча', 'Niespodziane spotkanie'],
            'rubens-women' => ['Рубенсовские женщины', 'Kobiety Rubensa'],
            'male-beauty-contest' => ['Конкурс мужской красоты', 'Konkurs piękności męskiej'],
            'authors-evening' => ['Авторский вечер', 'Wieczór autorski'],
            'epitaph' => ['Надгробная надпись', 'Nagrobek'],
            'joy-of-writing' => ['Радость писательства', 'Radość pisania'],
            'memory-at-last' => ['Вот память и нашла', 'Pamięć nareszcie'],
            'born' => ['Порожденный', 'Urodzony'],
            'vietnam' => ['Вьетнам', 'Wietnam'],
            'arrival' => ['Прилет', 'Przylot'],
            'tarsier' => ['Долгопят', 'Tarsjusz'],
            'hilarity' => ['Умора', 'Sto pociech'],
            'in-any-event' => ['Всякий случай', 'Wszelki wypadek'],
            'letters-of-the-dead' => ['Письма умерших', 'Listy umarłych'],
            'prospect' => ['Проспект', 'Prospekt'],
            'discovery' => ['Открытие', 'Odkrycie'],
            'lizard-skeleton' => ['Скелет ящера', 'Szkielet jaszczura'],
            'pursuit' => ['Погоня', 'Pogoń'],
            'allegro-ma-non-troppo' => ['Allegro ma non troppo', 'Allegro ma non troppo'],
            'autotomy' => ['Аутотомия', 'Autotomia'],
            'under-one-small-star' => ['Под тою же самой звездой', 'Pod jedną gwiazdką'],
            'large-number' => ['Большое число', 'Wielka liczba'],
            'lots-wife' => ['Лотова жена', 'Żona Lota'],
            'seen-from-above' => ['Виденное сверху', 'Widziane z góry'],
            'medieval-miniature' => ['Средневековая миниатюра', 'Miniatura średniowieczna'],
            'life-while-you-wait' => ['Жизнь в присутствии заказчика', 'Życie na poczekaniu'],
            'pi' => ['Число пи', 'Liczba Pi'],
            'dealings-with-the-dead' => ['Сношения с умершими', 'Konszachty z umarłymi'],
        ];

        foreach ($newPoems as $slug => [$title, $originalTitle]) {
            $response = $this->get("/different/{$slug}")->assertOk();
            $content = $response->getContent();

            $response
                ->assertSee("<h2>{$title}</h2>", false)
                ->assertSee("<h3>{$originalTitle}</h3>", false)
                ->assertSeeInOrder([
                    'Перевод Асара Эппеля',
                    '<div class="poem" lang="pl">',
                    "<h3>{$originalTitle}</h3>",
                ], false);
            self::assertSame(1, substr_count($content, 'Перевод Асара Эппеля'), $slug);
        }

        $supplementedPoems = [
            'im-too-close' => ['Я слишком близко', 'Jestem za blisko'],
            'station' => ['Вокзал', 'Dworzec'],
            'terrorist-he-looks' => ['Террорист, он смотрит', 'Terrorysta, on patrzy'],
            'in-honor-of-my-sister' => ['В честь моей сестры', 'Pochwała siostry'],
            'soliloquy-for-cassandra' => ['Монолог для Кассандры', 'Monolog dla Kasandry'],
            'impression-of-the-theater' => ['Впечатление от театра', 'Wrażenia z teatru'],
        ];

        foreach ($supplementedPoems as $slug => [$title, $originalTitle]) {
            $response = $this->get("/different/{$slug}")->assertOk();
            $content = $response->getContent();

            $response
                ->assertSee("<h2>{$title}</h2>", false)
                ->assertSeeInOrder([
                    '<div class="poem" lang="pl">',
                    "<h3>{$originalTitle}</h3>",
                    'Перевод Асара Эппеля',
                ], false);
            self::assertSame(1, substr_count($content, 'Перевод Асара Эппеля'), $slug);
        }
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

        self::assertCount(93, $catalogPaths);
        self::assertCount(93, array_unique($catalogPaths));

        $viewPaths = [];

        foreach (File::allFiles(resource_path('views/poems')) as $view) {
            $relativePath = str_replace('\\', '/', $view->getRelativePathname());
            $viewPaths[] = substr($relativePath, 0, -strlen('.blade.php'));
        }

        sort($catalogPaths);
        sort($viewPaths);

        self::assertSame($catalogPaths, $viewPaths);

        $lastPoem = $catalog->poems()[92];
        self::assertSame('moment', $lastPoem['section']);
        self::assertSame('everything', $lastPoem['slug']);
    }

    public function testNavigationKeepsItsWindowAroundTheCurrentPoem(): void
    {
        $catalog = app(PoemCatalog::class);

        $coverNavigation = $catalog->navigation();
        self::assertNull($coverNavigation['currentIndex']);
        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($coverNavigation['items']));

        $middleNavigation = $catalog->navigation('semicolon', 'absence');
        self::assertSame(53, $middleNavigation['currentIndex']);
        self::assertSame([51, 52, 53, 54, 55, 56], array_keys($middleNavigation['items']));

        $momentNavigation = $catalog->navigation('moment', 'moment');
        self::assertSame(70, $momentNavigation['currentIndex']);
        self::assertSame([68, 69, 70, 71, 72, 73], array_keys($momentNavigation['items']));

        $lastNavigation = $catalog->navigation('moment', 'everything');
        self::assertSame(92, $lastNavigation['currentIndex']);
        self::assertSame([87, 88, 89, 90, 91, 92], array_keys($lastNavigation['items']));
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
            self::assertSame(96, substr_count($xml, '<url>'));
            self::assertSame(96, substr_count($xml, '<loc>'));
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
            self::assertStringNotContainsString(
                'https://example.test/different/picture-september-11',
                $xml,
            );
            self::assertStringNotContainsString(
                '<loc>https://example.test/different/ball</loc>',
                $xml,
            );
            self::assertStringNotContainsString(
                '<loc>https://example.test/different/note</loc>',
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
