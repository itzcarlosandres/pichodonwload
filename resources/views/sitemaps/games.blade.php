{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($games as $game)
    <url>
        <loc>{{ route('game.show', $game->slug) }}</loc>
        <lastmod>{{ $game->updated_at ? $game->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
@if($game->cover_url)
        <image:image>
            <image:loc>{{ $game->cover_url }}</image:loc>
            <image:title>{{ htmlspecialchars($game->title, ENT_XML1, 'UTF-8') }}</image:title>
        </image:image>
@endif
@if($game->banner_url)
        <image:image>
            <image:loc>{{ $game->banner_url }}</image:loc>
            <image:title>{{ htmlspecialchars($game->title, ENT_XML1, 'UTF-8') }} Banner</image:title>
        </image:image>
@endif
    </url>
@endforeach
</urlset>
