{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $p)
    <url>
        <loc>{{ $p['loc'] }}</loc>
        <lastmod>{{ $p['lastmod'] }}</lastmod>
        <changefreq>{{ $p['changefreq'] }}</changefreq>
        <priority>{{ $p['priority'] }}</priority>
    </url>
@endforeach
</urlset>
