<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="google-site-verification" content="yvzIVHZghvLLFJArEmBKcr5HGABsieiNZYLausg9Loo" />
    <meta name="yandex-verification" content="74f87e2ca2368e81" />
    <link rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />
    @if(in_array($page, ['poem', 'project', 'author'], true))
        <link rel="stylesheet" type="text/css" href="/css/print.css" media="print" />
    @endif
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

@if(isset($bodyClass))
<body class="{{ $bodyClass }}">
@else
<body>
@endif

<a class="skip-link" href="#page-content">Перейти к основному содержанию</a>

<div id="wrap">
    <header id="bar">
        @if($page === 'main')
            <h1><span class="pseudo-anchor">Вислава Шимборская</span><span class="visually-hidden"> · </span><span class="book-title">Стихотворения</span></h1>
        @else
            <h1><a href="{{ route('main') }}" class="pseudo-anchor">Вислава Шимборская</a><span class="visually-hidden"> · </span><span class="book-title">Стихотворения</span></h1>
        @endif

        @if($page === 'project')
            <span class="head-nav" aria-current="page" aria-label="Текущая страница — О проекте">о проекте</span>
        @else
            <a href="{{ route('project') }}" class="head-nav">о проекте</a>
        @endif
    </header>

    @yield('body')

    <div id="royklogo" aria-hidden="true"></div>
</div>
<footer id="footer">
    &copy; 2009 Студия «Гриб-дождевик»
</footer>

@yield('scripts')

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
