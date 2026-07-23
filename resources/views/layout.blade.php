@php($isErrorPage = ($page ?? null) === 'error')
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="google-site-verification" content="yvzIVHZghvLLFJArEmBKcr5HGABsieiNZYLausg9Loo" />
    <meta name='yandex-verification' content='74f87e2ca2368e81' />
    <link rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#333333">

    <title>{{ $title }}</title>
    @yield('head')

</head>

<body @class(['error-layout' => $isErrorPage])>

<div id="wrap">
    <header id="bar">
        @if($page === 'main')
            <h1><span class="pseudo-anchor">Вислава Шимборская</span><span class="book-title">Стихотворения</span></h1>
        @else
            <h1><a href="{{ route('main') }}" class="pseudo-anchor">Вислава Шимборская</a><span class="book-title">Стихотворения</span></h1>
        @endif

        @if($page === 'project')
            <span class="head-nav">о проекте</span>
        @else
            <a href="{{ route('project') }}" class="head-nav">о проекте</a>
        @endif
    </header>
    <main id="main" @class(['error-main' => $isErrorPage])>
        @unless($isErrorPage)
        <nav id="leftbar" aria-label="Основная навигация">
            <ul id="navigation">
                <li><a href="#content" class="show-content-link" aria-haspopup="dialog">Содержание</a></li>
                @if($page === 'author')
                    <li><span>Об авторе</span></li>
                @else
                    <li><a href="{{ route('author') }}">Об авторе</a></li>
                @endif
            </ul>
        </nav>
        @endunless

        <article @class(['page', 'error-page' => $isErrorPage])>
            @yield('content')

            @unless($isErrorPage)
            <ul id="pager">
                @if($page === 'main')
                    <li id="center-bottom-nav" class="first"><span>Обложка</span><span class="shortkey"></span></li>
                @else
                    <li class="first"><a href="{{ route('main') }}">Обложка</a><span class="shortkey">(ctrl + ↓)</span></li>
                @endif

                @foreach($navigation['items'] as $index => $poem)
                    @if($navigation['currentIndex'] === $index)
                        <li id="center-bottom-nav"><span>{{ $index + 1 }}</span></li>
                    @else
                        <li><a title="{{ $poem['title'] }}" href="{{ route('poem', ['section' => $poem['section'], 'slug' => $poem['slug']]) }}">{{ $index + 1 }}</a></li>
                    @endif
                @endforeach
                <li class="last"><a href="#" class="show-content-link" aria-haspopup="dialog">Содержание</a><span class="shortkey">(ctrl + ↑)</span></li>
            </ul>
            @endunless
        </article>

        @unless($isErrorPage)
        @hasSection('images')
            <aside class="illustrations" aria-label="Иллюстрации">
                @yield('images')
            </aside>
        @endif

        <aside class="notabene" aria-label="Примечания">
            @yield('notes')
        </aside>

        <dialog id="content" aria-labelledby="content-title">
            <div class="dialog-titlebar">
                <span id="content-title">Содержание</span>
                <button type="button" class="content-close" aria-label="Закрыть">×</button>
            </div>
            <div class="dialog-body">
                <ul id="contents-wrap">
                @if($page === 'main')
                    <li class="chapter-link-list wide"><span class="chapter-link active">Обложка</span></li>
                @else
                    <li class="chapter-link-list wide"><a href="{{ route('main') }}" class="chapter-link">Обложка</a></li>
                @endif

                @foreach($sections as $sectionSlug => $section)
                    <li class="chapter-link-list">
                        <span class="chapter-link">{{ $section['title'] }}</span>
                        <ul>
                            @foreach($section['poems'] as $poem)
                                @if($currentPoem !== null && $currentPoem['section'] === $sectionSlug && $currentPoem['slug'] === $poem['slug'])
                                    <li><span class="active">{{ $poem['title'] }}</span></li>
                                @else
                                    <li><a href="{{ route('poem', ['section' => $sectionSlug, 'slug' => $poem['slug']]) }}">{{ $poem['title'] }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endforeach
                </ul>
            </div>
        </dialog>
        @endunless
    </main>
    <div id="royklogo" aria-hidden="true"></div>
</div>
<footer id="footer">
    &copy; 2009 Студия «Гриб-дождевик»
</footer>

<script src="/js/script.js"></script>

<!-- Yandex.Metrika counter -->
<script>
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(57627601, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/57627601" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

</body>
</html>
