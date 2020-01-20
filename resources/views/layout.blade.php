<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="google-site-verification" content="yvzIVHZghvLLFJArEmBKcr5HGABsieiNZYLausg9Loo" />
    <meta name='yandex-verification' content='74f87e2ca2368e81' />
    <link rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <title>{{ $title }}</title>
</head>

<body>

<div id="wrap">
    <div id="bar">
        @if($code === '/')
            <h1><span class="pseudo-anchor">Вислава Шимборская</span>&nbsp;&nbsp;<span class="book-title">Стихотворения</span></h1>
        @else
            <h1><a href="/" class="pseudo-anchor">Вислава Шимборская</a>&nbsp;&nbsp;<span class="book-title">Стихотворения</span></h1>
        @endif

        @if($code === 'project')
            <span class="head-nav">о проекте</span>
        @else
            <a href="/project" class="head-nav">о проекте</a>
        @endif
    </div>
    <div id="main">
        <div id="leftbar">
            <ul id="navigation">
                <li><a href="#content" class="show-content-link">Содержание</a></li>
                @if($code === 'author')
                    <li><span>Об авторе</span></li>
                @else
                    <li><a href="/author">Об авторе</a></li>
                @endif
            </ul>
            @yield('images')
        </div>

        <div class="page">
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
                <li class="last"><a href="#" class="show-content-link">Содержание</a><span class="shortkey">(ctrl + ↑)</span></li>
            </ul>
        </div>

        <div class="notabene">
            @yield('notes')
        </div>

        <div id="content" title="Содержание">
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
    </div>
    <div id="royklogo"></div>
</div>
<div id="footer">
    &copy; 2009 Студия «Гриб-дождевик»
</div>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.3.2/jquery.min.js" integrity="sha256-yDcKLQUDWenVBazEEeb0V6SbITYKIebLySKbrTp2eJk=" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/script.js"></script>
</body>
</html>
