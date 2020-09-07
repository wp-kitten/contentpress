const mix = require('laravel-mix');
mix.setPublicPath('dist');

//@! sass
mix
    .sass('src/sass/theme-styles.scss','css')
;


//@! JS
mix
.react('src/jsx/theme-scripts.jsx', 'js')
;

