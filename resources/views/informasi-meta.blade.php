<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="Laravel 10" name="generator">
<meta name="csrf-token" content="{{ $app->session->token() }}">
<title>{{ $app->config->get('app.name', 'Laravel') }}</title>
<meta name="description" content="{{ $app->config->get('app.description') }}">
<link rel="preconnect" href="{{ $app->request->getSchemeAndHttpHost() }}">
<link rel="dns-prefetch" href="{{ $app->request->getSchemeAndHttpHost() }}">
<meta content="{{ $app->config->get('app.name', 'Laravel') }}" name="application-name">
<meta content="{{ $app->config->get('app.name', 'Laravel') }}" name="apple-mobile-web-app-title">
<meta content="yes" name="mobile-web-app-capable">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="#d32f2f" name="apple-mobile-web-app-status-bar-style">
<meta content="#d32f2f" name="theme-color">
<meta content="#d32f2f" name="msapplication-TileColor">
<meta content="#d32f2f" name="msapplication-navbutton-color">
<meta content="{{ $app->url->route('mulai') }}" name="msapplication-starturl">
<link href="{{ $app->request->url() }}" rel="canonical">
<link href="{{ $app->config->get('app.author') }}" rel="author">
<link href="{{ $app->config->get('app.publisher') }}" rel="publisher">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}"
    rel="shortcut icon">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}" rel="icon"
    sizes="192x192">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 512.png')) }}" rel="icon"
    sizes="512x512">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}"
    rel="apple-touch-icon">
<meta content="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}"
    name="msapplication-TileImage">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}"
    rel="image_src">
<link href="{{ $app->url->asset('/favicon.ico') }}" rel="icon" type="image/x-icon">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/tampilan.css')) }}" rel="stylesheet">
<link href="{{ $app->url->route('pwa-manifest') . '?' . 'aplikasivalet=' . $app->config->get('app.aplikasivalet') }}"
    crossorigin="use-credentials" rel="manifest">
<script src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/interaksi.js')) }}" defer></script>
<script src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/app.js')) }}"></script>