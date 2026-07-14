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

<body>

<div id="wrap">
    <header id="bar">
        @if($code === '/')
            <h1><span class="pseudo-anchor">Вислава Шимборская</span><span class="book-title">Стихотворения</span></h1>
        @else
            <h1><a href="/" class="pseudo-anchor">Вислава Шимборская</a><span class="book-title">Стихотворения</span></h1>
        @endif

        @if($code === 'project')
            <span class="head-nav">о проекте</span>
        @else
            <a href="/project" class="head-nav">о проекте</a>
        @endif
    </header>
    <main id="main">
        <nav id="leftbar" aria-label="Основная навигация">
            <ul id="navigation">
                <li><a href="#content" class="show-content-link" aria-haspopup="dialog">Содержание</a></li>
                @if($code === 'author')
                    <li><span>Об авторе</span></li>
                @else
                    <li><a href="/author">Об авторе</a></li>
                @endif
            </ul>
        </nav>

        <article class="page">
            @yield('content')

            <ul id="pager">
                @if($code === '/')
                    <li id="center-bottom-nav" class="first"><span>Обложка</span><span class="shortkey"></span></li>
                @else
                    <li class="first"><a href="/">Обложка</a><span class="shortkey">(ctrl + ↓)</span></li>
                @endif

                @foreach($nav['content'] as $key => $value)
                    @if($value['class'] !== 'frontpage')
                        @if($nav['current_key'] === $key)
                            <li id="center-bottom-nav"><span>{{ $key + 1 }}</span></li>
                        @else
                            <li><a title="{{ $value['title'] }}" href="/{{ $value['parent'] }}/{{ $value['href'] }}">{{ $key + 1 }}</a></li>
                        @endif
                    @endif
                @endforeach
                <li class="last"><a href="#" class="show-content-link" aria-haspopup="dialog">Содержание</a><span class="shortkey">(ctrl + ↑)</span></li>
            </ul>
        </article>

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
                @if($code == '/')
                    <li class="chapter-link-list wide"><span class="chapter-link active">Обложка</span></li>
                @else
                    <li class="chapter-link-list wide"><a href="/" class="chapter-link">Обложка</a></li>
                @endif

                @foreach($toc as $parent_key => $parent)
                    <li class="chapter-link-list">
                        <span class="chapter-link">{{ parent_alias($parent_key) }}</span>
                        <ul>
                            @foreach($parent as $node)
                                @if($node['href'] === $code)
                                    <li><span class="active">{{ $node['title'] }}</span></li>
                                @else
                                    <li><a href="/{{ $parent_key }}/{{ $node['href'] }}">{{ $node['title'] }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endforeach
                </ul>
            </div>
        </dialog>
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
