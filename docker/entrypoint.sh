#!/bin/sh
set -x

# This file contains the list of commands necessary to setup the blog
# These commands are gonna be running inside the elm-mini-blog container

# Setup functions

get_maxmind_folder() {
  MAXMIND_DIR="$HOME/maxmind"
  mkdir -p "$MAXMIND_DIR"
  echo "$MAXMIND_DIR"
}

get_maxmind_db() {
  # GeoLite2-ASN
  # GeoLite2-City
  # GeoLite2-Country
  EDITION_ID="$1"
  LICENSE_KEY="$2"

  MAXMIND_DIR=$(get_maxmind_folder)

  EDITION_SUFFIX="tar.gz"
  EDITION_ID_TAR_GZ="$EDITION_ID.$EDITION_SUFFIX"
  MAXMIND_DB_TAR_GZ="$MAXMIND_DIR/$EDITION_ID_TAR_GZ"
  MAXMIND_UNTAR_FOLDER="$(dirname $MAXMIND_DB_TAR_GZ)"

  curl "https://download.maxmind.com/app/geoip_download?edition_id=$EDITION_ID&license_key=$LICENSE_KEY&suffix=$EDITION_SUFFIX" -o "$MAXMIND_DB_TAR_GZ"
  LINES="$(wc -l $MAXMIND_DB_TAR_GZ | awk '{print$1}')"

  if [ "$LINES" -gt 10 ]
  then
    MAXMIND_FOLDER="$(tar -tzf "$MAXMIND_DB_TAR_GZ" | head -n 1)"
    MAXMIND_UNTAR_FOLDER="$MAXMIND_UNTAR_FOLDER/$MAXMIND_FOLDER"
    mkdir -p "$MAXMIND_UNTAR_FOLDER"
    tar -C "$MAXMIND_UNTAR_FOLDER" -xzf "$MAXMIND_DB_TAR_GZ"
    MAXMIND_UNTAR_FOLDER_CONTENT="$MAXMIND_UNTAR_FOLDER/$MAXMIND_FOLDER"
    MAXMIND_MMDB="$MAXMIND_UNTAR_FOLDER_CONTENT/$EDITION_ID.mmdb"
    echo "${MAXMIND_MMDB//\/\//\/}"
  fi
}

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
export LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY
export LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY
export LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY
export LARAVEL_WORKDIR=$LARAVEL_WORKDIR

EOF

# Create .env file
cp .env.example .env
echo "LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" >> .env
echo "LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY" >> .env
echo "LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY" >> .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=localhost/' .env
# https://stackoverflow.com/a/28905017
grep DB_SOCKET .env || sed -i 's/DB_CONNECTION/DB_SOCKET=\/run\/mysqld\/mysqld.sock\nDB_CONNECTION/' .env

# Copy DB_USERNAME and DB_PASSWORD from .env file
# as DB_USER and DB_PASS to the ~/.bashrc
grep DB_USERNAME .env | sed 's/DB_USERNAME/export DB_USER/' | tee -a ~/.bashrc
grep DB_PASSWORD .env | sed 's/DB_PASSWORD/export DB_PASS/' | tee -a ~/.bashrc
grep DB_DATABASE .env | sed 's/DB_DATABASE/export DB_NAME/' | tee -a ~/.bashrc
grep DB_HOST .env | sed 's/DB_HOST/export DB_HOST/' | tee -a ~/.bashrc

# Export the env variables
. ~/.bashrc

# Boot openrc
openrc boot

# Maxmind GeoIP2 Nginx module

