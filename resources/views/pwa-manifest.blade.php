{
"short_name": "{{ $app->config->get('app.name') }}",
"name": "{{ $app->config->get('app.description') }}",
"icons": [
{
"src": "{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}",
"type": "image/png",
"sizes": "192x192"
},
{
"src": "{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 512.png')) }}",
"type": "image/png",
"sizes": "512x512",
"purpose": "any maskable"
}
],
"id": "{{ $app->request->getBasePath() . '/' }}",
"start_url": "{{ $app->request->getBasePath() . '/' }}",
"background_color": "#d32f2f",
"display": "standalone",
"theme_color": "#d32f2f",
"description": "{{ $app->config->get('app.description') }}"
}