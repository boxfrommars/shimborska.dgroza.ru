@extends('layout')

@section('head')
    <meta name="description" content="Сайт, посвящённый польской поэтессе Виславе Шимборской, — лауреату Нобелевской премии 1996 года. Представлены сборники Двоеточие, Мгновение и другие стихотворения и проза в разных переводах и на польском языке" />
    <script type="application/ld+json">
        {!! json_encode([
            '@' . 'context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => $canonicalUrl,
            'name' => 'Вислава Шимборская',
            'alternateName' => [
                'Шимборская',
                'shimborska.dgroza.ru',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endsection

@section('content')
    <p class="cover unpined"><a href="{{ route('poem', ['section' => 'different', 'slug' => 'two-monkeys']) }}" aria-label="Вислава Шимборская. Обложка — перейти к стихотворению «Две обезьяны»"><img alt="Вислава Шимборская. Обложка" src="/images/szymborska1.jpg" /></a></p>
@endsection

@section('notes')
    <div class="note" id="book-note">
        <h2>Сайт, посвящённый польской поэтессе <span class="note-proper-name">Виславе Шимборской</span>,&nbsp;— лауреату Нобелевской премии 1996&nbsp;года</h2>
    </div>
@endsection
