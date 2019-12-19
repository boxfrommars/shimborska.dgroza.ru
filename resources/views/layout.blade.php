<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="google-site-verification" content="yvzIVHZghvLLFJArEmBKcr5HGABsieiNZYLausg9Loo" />
    <meta name='yandex-verification' content='74f87e2ca2368e81' />
    <link rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />

    <title>{{ $title }}</title>
</head>

<body>

<div id="wrap">
    <div id="bar">
        @if($main_page)
            <h1><span class="pseudo-anchor">Вислава Шимборская</span>&nbsp;&nbsp;<span class="book-title">Стихотворения</span></h1>
        @else
            <h1><a href="/" class="pseudo-anchor">Вислава Шимборская</a>&nbsp;&nbsp;<span class="book-title">Стихотворения</span></h1>
        @endif

        @if(isset($aboutproject))
            <span class="head-nav">о проекте</span>
        @else
            <a href="/project" class="head-nav">о проекте</a>
        @endif
    </div>
    <div id="main">
        <div id="leftbar">
            <ul id="navigation">
                <li><a href="#content" class="show-content-link">Содержание</a></li>
                @if(isset($aboutauthor))
                    <li><span>Об авторе</span></li>
                @else
                    <li><a href="/author">Об авторе</a></li>
                @endif
            </ul>
            @if(isset($images))
                {!! $images !!}
            @endif
        </div>

        <div class="page">
            @yield('content')
            @yield('navigation')
        </div>

        <div class="notabene">
            @yield('notes')
        </div>

        <div id="content" title="Содержание">
            @yield('tableOfContent')
        </div>
    </div>
    <div id="royklogo"></div>
</div>
<div id="footer">
    &copy; 2009 Студия «Гриб-дождевик»
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.3.2/jquery.min.js" integrity="sha256-yDcKLQUDWenVBazEEeb0V6SbITYKIebLySKbrTp2eJk=" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/script.js"></script>
</body>
</html>
