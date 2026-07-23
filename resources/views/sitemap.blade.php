<?= '<'.'?'.'xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($tags as $tag)
    <url>
        <loc>{{ $tag['url'] }}</loc>
        <lastmod>{{ $tag['lastModificationDate']->format(DateTime::ATOM) }}</lastmod>
        <priority>{{ number_format($tag['priority'], 1) }}</priority>
    </url>
@endforeach
</urlset>
