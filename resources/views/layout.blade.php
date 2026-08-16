@extends('layouts.shell')

@section('body')
    <main id="main">
        <nav id="leftbar" aria-label="Основная навигация">
            <ul id="navigation">
                <li><a href="#content" class="show-content-link" aria-haspopup="dialog">Содержание</a></li>
                @if($page === 'author')
                    <li><span aria-current="page" aria-label="Текущая страница — Об авторе">Об авторе</span></li>
                @else
                    <li><a href="{{ route('author') }}">Об авторе</a></li>
                @endif
            </ul>
        </nav>

        <article id="page-content" class="page" tabindex="-1">
            @yield('content')

            <nav aria-label="Постраничная навигация">
                <ul id="pager">
                    @if($page === 'main')
                        <li id="center-bottom-nav" class="first"><span aria-current="page" aria-label="Текущая страница — Обложка">Обложка</span><span class="shortkey"></span></li>
                    @else
                        <li class="first"><a href="{{ route('main') }}">Обложка</a><span class="shortkey" data-shortcut="cover"></span></li>
                    @endif

                    @foreach($navigation['items'] as $index => $poem)
                        @if($navigation['currentIndex'] === $index)
                            <li id="center-bottom-nav"><span aria-current="page" aria-label="Текущая страница {{ $index + 1 }} — {{ $poem['title'] }}">{{ $index + 1 }}</span></li>
                        @else
                            <li><a title="Страница {{ $index + 1 }} — {{ $poem['title'] }}" aria-label="Страница {{ $index + 1 }} — {{ $poem['title'] }}" href="{{ route('poem', ['section' => $poem['section'], 'slug' => $poem['slug']]) }}">{{ $index + 1 }}</a></li>
                        @endif
                    @endforeach
                    <li class="last"><a href="#" class="show-content-link" aria-haspopup="dialog">Содержание</a><span class="shortkey" data-shortcut="contents"></span></li>
                </ul>
            </nav>
        </article>

        @php
            $illustrationsContent = trim($__env->yieldContent('images'));
            $notesContent = trim($__env->yieldContent('notes'));
            $notesLabel = $page === 'main' ? 'О сайте' : 'Примечания';
        @endphp

        @if($illustrationsContent !== '')
            <aside class="illustrations" aria-label="Иллюстрации">
                {!! $illustrationsContent !!}
            </aside>
        @endif

        @if($notesContent !== '')
            <aside class="notabene" aria-label="{{ $notesLabel }}">
                {!! $notesContent !!}
            </aside>
        @endif

        <dialog id="content" aria-labelledby="content-title">
            <div class="dialog-titlebar">
                <span id="content-title">Содержание</span>
                <button type="button" class="content-close" aria-label="Закрыть">×</button>
            </div>
            <div class="dialog-body">
                <div id="contents-wrap">
                @if($page === 'main')
                    <div class="chapter-link-list wide"><span class="chapter-link active" aria-current="page" aria-label="Текущая страница — Обложка">Обложка</span></div>
                @else
                    <div class="chapter-link-list wide"><a href="{{ route('main') }}" class="chapter-link">Обложка</a></div>
                @endif

                @foreach([array_slice($sections, 0, 1, true), array_slice($sections, 1, null, true)] as $column)
                    <ul class="contents-column">
                        @foreach($column as $sectionSlug => $section)
                            <li class="chapter-link-list" data-section="{{ $sectionSlug }}">
                                <span class="chapter-link">{{ $section['title'] }}</span>
                                <ul>
                                    @foreach($section['poems'] as $poem)
                                        @if($currentPoem !== null && $currentPoem['section'] === $sectionSlug && $currentPoem['slug'] === $poem['slug'])
                                            <li><span class="active" aria-current="page" aria-label="Текущая страница — {{ $poem['title'] }}">{{ $poem['title'] }}</span></li>
                                        @else
                                            <li><a href="{{ route('poem', ['section' => $sectionSlug, 'slug' => $poem['slug']]) }}">{{ $poem['title'] }}</a></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
                </div>
            </div>
        </dialog>
    </main>
@endsection

@section('scripts')
    <script src="/js/script.js?v={{ filemtime(public_path('js/script.js')) }}"></script>
@endsection
