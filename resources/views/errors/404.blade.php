@extends('layouts.shell', [
    'bodyClass' => 'error-layout',
    'page' => 'error',
    'title' => 'Вислава Шимборская. Страница не найдена',
])

@section('body')
    <main id="main" class="error-main">
        <article class="page error-page">
            <h2>404 — Страница не найдена</h2>
            <p>Такой страницы здесь нет — возможно, адрес изменился или в нём опечатка.</p>
            <p><a href="{{ route('main') }}">Вернуться на обложку</a></p>
        </article>
    </main>
@endsection
