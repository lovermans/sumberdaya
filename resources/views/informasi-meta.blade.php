<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="Laravel 10" name="generator">
<meta name="csrf-token" content="{{ $appRangka->session->token() }}">
<title>{{ $confRangka->get('app.name', 'Laravel') }}</title>
<meta name="description" content="{{ $confRangka->get('app.description') }}">
<link rel="preconnect" href="{{ $urlRangka->route('mulai') }}">
<link rel="dns-prefetch" href="{{ $urlRangka->route('mulai') }}">
<meta content="{{ $confRangka->get('app.name', 'Laravel') }}" name="application-name">
<meta content="{{ $confRangka->get('app.name', 'Laravel') }}" name="apple-mobile-web-app-title">
<meta content="yes" name="mobile-web-app-capable">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="#d32f2f" name="apple-mobile-web-app-status-bar-style">
<meta content="#d32f2f" name="theme-color">
<meta content="#d32f2f" name="msapplication-TileColor">
<meta content="#d32f2f" name="msapplication-navbutton-color">
<meta content="{{ $urlRangka->route('mulai', [], false) }}" name="msapplication-starturl">
<link href="{{ $rekRangka->url() }}" rel="canonical">
<link href="{{ $confRangka->get('app.author') }}" rel="author">
<link href="{{ $confRangka->get('app.publisher') }}" rel="publisher">
<link href="{{ $mixRangka('images/Ikon Aplikasi 192.png') }}" rel="shortcut icon">
<link href="{{ $mixRangka('images/Ikon Aplikasi 192.png') }}" rel="icon" sizes="192x192">
<link href="{{ $mixRangka('images/Ikon Aplikasi 512.png') }}" rel="icon" sizes="512x512">
<link href="{{ $mixRangka('images/Ikon Aplikasi 192.png') }}" rel="apple-touch-icon">
<meta content="{{ $mixRangka('images/Ikon Aplikasi 192.png') }}" name="msapplication-TileImage">
<link href="{{ $mixRangka('images/Ikon Aplikasi 192.png') }}" rel="image_src">
<link href="/favicon.ico" rel="icon" type="image/x-icon">
<link href="{{ $mixRangka('/tampilan.css') }}" rel="stylesheet">
<link href="/pwa-manifest.json" crossorigin="use-credentials" rel="manifest">
<script src="{{ $mixRangka('/interaksi.js') }}" defer></script>