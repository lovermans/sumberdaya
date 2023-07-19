{
    "short_name": "{{ $confRangka->get('app.name') }}",
    "name": "{{ $confRangka->get('app.description') }}",
    "icons": [
        {
            "src": "{{ $urlRangka->asset($mixRangka('/images/Ikon Aplikasi 192.png')) }}",
            "type": "image/png",
            "sizes": "192x192"
        },
        {
            "src": "{{ $urlRangka->asset($mixRangka('/images/Ikon Aplikasi 512.png')) }}",
            "type": "image/png",
            "sizes": "512x512",
            "purpose": "any maskable"
        }
    ],
    "id": "{{ $rekRangka->getBasePath() . '/' }}",
    "start_url": "{{ $rekRangka->getBasePath() . '/' }}",
    "background_color": "#d32f2f",
    "display": "standalone",
    "theme_color": "#d32f2f",
    "description": "{{ $confRangka->get('app.description') }}"
}