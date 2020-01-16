<?= '<'.'?'.'xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($tags as $tag)
    <url>
@if (! empty($tag->url))
        <loc>{{ url($tag->url) }}</loc>
@endif
@if (! empty($tag->lastModificationDate))
        <lastmod>{{ $tag->lastModificationDate->format(DateTime::ATOM) }}</lastmod>
@endif
@if (! empty($tag->changeFrequency))
        <changefreq>{{ $tag->changeFrequency }}</changefreq>
@endif
@if (! empty($tag->priority))
        <priority>{{ number_format($tag->priority,1) }}</priority>
@endif
    </url>
@endforeach
</urlset>
