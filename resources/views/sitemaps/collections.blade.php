{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($franchises as $franchise)
    <url>
        <loc>{{ route('collections.show', $franchise->slug) }}</loc>
        <lastmod>{{ $franchise->updated_at ? $franchise->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
@if($franchise->image)
        <image:image>
            <image:loc>{{ asset($franchise->image) }}</image:loc>
            <image:title>{{ htmlspecialchars($franchise->name, ENT_XML1, 'UTF-8') }} Colección</image:title>
        </image:image>
@endif
    </url>
@endforeach
</urlset>
