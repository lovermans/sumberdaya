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
 mix.js('resources/js/js.js', 'public/interaksi.js').version();
 mix.combine('resources/js/slimselect.js', 'public/ss.js').version();
 mix.css('resources/css/css.css', 'public/tampilan.css').version();
 mix.copy('resources/css/gambar/*.webp', 'public/images').version();
 mix.copy('resources/css/svg/*.svg', 'public').version();
