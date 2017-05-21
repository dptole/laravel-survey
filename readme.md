Laravel survey
==============

Custom survey app in laravel.

Pre-requisites
==============

- Linux (any dist)
- Node.js v7+
- MySQL v5.5+
- PHP v5.6+

Building
========

Inside the repo's folder.

Install Node.js dependencies and generate, it will take some time.

```
$ npm i
```

Download [composer][composer-url].

```
$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
$ php composer-setup.php
```

Install [composer][composer-url]'s dependencies.

```
$ php composer.phar install
```

Generate the new `APP_KEY` value on the `.env` file.

```
$ php artisan key:generate
```

Copy and edit your own `.env` file out of `.env.example`, it is essential.

```
$ cp .env.example .env
```

You have to configure MySQL yourself.

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

Run webpack's tasks to generate `app.js` and `app.css`.

```
$ npm run dev
OR
$ npm run production # to generate minified files
```

Start up the server.

```
$ php artisan serve
```

[composer-url]: https://getcomposer.org/

