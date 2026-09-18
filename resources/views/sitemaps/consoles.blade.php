{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($consoles as $console)
    <url>
        <loc>{{ route('consoles.show', $console->slug) }}</loc>
        <lastmod>{{ $console->updated_at ? $console->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
@if($console->icon)
        <image:image>
            <image:loc>{{ asset($console->icon) }}</image:loc>
            <image:title>{{ htmlspecialchars($console->name, ENT_XML1, 'UTF-8') }}</image:title>
        </image:image>
@endif
    </url>
@endforeach
</urlset>
