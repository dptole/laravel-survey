#!/bin/sh
set -x

# This file contains the list of commands necessary to setup the blog
# These commands are gonna be running inside the elm-mini-blog container

# Cleanup
# By removing this command the installation process will be faster but
# changes in the composer or npm files will be ignored
rm -rf node_modules package-lock.json vendor composer.lock composer.phar composer-setup.php .env

# Remove old bundles if any
rm -rf public/js/*.js public/css/*.css

# Setup configs
pwd_string=$(pwd)
PWD_SAFE=${pwd_string//\//\\/}
LARAVEL_PUBLIC_PATH=$PWD_SAFE\\/public

# Install alpine packages not related to PHP
apk add curl bash vim nodejs npm mariadb mariadb-client openrc nginx nginx-mod-http-geoip2

# Install alpine PHP packages from a specific repository
# https://github.com/codecasts/php-alpine
curl 'https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub' -o /etc/apk/keys/php-alpine.rsa.pub
apk add php7-curl php7-dom php7-fpm php7-json php7-session php7-openssl php7-pdo_mysql php7-pdo php7-fileinfo --repository https://dl.bintray.com/php-alpine/v3.10/php-7.4

# Config ~/.vimrc file
cat <<EOF -> ~/.vimrc
set sts=2
set ts=2
set sw=2
set et
set number
set encoding=utf-8
syntax on
autocmd FileType * setlocal formatoptions-=c formatoptions-=r formatoptions-=o
autocmd BufReadPost,BufNewFile *.elm setfiletype haskell
EOF

# Config ~/.bashrc file
cat <<EOF -> ~/.bashrc
#!/bin/bash
export DB_DATA_PATH=/var/lib/mysql
export DB_ROOT_PASS=root
export MAX_ALLOWED_PACKET=200M

EOF

# Create .env file
cp .env.example .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=localhost/' .env
# https://stackoverflow.com/a/28905017
grep DB_SOCKET .env || sed -i 's/DB_CONNECTION/DB_SOCKET=\/run\/mysqld\/mysqld.sock\nDB_CONNECTION/' .env

# Copy DB_USERNAME and DB_PASSWORD from .env file
# as DB_USER and DB_PASS to the ~/.bashrc
grep DB_USERNAME .env | sed 's/DB_USERNAME/export DB_USER/' | tee -a ~/.bashrc
grep DB_PASSWORD .env | sed 's/DB_PASSWORD/export DB_PASS/' | tee -a ~/.bashrc

# Export the env variables
. ~/.bashrc

# Boot openrc
openrc boot

# Start PHP-FPM
rc-service php-fpm7 start

# Create nginx default configuration file
cat <<'EOF' -> /etc/nginx/conf.d/default.conf
# Disabled for the moment
#
# geoip2 /app/app/data/GeoLite2-Country_20200128/GeoLite2-Country.mmdb {
#   auto_reload 5m;
# 
#   $IP_geoip2_data_country_code source=$remote_addr country iso_code;
#   $IP_geoip2_data_country_name_en source=$remote_addr country names en;
# 
#   $HEADER_geoip2_data_country_code source=$http_x_forwarded_for country iso_code;
#   $HEADER_geoip2_data_country_name_en source=$http_x_forwarded_for country names en;
# }
# 
# geoip2 /app/app/data/GeoLite2-City_20200128/GeoLite2-City.mmdb {
#   auto_reload 5m;
# 
#   $IP_geoip2_data_city_name_en source=$remote_addr city names en;
# 
#   $HEADER_geoip2_data_city_name_en source=$http_x_forwarded_for city names en;
# }
# 
# geoip2 /app/app/data/GeoLite2-ASN_20200128/GeoLite2-ASN.mmdb {
#   auto_reload 5m;
# 
#   $IP_geoip2_data_asn_code source=$remote_addr autonomous_system_number;
#   $IP_geoip2_data_asn_name source=$remote_addr autonomous_system_organization;
# 
#   $HEADER_geoip2_data_asn_code source=$http_x_forwarded_for autonomous_system_number;
#   $HEADER_geoip2_data_asn_name source=$http_x_forwarded_for autonomous_system_organization;
# }

server {
  listen LARAVEL_NGINX_HTTP_PORT;
  server_name localhost;
  root LARAVEL_NGINX_ROOT;

  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-XSS-Protection "1; mode=block";
  add_header X-Content-Type-Options "nosniff";
  gzip on;
  gzip_types text/plain text/css text/javascript text/x-javascript application/javascript application/x-javascript;

  index index.html index.htm index.php;

  charset utf-8;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location = /favicon.ico { access_log off; log_not_found off; }
  location = /robots.txt  { access_log off; log_not_found off; }

  error_page 404 /index.php;

  location ~ \.php$ {
    fastcgi_pass localhost:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;

    # Disabled for the moment

    # fastcgi_param MM_IP_COUNTRY_CODE            $IP_geoip2_data_country_code;
    # fastcgi_param MM_IP_EN_COUNTRY_NAME         $IP_geoip2_data_country_name_en;
    # fastcgi_param MM_IP_EN_CITY_NAME            $IP_geoip2_data_city_name_en;
    # fastcgi_param MM_IP_ASN_CODE                $IP_geoip2_data_asn_code;
    # fastcgi_param MM_IP_ASN_NAME                $IP_geoip2_data_asn_name;

    # fastcgi_param MM_HEADER_COUNTRY_CODE        $HEADER_geoip2_data_country_code;
    # fastcgi_param MM_HEADER_EN_COUNTRY_NAME     $HEADER_geoip2_data_country_name_en;
    # fastcgi_param MM_HEADER_EN_CITY_NAME        $HEADER_geoip2_data_city_name_en;
    # fastcgi_param MM_HEADER_ASN_CODE            $HEADER_geoip2_data_asn_code;
    # fastcgi_param MM_HEADER_ASN_NAME            $HEADER_geoip2_data_asn_name;

    include fastcgi_params;
  }

  location ~ /\.(?!well-known).* {
    deny all;
  }
}
EOF

sed -i 's/LARAVEL_NGINX_HTTP_PORT/'$LARAVEL_HTTP_PORT'/' /etc/nginx/conf.d/default.conf
sed -i 's/LARAVEL_NGINX_ROOT/'$LARAVEL_PUBLIC_PATH'/' /etc/nginx/conf.d/default.conf

# Install PDO PHP extension on docker alpine
# https://github.com/docker-library/php/issues/465
docker-php-ext-install pdo pdo_mysql

# Setup mysql / mariadb
# https://wiki.alpinelinux.org/wiki/MariaDB
mkdir -p $DB_DATA_PATH

/etc/init.d/mariadb setup
rc-service mariadb start

# Creating default user and database
mysql -e "CREATE USER homestead@localhost IDENTIFIED BY 'secret';"
mysql -e "CREATE DATABASE homestead;"
mysql -e "GRANT ALL ON homestead.* TO homestead@localhost;"

# Install npm dependencies
npm i

# Download composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
ln -sf $(pwd)/composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Speed up composer
# https://packagist.org/packages/hirak/prestissimo
# https://stackoverflow.com/a/38102206
composer global require hirak/prestissimo

# Install composer's dependencies
composer install

# Clean up so that the next command works
php artisan optimize:clear

# Generate new APP_KEY on .env file
php artisan key:generate

# Start DB migrations
php artisan migrate

if [ "$LARAVEL_SERVER_ENV" == "dev" ]
then
  # http://manpages.org/openrc-run/8

  # NPM-openrc service
  cat <<'EOF' -> /root/npm-run-watch.js
const child_process = require('child_process')
const cp = child_process.exec('npm run watch', {cwd: 'PWD'})
cp.stdout.pipe(process.stdout)
cp.stderr.pipe(process.stderr)
cp.on('exit', () => process.exit())
process.on('SIGTERM', () => cp.kill())

EOF

  # PHP-openrc service
  cat <<'EOF' -> /root/php-artisan-serve.js
const child_process = require('child_process')
const cp = child_process.exec('php artisan serve --host=0.0.0.0 --port=LARAVEL_HTTP_PORT', {cwd: 'PWD'})
cp.stdout.pipe(process.stdout)
cp.stderr.pipe(process.stderr)
cp.on('exit', () => process.exit())
process.on('SIGTERM', () => cp.kill())

EOF

  # Run npm run watch as an openrc service
  cat <<'EOF' -> /etc/init.d/npm-run-watch
#!/sbin/openrc-run

name="npm run watch"
description="NPM watch command for rebundle the JavaScript/CSS assets"

command="/usr/bin/node"
command_args="/root/npm-run-watch.js"
command_background="yes"

pidfile="/run/$RC_SVCNAME.pid"

output_log="/var/log/$RC_SVCNAME.log"
error_log="/var/log/$RC_SVCNAME.log"

EOF

  # Run php artisan serve an openrc service
  cat <<'EOF' -> /etc/init.d/php-artisan-serve
#!/sbin/openrc-run

name="php artisan serve"
description="Dev server for PHP using artisan"

command="/usr/bin/node"
command_args="/root/php-artisan-serve.js"
command_background="yes"

pidfile="/run/$RC_SVCNAME.pid"

output_log="/var/log/$RC_SVCNAME.log"
error_log="/var/log/$RC_SVCNAME.err"

EOF

  chmod +x /etc/init.d/npm-run-watch
  chmod +x /etc/init.d/php-artisan-serve

  sed -i 's/PWD/'$PWD_SAFE'/' /root/npm-run-watch.js

  sed -i 's/PWD/'$PWD_SAFE'/' /root/php-artisan-serve.js
  sed -i 's/LARAVEL_HTTP_PORT/'$LARAVEL_HTTP_PORT'/' /root/php-artisan-serve.js

  # Watch for changes in the js/css files to rebundle
  rc-service npm-run-watch start

  # Start the artisan server in the background (dev)
  rc-service php-artisan-serve start

else
  # Create production JavaScript and CSS assets
  npm run production

  # Not sure why...
  sed -i 's/APP_ENV=local/APP_ENV=production/' .env

  # Nginx user requires write access
  chmod -R o+w storage

  # Perform artisan optimizations
  composer install --optimize-autoloader --no-dev
  php artisan optimize:clear
  php artisan optimize

  # Start the prod server
  rc-service nginx start

  # Set these services for the default runlevel
  rc-update add php-fpm7 default
  rc-update add nginx default

fi

# Don't stop the container on server crash
set +x
sleep 3

echo '###################################'
echo '#          All is set!            #'
echo '###################################'

while :;
do
  echo Idle...
  date +%F_%T
  sleep $((2**20))
done
