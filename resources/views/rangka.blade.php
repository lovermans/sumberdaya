<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">

<head> <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ route('perlu-javascript') }}'">
    </noscript>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="Laravel 9" name="generator">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="desciption" content="{{ config('app.description') }}">
    <link rel="preconnect" href="{{ url('/') }}">
    <link rel="dns-prefetch" href="{{ url('/') }}">
    <meta content="{{ config('app.name', 'Laravel') }}" name="application-name">
    <meta content="{{ config('app.name', 'Laravel') }}" name="apple-mobile-web-app-title">
    <meta content="yes" name="mobile-web-app-capable">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="#d32f2f" name="apple-mobile-web-app-status-bar-style">
    <meta content="#d32f2f" name="theme-color">
    <meta content="#d32f2f" name="msapplication-TileColor">
    <meta content="#d32f2f" name="msapplication-navbutton-color">
    <meta content="{{ config('app.name', 'Laravel') }}" name="description">
    <meta content="{{ url('/') }}" name="msapplication-starturl">
    <link href="{{ url()->current() }}" rel="canonical">
    <link href="{{ config('app.author') }}" rel="author">
    <link href="{{ config('app.publisher') }}" rel="publisher">
    <link href="{{ asset(mix('images/Logo Perusahaan.webp')) }}" rel="shortcut icon">
    <link href="{{ asset(mix('images/Logo Perusahaan.webp')) }}" rel="icon" sizes="192x192">
    <link href="{{ asset(mix('images/Logo Perusahaan.webp')) }}" rel="apple-touch-icon">
    <meta content="{{ asset(mix('images/Logo Perusahaan.webp')) }}" name="msapplication-TileImage">
    <link href="{{ asset(mix('images/Logo Perusahaan.webp')) }}" rel="image_src">
    <link href="{{ asset('/favicon.ico') }}" rel="icon" type="image/x-icon">
    <link href="{{ asset(mix('/tampilan.css')) }}" rel="stylesheet"> 
    <script src="{{ asset(mix('/ss.js')) }}"></script> 
</head>

<body data-tematerang=""> <a class="skip-navigation tcetak" href="#main" tabindex="0">Langsung Ke Konten Utama</a><input
        class="tcetak" type="checkbox" id="nav"><input class="tcetak" type="checkbox" id="menu"><input
        class="tcetak" type="checkbox" id="tema"><label for="nav" id="nav-kanvas"
        class="blok-kanvas"></label><label for="menu" id="menu-kanvas" class="blok-kanvas"></label>
    <main id="main">
        <section>@yield('isi')</section>
    </main>
    <header class="tcetak">
        <section> <label for="nav" id="tbl-nav" title="Menu"><svg class="on" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menu' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg> <svg class="off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#menu' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></label> <a href="{{route('mulai')}}"><img id="logo" src="{{ asset(mix('/images/Logo Perusahaan.webp')) }}"
                title="{{ config('app.usaha') }}" alt="{{ config('app.usaha') }}" loading="lazy"></a>
            <h1>{{ config('app.name', 'Laravel') }}</h1> <label for="tema" id="tbl-tema" onclick=""
                title="Ubah Tema"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#tema' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></label> @auth<label for="menu" id="tbl-menu" onclick="" title="Akun"><img id="akun"
                        @class([
                            'svg' => !Storage::exists(
                                'sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp'
                            ),
                        ])
                        src="{{ Storage::exists('sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp') ? route('sdm.tautan-foto-profil', ['berkas_foto_profil' => auth()->user()->sdm_no_absen . '.webp' . '?' . filemtime(storage_path('app/sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp'))]) : asset(mix('/ikon.svg')) . '#akun' }}"
                        alt="{{ auth()->user()->nama ?? 'foto akun' }}"
                    alt="{{ auth()->user()->nama ?? 'foto akun' }}" loading="lazy"></label>@endauth <div
                class="bersih"></div>
        </section>
    </header> @auth <aside class="tcetak">
            <div id="menu-akun"> <a class="menu-xhr {{ request()->routeIs('akun') ? 'aktif' : '' }}"
                    href="{{ route('akun') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#akun' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>Profil</a> <a class="menu-xhr {{ request()->routeIs('ubah-sandi') ? 'aktif' : '' }}"
                    href="{{ route('ubah-sandi') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ asset(mix('/ikon.svg')) . '#kunci' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>Ubah Sandi</a>
                <form method="POST" action="{{ route('logout') }}"> @csrf<a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();"><svg viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ asset(mix('/ikon.svg')) . '#keluar' }}"
                                xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>Keluar</a> </form>
            </div>
    </aside> @endauth <nav class="tcetak">
        <div class="menu-t"><a class="nav-xhr {{ request()->routeIs('tentang-aplikasi') ? 'aktif' : '' }}"
                href="{{ route('tentang-aplikasi') }}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ asset(mix('/ikon.svg')) . '#informasi' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>Tentang Aplikasi</a></div>
    </nav>
    <div id="brand" class="tcetak"></div>
    <div id="hiasan" class="tcetak"></div>
    <footer class="tcetak">
        <section></section>
    </footer>
    <script src="{{ asset(mix('interaksi.js')) }}" defer></script>
</body>

</html>
