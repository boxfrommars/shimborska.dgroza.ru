@extends('layouts.shell')

@section('body')
    <main id="main">
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

        <article class="page">
            @yield('content')

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
    </main>
@endsection

@section('scripts')
    <script src="/js/script.js"></script>
@endsection
