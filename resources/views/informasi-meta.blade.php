<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="Laravel 9" name="generator">
<meta name="csrf-token" content="{{ $sesiRangka->token() }}">
<title>{{ $confRangka->get('app.name', 'Laravel') }}</title>
<meta name="desciption" content="{{ $confRangka->get('app.description') }}">
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
<meta content="{{ $confRangka->get('app.name', 'Laravel') }}" name="description">
<meta content="{{ $urlRangka->route('mulai') }}" name="msapplication-starturl">
<link href="{{ $rekRangka->url() }}" rel="canonical">
<link href="{{ $confRangka->get('app.author') }}" rel="author">
<link href="{{ $confRangka->get('app.publisher') }}" rel="publisher">
<link href="{{ $mixRangka('images/Logo Perusahaan.webp') }}" rel="shortcut icon">
<link href="{{ $mixRangka('images/Logo Perusahaan.webp') }}" rel="icon" sizes="192x192">
<link href="{{ $mixRangka('images/Logo Perusahaan.webp') }}" rel="apple-touch-icon">
<meta content="{{ $mixRangka('images/Logo Perusahaan.webp') }}" name="msapplication-TileImage">
<link href="{{ $mixRangka('images/Logo Perusahaan.webp') }}" rel="image_src">
<link href="/favicon.ico" rel="icon" type="image/x-icon">
<link href="{{ $mixRangka('/tampilan.css') }}" rel="stylesheet">