MAXMIND_COUNTRY_TEMPLATE="$(get_maxmind_folder)/maxmind-country-template"
FASTCGI_COUNTRY_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-country-params-template"
touch "$MAXMIND_COUNTRY_TEMPLATE"
touch "$FASTCGI_COUNTRY_PARAMS_TEMPLATE"
if [ "$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" != "false" ]
then
  MAXMIND_COUNTRY_MMDB="$(get_maxmind_db GeoLite2-Country $LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY)"
  cat <<'EOF' -> $MAXMIND_COUNTRY_TEMPLATE
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_country_code source=$remote_addr country iso_code;
  $IP_geoip2_data_country_name_en source=$remote_addr country names en;

  $HEADER_geoip2_data_country_code source=$http_x_forwarded_for country iso_code;
  $HEADER_geoip2_data_country_name_en source=$http_x_forwarded_for country names en;
}
EOF

  cat <<'EOF' -> $FASTCGI_COUNTRY_PARAMS_TEMPLATE
    # var_dump($_SERVER['MM_IP_COUNTRY_CODE']);
    fastcgi_param MM_IP_COUNTRY_CODE            $IP_geoip2_data_country_code;

    # var_dump($_SERVER['MM_IP_EN_COUNTRY_NAME']);
    fastcgi_param MM_IP_EN_COUNTRY_NAME         $IP_geoip2_data_country_name_en;

    # var_dump($_SERVER['MM_HEADER_COUNTRY_CODE']);
    fastcgi_param MM_HEADER_COUNTRY_CODE        $HEADER_geoip2_data_country_code;

    # var_dump($_SERVER['MM_HEADER_EN_COUNTRY_NAME']);
    fastcgi_param MM_HEADER_EN_COUNTRY_NAME     $HEADER_geoip2_data_country_name_en;

EOF

  sed -i "s/MMDB/${MAXMIND_COUNTRY_MMDB//\//\\/}/" $MAXMIND_COUNTRY_TEMPLATE
fi

MAXMIND_ASN_TEMPLATE="$(get_maxmind_folder)/maxmind-asn-template"
FASTCGI_ASN_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-asn-params-template"
touch "$MAXMIND_ASN_TEMPLATE"
touch "$FASTCGI_ASN_PARAMS_TEMPLATE"
if [ "$LARAVEL_MAXMIND_ASN_LICENSE_KEY" != "false" ]
then
  MAXMIND_ASN_MMDB="$(get_maxmind_db GeoLite2-ASN $LARAVEL_MAXMIND_ASN_LICENSE_KEY)"
  cat <<'EOF' -> $MAXMIND_ASN_TEMPLATE
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_asn_code source=$remote_addr autonomous_system_number;
  $IP_geoip2_data_asn_name source=$remote_addr autonomous_system_organization;

  $HEADER_geoip2_data_asn_code source=$http_x_forwarded_for autonomous_system_number;
  $HEADER_geoip2_data_asn_name source=$http_x_forwarded_for autonomous_system_organization;
}
EOF

  cat <<'EOF' -> $FASTCGI_ASN_PARAMS_TEMPLATE
    # var_dump($_SERVER['MM_IP_ASN_CODE']);
    fastcgi_param MM_IP_ASN_CODE                $IP_geoip2_data_asn_code;

    # var_dump($_SERVER['MM_IP_ASN_NAME']);
    fastcgi_param MM_IP_ASN_NAME                $IP_geoip2_data_asn_name;

    # var_dump($_SERVER['MM_HEADER_ASN_CODE']);
    fastcgi_param MM_HEADER_ASN_CODE            $HEADER_geoip2_data_asn_code;

    # var_dump($_SERVER['MM_HEADER_ASN_NAME']);
    fastcgi_param MM_HEADER_ASN_NAME            $HEADER_geoip2_data_asn_name;

EOF

  sed -i "s/MMDB/${MAXMIND_ASN_MMDB//\//\\/}/" $MAXMIND_ASN_TEMPLATE
fi

MAXMIND_CITY_TEMPLATE="$(get_maxmind_folder)/maxmind-city-template"
FASTCGI_CITY_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-city-params-template"
touch "$MAXMIND_CITY_TEMPLATE"
touch "$FASTCGI_CITY_PARAMS_TEMPLATE"
if [ "$LARAVEL_MAXMIND_CITY_LICENSE_KEY" != "false" ]
then
  MAXMIND_CITY_MMDB="$(get_maxmind_db GeoLite2-City $LARAVEL_MAXMIND_CITY_LICENSE_KEY)"
  cat <<'EOF' -> $MAXMIND_CITY_TEMPLATE
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_city_name_en source=$remote_addr city names en;

  $HEADER_geoip2_data_city_name_en source=$http_x_forwarded_for city names en;
}
EOF

  cat <<'EOF' -> $FASTCGI_CITY_PARAMS_TEMPLATE
    # var_dump($_SERVER['MM_IP_EN_CITY_NAME']);
    fastcgi_param MM_IP_EN_CITY_NAME            $IP_geoip2_data_city_name_en;

    # var_dump($_SERVER['MM_HEADER_EN_CITY_NAME']);
    fastcgi_param MM_HEADER_EN_CITY_NAME        $HEADER_geoip2_data_city_name_en;

