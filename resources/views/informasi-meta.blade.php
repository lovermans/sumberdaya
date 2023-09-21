<noscript>
    <meta HTTP-EQUIV="refresh" content="0;url='{{ $app->url->route('perlu-javascript') }}'">
</noscript>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="generator" content="Laravel 10">
<meta name="csrf-token" content="{{ $app->session->token() }}">
<title>{{ $app->config->get('app.name', 'Laravel') }}</title>
<meta name="description" content="{{ $app->config->get('app.description') }}">
<link href="{{ $app->request->getSchemeAndHttpHost() }}" rel="preconnect">
<link href="{{ $app->request->getSchemeAndHttpHost() }}" rel="dns-prefetch">
<meta name="application-name" content="{{ $app->config->get('app.name', 'Laravel') }}">
<meta name="apple-mobile-web-app-title" content="{{ $app->config->get('app.name', 'Laravel') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#d32f2f">
<meta name="theme-color" content="#d32f2f">
<meta name="msapplication-TileColor" content="#d32f2f">
<meta name="msapplication-navbutton-color" content="#d32f2f">
<meta name="msapplication-starturl" content="{{ $app->url->route('mulai') }}">
<link href="{{ $app->request->url() }}" rel="canonical">
<link href="{{ $app->config->get('app.author') }}" rel="author">
<link href="{{ $app->config->get('app.publisher') }}" rel="publisher">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}" rel="shortcut icon">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}" rel="icon" sizes="192x192">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 512.png')) }}" rel="icon" sizes="512x512">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}" rel="apple-touch-icon">
<meta name="msapplication-TileImage" content="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}" rel="image_src">
<link type="image/x-icon" href="{{ $app->url->asset('/favicon.ico') }}" rel="icon">
<link href="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/tampilan.css')) }}" rel="stylesheet">
<link href="{{ $app->url->route('pwa-manifest') . '?' . 'aplikasivalet=' . $app->config->get('app.aplikasivalet') }}" crossorigin="use-credentials"
    rel="manifest">
<script nonce="{{ $app->request->session()->get('sesiNonce') }}" src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/interaksi.js')) }}" defer></script>
