Laravel 6 survey
================

  [![Build status][circle-ci-badge]][circle-ci]
  [![Coverage Status][coveralls-badge]][coveralls]
  [![StyleCI status][styleci-badge]][style-ci]
  [![Docs][doxygen-badge]][doxygen-url]
  [![Made in][brazil-badge]][brazil-country]

Custom survey app in Laravel 6.

Pre-requisites
==============

- Linux
- Node.js v7+
- MySQL v5.5+ (or MariaDB)
- PHP v7.2+

> Or you could install it via [docker][docker-install-container-url].

Building
========

Inside the repo's folder.

Install Node.js dependencies, it will take some time.

```
$ npm i
```

Download [composer][composer-url].

```
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php composer-setup.php
```

> If you want to speed up composer then download [prestissimo][prestissimo-url]. Hopefully this is just a [temporary solution][composer-parallel-downloads-url].
>
> ```
> $ php composer.phar global require hirak/prestissimo
> ```

Install [composer][composer-url]'s dependencies.

```
$ php composer.phar install
```

Copy and edit your own `.env` file out of `.env.example`, it is essential.

```
$ cp .env.example .env
```

Generate the new `APP_KEY` value on the `.env` file.

```
$ php artisan key:generate
```

You'll have to configure MySQL yourself, it's not that hard.

```
Log into your MySQL as root.

Say your .env file has the following confs

  DB_USERNAME=laravel_survey_user
  DB_HOST=127.0.0.1
  DB_PASSWORD=laravel_survey_pass
  DB_DATABASE=laravel_survey_database

Create the user DB_USERNAME at DB_HOST identified by DB_PASSWORD.
> CREATE USER laravel_survey_user@127.0.0.1 IDENTIFIED BY 'laravel_survey_pass';

Create the database DB_DATABASE.
> CREATE DATABASE laravel_survey_database;

Grant DB_USERNAME all privileges to DB_DATABASE.
> GRANT ALL ON laravel_survey_database.* TO laravel_survey_user@127.0.0.1;
```

Execute laravel's migrations.

```
$ php artisan migrate
```

Run webpack's tasks to generate JavaScript and CSS assets.

```
$ npm run dev
OR
$ npm run production # to generate minified files
```

Start up the server.

```
$ php artisan serve
```

> You will have to access `http://localhost:8000/laravel/`.

Optional 3rd party integrations
===============================

I recommend you just updating the `.env` file variables `PUSHER_ENABLED=true` and `GOOGLE_RECAPTCHA_ENABLED=true` and starting up the server. The first page will tell you about missing configurations in these optional 3rd party integrations. A form will be created for input and validation of the credentials. If some error occur you'll be notified. In case all credentials are correct the `.env` file will be updated and you'll be able to proceed normally.

# Pusher

Create your app at [pusher's dashboard][pusher-url]. Edit the `.env` file with your credentials

```
PUSHER_ENABLED=true
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

# Google Recaptcha

Go to [Google Recaptcha's admin page][google-recaptcha-url] and register a new site. Edit the `.env` file with your keys

```
GOOGLE_RECAPTCHA_ENABLED=true
GOOGLE_RECAPTCHA_SITE_SECRET=
GOOGLE_RECAPTCHA_SITE_KEY=
```

Community
=========

- [Code of conduct][CODE_OF_CONDUCT]
- [Contributing][CONTRIBUTING]

License
=======

[MIT][LICENSE]

[doxygen-badge]: https://img.shields.io/badge/docs-Doxygen-brightgreen.svg
[doxygen-url]: https://dptole.ngrok.io/laravel/doxygen/
[coveralls-badge]: https://coveralls.io/repos/github/dptole/laravel-survey/badge.svg?branch=master
[coveralls]: https://coveralls.io/github/dptole/laravel-survey
[brazil-badge]: https://img.shields.io/badge/Made%20in%20Brazil-555.svg?logo=data:image/svg%2bxml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3QgZmlsbD0iIzRhOWMzOCIgc3Ryb2tlPSIjNGE5YzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgeD0iMCIgeT0iOC41IiB3aWR0aD0iNjQiIGhlaWdodD0iNDciLz48cGF0aCBzdHJva2U9IiNmZWUxM2IiIGlkPSJzdmdfNCIgZD0ibTUuNjkzMTgsMzJsMjYuMzA2ODIsLTIwLjEyMTIxbDI2LjMwNjgyLDIwLjEyMTIxbC0yNi4zMDY4MiwyMC4xMjEyMWwtMjYuMzA2ODIsLTIwLjEyMTIxeiIgc3Ryb2tlLW9wYWNpdHk9Im51bGwiIHN0cm9rZS13aWR0aD0iMS41IiBmaWxsPSIjZmVlMTNiIi8+PHBhdGggc3Ryb2tlPSIjMDUyZDc2IiBpZD0ic3ZnXzciIGQ9Im0yMS43MjkzMSwzMS45MDc4MWwwLDBjMCwtNS4yOTQ1MSA0LjYyOTc4LC05LjU4NjU1IDEwLjM0MDkxLC05LjU4NjU1bDAsMGMyLjc0MjU4LDAgNS4zNzI4MywxLjAxMDAxIDcuMzEyMTMsMi44MDc4M2MxLjkzOTMsMS43OTc4MyAzLjAyODc4LDQuMjM2MiAzLjAyODc4LDYuNzc4NzFsMCwwYzAsNS4yOTQ1IC00LjYyOTc4LDkuNTg2NTUgLTEwLjM0MDkxLDkuNTg2NTVsMCwwYy01LjcxMTEzLDAgLTEwLjM0MDkxLC00LjI5MjA0IC0xMC4zNDA5MSwtOS41ODY1NWwwLDAuMDAwMDF6bTEwLjM0MDkxLC05LjU4NjU1bDAsMTkuMTczMDhtLTEwLjM0MDkxLC05LjU4NjU1bDIwLjY4MTgyLDAiIHN0cm9rZS13aWR0aD0iMS41IiBmaWxsPSIjMDUyZDc2Ii8+PC9zdmc+
[brazil-country]: https://www.google.com/maps/place/Brazil/
[styleci-badge]: https://github.styleci.io/repos/88016589/shield?branch=dev&style=flat
[style-ci]: https://github.styleci.io/repos/88016589
[circle-ci]: https://circleci.com/gh/dptole/laravel-survey
[circle-ci-badge]: https://img.shields.io/circleci/project/dptole/laravel-survey.svg
[google-recaptcha-url]: https://www.google.com/recaptcha/admin#list
[pusher-url]: https://dashboard.pusher.com/accounts/sign_in
[composer-url]: https://getcomposer.org/
[docker-install-container-url]: https://github.com/dptole/laravel-survey/blob/master/docker
[prestissimo-url]: https://packagist.org/packages/hirak/prestissimo
[composer-parallel-downloads-url]: https://github.com/composer/composer/pull/7904
[LICENSE]: https://github.com/dptole/laravel-survey/blob/master/LICENSE
[CONTRIBUTING]: https://github.com/dptole/laravel-survey/blob/master/CONTRIBUTING.md
[CODE_OF_CONDUCT]: https://github.com/dptole/laravel-survey/blob/master/CODE_OF_CONDUCT.md