EOF

  sed -i "s/MMDB/${MAXMIND_CITY_MMDB//\//\\/}/" $MAXMIND_CITY_TEMPLATE
fi

# Create nginx default configuration file
NGINX_DEFAULT_VIRTUAL_HOST="/etc/nginx/conf.d/default.conf"
cat <<'EOF' -> $NGINX_DEFAULT_VIRTUAL_HOST
MAXMIND_COUNTRY_MMDB
MAXMIND_ASN_MMDB
MAXMIND_CITY_MMDB

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

FASTCGI_COUNTRY_PARAMS
FASTCGI_ASN_PARAMS
FASTCGI_CITY_PARAMS

    include fastcgi_params;
  }

  location ~ /\.(?!well-known).* {
    deny all;
  }
}
EOF

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_COUNTRY_MMDB", fs.readFileSync("'$MAXMIND_COUNTRY_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_COUNTRY_PARAMS", fs.readFileSync("'$FASTCGI_COUNTRY_PARAMS_TEMPLATE'").toString()))'

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_ASN_MMDB", fs.readFileSync("'$MAXMIND_ASN_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_ASN_PARAMS", fs.readFileSync("'$FASTCGI_ASN_PARAMS_TEMPLATE'").toString()))'

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_CITY_MMDB", fs.readFileSync("'$MAXMIND_CITY_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_CITY_PARAMS", fs.readFileSync("'$FASTCGI_CITY_PARAMS_TEMPLATE'").toString()))'

sed -i 's/LARAVEL_NGINX_HTTP_PORT/'$LARAVEL_HTTP_PORT'/' $NGINX_DEFAULT_VIRTUAL_HOST
sed -i 's/LARAVEL_NGINX_ROOT/'$LARAVEL_PUBLIC_PATH'/' $NGINX_DEFAULT_VIRTUAL_HOST

# Install PDO PHP extension on docker alpine
# https://github.com/docker-library/php/issues/465
docker-php-ext-install pdo pdo_mysql

# Setup mysql / mariadb
# https://wiki.alpinelinux.org/wiki/MariaDB
mkdir -p $DB_DATA_PATH

/etc/init.d/mariadb setup
rc-service mariadb start

# Creating default user and database
mysql -e "CREATE USER ${DB_USER}@${DB_HOST} IDENTIFIED BY '${DB_PASS}';"
mysql -e "CREATE DATABASE ${DB_NAME};"
mysql -e "GRANT ALL ON ${DB_NAME}.* TO ${DB_USER}@${DB_HOST};"

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
const kill = () => process.kill(-process.pid)
cp.stdout.pipe(process.stdout)
cp.stderr.pipe(process.stderr)
cp.on('exit', kill)
process.on('SIGTERM', kill)

EOF

  # PHP-openrc service
  cat <<'EOF' -> /root/php-artisan-serve.js
const child_process = require('child_process')
const cp = child_process.exec('php artisan serve --host=0.0.0.0 --port=LARAVEL_HTTP_PORT', {cwd: 'PWD'})
const kill = () => process.kill(-process.pid)
cp.stdout.pipe(process.stdout)
cp.stderr.pipe(process.stderr)
cp.on('exit', kill)
process.on('SIGTERM', kill)

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
  sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env

  # Perform artisan optimizations
  composer install --optimize-autoloader --no-dev
  php artisan optimize:clear
  php artisan optimize

  # Nginx user requires write access
  chmod -R o+w storage

  # Start PHP-FPM
  rc-service php-fpm7 start

  # Start the prod server
  rc-service nginx start

  # Set these services to the default runlevel
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
