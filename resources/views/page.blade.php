@extends('layout')

@section('content')
    {!! $content !!}
@endsection

@section('notes')
    {!! $notes !!}
@endsection

@section('navigation')
    <ul id="pager">
        @if($tocCurrent == '/')
            <li id="center-bottom-nav" class="first"><span>Обложка</span><span class="shortkey"></span></li>
        @else
            <li class="first"><a href="/">Обложка</a><span class="shortkey">(ctrl + ↓)</span></li>
        @endif

        @foreach($nav['content'] as $key => $value)
            @if($value['class'] != 'frontpage')
                @if($nav['id'] == $key)
                    <li id="center-bottom-nav"><span>{{ $key }}</span></li>
                @else
                    <li><a title="{{ $value['title'] }}" href="/{{ $value['parent'] }}/{{ $value['href'] }}">{{ $key }}</a></li>
                @endif
            @endif
        @endforeach
        <li class="last"><a href="#" class="show-content-link">Содержание</a><span class="shortkey">(ctrl + ↑)</span></li>
    </ul>
@endsection

@section('tableOfContent')
    <ul id="contents-wrap">
        @if($tocCurrent == '/')
            <li class="chapter-link-list wide"><span class="chapter-link active">Обложка</span></li>
        @else
            <li class="chapter-link-list wide"><a href="/" class="chapter-link">Обложка</a></li>
        @endif

        @foreach($toc as $parent_key => $parent)
            <li class="chapter-link-list">
                <span class="chapter-link">{{ parent_alias($parent_key) }}</span>
                <ul>
                @foreach($parent as $node)
                    @if($node['href'] == $title)
                        <li><span class="active">{{ $node['title'] }}</span></li>
                    @else
                        <li><a href="/{{ $parent_key }}/{{ $node['href'] }}">{{ $node['title'] }}</a></li>
                    @endif
                @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
@endsection
