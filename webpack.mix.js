const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
*/

mix.disableNotifications();
// mix.setPublicPath('');
// mix.js('resources/js/app.js', 'public/app.js').version();
mix.scripts('resources/js/interaksi.js', 'public/interaksi.js').version();
mix.scripts('resources/js/echo-es.js', 'public/echo-es.js').version();
mix.scripts('resources/js/window-pusher.js', 'public/window-pusher.js').version();
mix.scripts('resources/js/slimselect-es.js', 'public/slimselect-es.js').version();
mix.scripts('resources/js/siapkan-foto-es.js', 'public/siapkan-foto-es.js').version();
// mix.scripts('resources/js/service-worker.js', 'public/service-worker.js').version();
// mix.combine(['resources/js/js.js', 'resources/js/slimselect.js'], 'public/interaksi.js', true).version();
mix.css('resources/css/app.css', 'public/tampilan.css').options({
    processCssUrls: false
}).version();
mix.copy('resources/css/gambar/*.webp', 'public/images').version();
mix.copy('resources/css/font/*.woff2', 'public/fonts').version();
mix.copy('resources/css/gambar/Ikon Aplikasi*.png', 'public/images').version();
mix.copy('resources/css/svg/*.svg', 'public').version();
// mix.copy('public/tampilan.css', 'resources/views/css.blade.php');
