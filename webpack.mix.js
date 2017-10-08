const { mix } = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js/app.js')
   .js('resources/assets/js/questions/main.js', 'public/js/questions.js')
   .js('resources/assets/js/start-survey/main.js', 'public/js/start-survey.js')
   .js('resources/assets/js/manage-survey/main.js', 'public/js/manage-survey.js')
   .sass('resources/assets/sass/app.scss', 'public/css')

