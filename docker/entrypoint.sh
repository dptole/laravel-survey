#!/bin/sh
set -x

# This file contains the list of commands necessary to setup the blog
# These commands are gonna be running inside the elm-mini-blog container

# Setup functions

city_log_format() {
  cat <<'EOF' -> $1

log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  '$status $body_bytes_sent "$http_referer" '
  '"$http_user_agent" "$http_x_forwarded_for" '
  'COUNTRY="$HEADER_geoip2_data_country_name_en" CITY="$HEADER_geoip2_data_city_name_en" SUBDIV0="$HEADER_geoip2_data_subdivisions_name_en" '
  'LAT="$HEADER_geoip2_data_location_latitude" LONG="$HEADER_geoip2_data_location_longitude" TZ="$HEADER_geoip2_data_location_time_zone" '
  'SUBDIVISO="$HEADER_geoip2_data_subdivisions_iso_code" POSTAL="$HEADER_geoip2_data_postal_code"';

access_log /var/log/nginx/access.log main;

EOF
}

city_asn_log_format() {
  cat <<'EOF' -> $1

log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  '$status $body_bytes_sent "$http_referer" '
  '"$http_user_agent" "$http_x_forwarded_for" '
  'COUNTRY="$HEADER_geoip2_data_country_name_en" COUNTRYCODE="$HEADER_geoip2_data_country_code" CITY="$HEADER_geoip2_data_city_name_en" '
  'SUBDIV0="$HEADER_geoip2_data_subdivisions_name_en" '
  'LAT="$HEADER_geoip2_data_location_latitude" LONG="$HEADER_geoip2_data_location_longitude" TZ="$HEADER_geoip2_data_location_time_zone" '
  'SUBDIVISO="$HEADER_geoip2_data_subdivisions_iso_code" POSTAL="$HEADER_geoip2_data_postal_code" '
  'ASNCODE="$HEADER_geoip2_data_asn_code" ASNNAME="$HEADER_geoip2_data_asn_name"';

access_log /var/log/nginx/access.log main;

EOF
}

country_log_format() {
  cat <<'EOF' -> $1

log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  '$status $body_bytes_sent "$http_referer" '
  '"$http_user_agent" "$http_x_forwarded_for" '
  'COUNTRY="$HEADER_geoip2_data_country_name_en" COUNTRYCODE="$HEADER_geoip2_data_country_code"';

access_log /var/log/nginx/access.log main;

EOF
}

country_asn_log_format() {
  cat <<'EOF' -> $1

log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  '$status $body_bytes_sent "$http_referer" '
  '"$http_user_agent" "$http_x_forwarded_for" '
  'COUNTRY="$HEADER_geoip2_data_country_name_en" COUNTRYCODE="$HEADER_geoip2_data_country_code" '
  'ASNCODE="$HEADER_geoip2_data_asn_code" ASNNAME="$HEADER_geoip2_data_asn_name"';

access_log /var/log/nginx/access.log main;

EOF
}

asn_log_format() {
  cat <<'EOF' -> $1

log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  '$status $body_bytes_sent "$http_referer" '
  '"$http_user_agent" "$http_x_forwarded_for" '
  'ASNCODE="$HEADER_geoip2_data_asn_code" ASNNAME="$HEADER_geoip2_data_asn_name"';

access_log /var/log/nginx/access.log main;

EOF
}

create_maxmind_country_fastcgi_template() {
  cat <<'EOF' -> $1
    fastcgi_param MM_IP_COUNTRY_CODE            $IP_geoip2_data_country_code;
    fastcgi_param MM_IP_EN_COUNTRY_NAME         $IP_geoip2_data_country_name_en;
    fastcgi_param MM_IP_EN_CONTINENT_NAME       $IP_geoip2_data_continent_name_en;

    fastcgi_param MM_HEADER_COUNTRY_CODE        $HEADER_geoip2_data_country_code;
    fastcgi_param MM_HEADER_EN_COUNTRY_NAME     $HEADER_geoip2_data_country_name_en;
    fastcgi_param MM_HEADER_EN_CONTINENT_NAME   $HEADER_geoip2_data_continent_name_en;

EOF
}

create_maxmind_asn_fastcgi_template() {
  cat <<'EOF' -> $1
    fastcgi_param MM_IP_ASN_CODE      $IP_geoip2_data_asn_code;
    fastcgi_param MM_IP_ASN_NAME      $IP_geoip2_data_asn_name;

    fastcgi_param MM_HEADER_ASN_CODE  $HEADER_geoip2_data_asn_code;
    fastcgi_param MM_HEADER_ASN_NAME  $HEADER_geoip2_data_asn_name;

EOF
}

create_maxmind_city_fastcgi_template() {
  cat <<'EOF' -> $1
    fastcgi_param MM_IP_COUNTRY_CODE              $IP_geoip2_data_country_code;
    fastcgi_param MM_IP_EN_COUNTRY_NAME           $IP_geoip2_data_country_name_en;
    fastcgi_param MM_IP_EN_CITY_NAME              $IP_geoip2_data_city_name_en;
    fastcgi_param MM_IP_POSTAL_CODE               $IP_geoip2_data_postal_code;
    fastcgi_param MM_IP_EN_SUBDIVISIONS_ISO_CODE  $IP_geoip2_data_subdivisions_iso_code;
    fastcgi_param MM_IP_EN_SUBDIVISIONS_NAME      $IP_geoip2_data_subdivisions_name_en;
    fastcgi_param MM_IP_LOCATION_LATITUDE         $IP_geoip2_data_location_latitude;
    fastcgi_param MM_IP_LOCATION_LONGITUDE        $IP_geoip2_data_location_longitude;
    fastcgi_param MM_IP_LOCATION_TIME_ZONE        $IP_geoip2_data_location_time_zone;
    fastcgi_param MM_IP_EN_CONTINENT_NAME         $IP_geoip2_data_continent_name_en;

    fastcgi_param MM_HEADER_COUNTRY_CODE              $HEADER_geoip2_data_country_code;
    fastcgi_param MM_HEADER_EN_COUNTRY_NAME           $HEADER_geoip2_data_country_name_en;
    fastcgi_param MM_HEADER_EN_CITY_NAME              $HEADER_geoip2_data_city_name_en;
    fastcgi_param MM_HEADER_POSTAL_CODE               $HEADER_geoip2_data_postal_code;
    fastcgi_param MM_HEADER_EN_SUBDIVISIONS_ISO_CODE  $HEADER_geoip2_data_subdivisions_iso_code;
    fastcgi_param MM_HEADER_EN_SUBDIVISIONS_NAME      $HEADER_geoip2_data_subdivisions_name_en;
    fastcgi_param MM_HEADER_LOCATION_LATITUDE         $HEADER_geoip2_data_location_latitude;
    fastcgi_param MM_HEADER_LOCATION_LONGITUDE        $HEADER_geoip2_data_location_longitude;
    fastcgi_param MM_HEADER_LOCATION_TIME_ZONE        $HEADER_geoip2_data_location_time_zone;
    fastcgi_param MM_HEADER_EN_CONTINENT_NAME         $HEADER_geoip2_data_continent_name_en;

EOF
}

create_maxmind_country_template() {
  cat <<'EOF' -> $1
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_country_code source=$remote_addr country iso_code;
  $IP_geoip2_data_country_name_en source=$remote_addr country names en;
  $IP_geoip2_data_continent_name_en source=$remote_addr continent name en;

  $HEADER_geoip2_data_country_code source=$http_x_forwarded_for country iso_code;
  $HEADER_geoip2_data_country_name_en source=$http_x_forwarded_for country names en;
  $HEADER_geoip2_data_continent_name_en source=$http_x_forwarded_for continent name en;
}

EOF
}

create_maxmind_asn_template() {
  cat <<'EOF' -> $1
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_asn_code source=$remote_addr autonomous_system_number;
  $IP_geoip2_data_asn_name source=$remote_addr autonomous_system_organization;

  $HEADER_geoip2_data_asn_code source=$http_x_forwarded_for autonomous_system_number;
  $HEADER_geoip2_data_asn_name source=$http_x_forwarded_for autonomous_system_organization;
}

EOF
}

create_maxmind_city_template() {
  cat <<'EOF' -> $1
geoip2 MMDB {
  auto_reload 5m;

  $IP_geoip2_data_country_code source=$remote_addr country iso_code;
  $IP_geoip2_data_country_name_en source=$remote_addr country names en;
  $IP_geoip2_data_city_name_en source=$remote_addr city names en;
  $IP_geoip2_data_postal_code source=$remote_addr postal code;
  $IP_geoip2_data_subdivisions_iso_code source=$remote_addr subdivisions 0 iso_code;
  $IP_geoip2_data_subdivisions_name_en source=$remote_addr subdivisions 0 names en;
  $IP_geoip2_data_location_latitude source=$remote_addr location latitude;
  $IP_geoip2_data_location_longitude source=$remote_addr location longitude;
  $IP_geoip2_data_location_time_zone source=$remote_addr location time_zone;
  $IP_geoip2_data_continent_name_en source=$remote_addr continent name en;

  $HEADER_geoip2_data_country_code source=$http_x_forwarded_for country iso_code;
  $HEADER_geoip2_data_country_name_en source=$http_x_forwarded_for country names en;
  $HEADER_geoip2_data_city_name_en source=$http_x_forwarded_for city names en;
  $HEADER_geoip2_data_postal_code source=$http_x_forwarded_for postal code;
  $HEADER_geoip2_data_subdivisions_iso_code source=$http_x_forwarded_for subdivisions 0 iso_code;
  $HEADER_geoip2_data_subdivisions_name_en source=$http_x_forwarded_for subdivisions 0 names en;
  $HEADER_geoip2_data_location_latitude source=$http_x_forwarded_for location latitude;
  $HEADER_geoip2_data_location_longitude source=$http_x_forwarded_for location longitude;
  $HEADER_geoip2_data_location_time_zone source=$http_x_forwarded_for location time_zone;
  $HEADER_geoip2_data_continent_name_en source=$http_x_forwarded_for continent name en;
}

EOF
}

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

# Install alpine packages not related to PHP
apk add curl bash vim nodejs npm mariadb mariadb-client openrc

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

export LARAVEL_HTTP_PORT=$LARAVEL_HTTP_PORT
export LARAVEL_SERVER_ENV=$LARAVEL_SERVER_ENV
export LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY
export LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY
export LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY
export LARAVEL_WORKDIR=$LARAVEL_WORKDIR
export LARAVEL_MAXMIND_COUNTRY_MMDB=$LARAVEL_MAXMIND_COUNTRY_MMDB
export LARAVEL_MAXMIND_ASN_MMDB=$LARAVEL_MAXMIND_ASN_MMDB
export LARAVEL_MAXMIND_CITY_MMDB=$LARAVEL_MAXMIND_CITY_MMDB

export LARAVEL_PUBLIC_PATH=$LARAVEL_WORKDIR/public

EOF

# Create .env file
cp .env.example .env
echo "LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" >> .env
echo "LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY" >> .env
echo "LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY" >> .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=localhost/' .env
# https://stackoverflow.com/a/28905017
grep DB_SOCKET .env || sed -i 's/DB_CONNECTION/DB_SOCKET=\/run\/mysqld\/mysqld.sock\nDB_CONNECTION/' .env

# Copy variables from .env into ~/.bashrc
grep DB_USERNAME .env | sed 's/DB_USERNAME/export DB_USER/' | tee -a ~/.bashrc
grep DB_PASSWORD .env | sed 's/DB_PASSWORD/export DB_PASS/' | tee -a ~/.bashrc
grep DB_DATABASE .env | sed 's/DB_DATABASE/export DB_NAME/' | tee -a ~/.bashrc
grep DB_HOST .env | sed 's/DB_HOST/export DB_HOST/' | tee -a ~/.bashrc
grep BROTLI_STATIC_ON .env | sed 's/BROTLI_STATIC/export BROTLI_STATIC/' | tee -a ~/.bashrc
grep GZIP_STATIC_ON .env | sed 's/GZIP_STATIC/export BROTLI_STATIC/' | tee -a ~/.bashrc

# Export the env variables
. ~/.bashrc

# Boot openrc
openrc boot

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

# Maxmind GeoIP2 Nginx module
MAXMIND_COUNTRY_TEMPLATE="$(get_maxmind_folder)/maxmind-country-template"
FASTCGI_COUNTRY_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-country-params-template"
touch "$MAXMIND_COUNTRY_TEMPLATE"
touch "$FASTCGI_COUNTRY_PARAMS_TEMPLATE"
if [ -e "$LARAVEL_MAXMIND_COUNTRY_MMDB" ]
then
  create_maxmind_country_template $MAXMIND_COUNTRY_TEMPLATE
  sed -i "s/MMDB/${LARAVEL_MAXMIND_COUNTRY_MMDB//\//\\/}/" $MAXMIND_COUNTRY_TEMPLATE
  create_maxmind_country_fastcgi_template $FASTCGI_COUNTRY_PARAMS_TEMPLATE

elif [ "$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" != "false" ]
then
  MAXMIND_COUNTRY_MMDB="$(get_maxmind_db GeoLite2-Country $LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY)"

  if [ -e "$MAXMIND_COUNTRY_MMDB" ]
  then
    create_maxmind_country_template $MAXMIND_COUNTRY_TEMPLATE
    sed -i "s/MMDB/${MAXMIND_COUNTRY_MMDB//\//\\/}/" $MAXMIND_COUNTRY_TEMPLATE
    create_maxmind_country_fastcgi_template $FASTCGI_COUNTRY_PARAMS_TEMPLATE
  fi
fi

MAXMIND_ASN_TEMPLATE="$(get_maxmind_folder)/maxmind-asn-template"
FASTCGI_ASN_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-asn-params-template"
touch "$MAXMIND_ASN_TEMPLATE"
touch "$FASTCGI_ASN_PARAMS_TEMPLATE"
if [ -e "$LARAVEL_MAXMIND_ASN_MMDB" ]
then
  create_maxmind_asn_template $MAXMIND_ASN_TEMPLATE
  sed -i "s/MMDB/${LARAVEL_MAXMIND_ASN_MMDB//\//\\/}/" $MAXMIND_ASN_TEMPLATE
  create_maxmind_asn_fastcgi_template $FASTCGI_ASN_PARAMS_TEMPLATE

elif [ "$LARAVEL_MAXMIND_ASN_LICENSE_KEY" != "false" ]
then
  MAXMIND_ASN_MMDB="$(get_maxmind_db GeoLite2-ASN $LARAVEL_MAXMIND_ASN_LICENSE_KEY)"

  if [ -e "$MAXMIND_ASN_MMDB" ]
  then
    create_maxmind_asn_template $MAXMIND_ASN_TEMPLATE
    sed -i "s/MMDB/${MAXMIND_ASN_MMDB//\//\\/}/" $MAXMIND_ASN_TEMPLATE
    create_maxmind_asn_fastcgi_template $FASTCGI_ASN_PARAMS_TEMPLATE
  fi
fi

MAXMIND_CITY_TEMPLATE="$(get_maxmind_folder)/maxmind-city-template"
FASTCGI_CITY_PARAMS_TEMPLATE="$(get_maxmind_folder)/fastcgi-city-params-template"
touch "$MAXMIND_CITY_TEMPLATE"
touch "$FASTCGI_CITY_PARAMS_TEMPLATE"
if [ -e "$LARAVEL_MAXMIND_CITY_MMDB" ]
then
  create_maxmind_city_template $MAXMIND_CITY_TEMPLATE
  sed -i "s/MMDB/${LARAVEL_MAXMIND_CITY_MMDB//\//\\/}/" $MAXMIND_CITY_TEMPLATE
  create_maxmind_city_fastcgi_template $FASTCGI_CITY_PARAMS_TEMPLATE

elif [ "$LARAVEL_MAXMIND_CITY_LICENSE_KEY" != "false" ]
then
  MAXMIND_CITY_MMDB="$(get_maxmind_db GeoLite2-City $LARAVEL_MAXMIND_CITY_LICENSE_KEY)"

  if [ -e "$MAXMIND_CITY_MMDB" ]
  then
    create_maxmind_city_template $MAXMIND_CITY_TEMPLATE
    sed -i "s/MMDB/${MAXMIND_CITY_MMDB//\//\\/}/" $MAXMIND_CITY_TEMPLATE
    create_maxmind_city_fastcgi_template $FASTCGI_CITY_PARAMS_TEMPLATE
  fi
fi

# Don't expose PHP
# https://stackoverflow.com/a/2661807
sed -i 's/expose_php = on/expose_php = off/i' /etc/php7/php.ini

# Don't buffer responses
sed -i 's/output_buffering = 4096/output_buffering = off/i' /etc/php7/php.ini

# Maximum amount of time each script may spend parsing request data. It's a good
# idea to limit this time on productions servers in order to eliminate unexpectedly
# long running scripts
sed -i 's/max_input_time = 60/max_input_time = 5/i' /etc/php7/php.ini

# Setup Nginx installation
NGINX_DEFAULT_CONF="/etc/nginx/nginx.conf"
NGINX_DEFAULT_VIRTUAL_HOST="/etc/nginx/conf.d/default.conf"

addgroup -S nginx
adduser -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx

export GNUPGHOME="$(mktemp -d)"

apk add autoconf libtool automake git g++ cmake gcc libc-dev make openssl-dev pcre-dev zlib-dev linux-headers gnupg1 libxslt-dev gd-dev perl-dev geoip-dev libmaxminddb-dev

mkdir -p /etc/nginx/conf.d
mkdir -p /var/log/nginx/
mkdir -p /var/cache/nginx/client_temp
mkdir -p /var/cache/nginx/proxy_temp
mkdir -p /var/cache/nginx/uwsgi_temp
mkdir -p /var/cache/nginx/scgi_temp
mkdir -p /usr/lib/nginx/modules
mkdir -p /usr/lib/nginx/git-modules/ngx_brotli
mkdir -p /usr/lib/nginx/git-modules/ngx_geoip2

chown -R nginx.nginx /etc/nginx/
chown -R nginx.nginx /var/log/nginx/
chown -R nginx.nginx /var/cache/nginx/
chown -R nginx.nginx /usr/lib/nginx/

git clone --recursive https://github.com/google/ngx_brotli.git /usr/lib/nginx/git-modules/ngx_brotli
cd /usr/lib/nginx/git-modules/ngx_brotli
# Try to make it stable
git reset --hard e505dce68acc190cc5a1e780a3b0275e39f160ca

git clone --recursive https://github.com/leev/ngx_http_geoip2_module.git /usr/lib/nginx/git-modules/ngx_geoip2
cd /usr/lib/nginx/git-modules/ngx_geoip2
# Try to make it stable
git reset --hard 1cabd8a1f68ea3998f94e9f3504431970f848fbf

# Download nginx binary and compile its source code
cd $HOME
wget https://nginx.org/download/nginx-1.16.1.tar.gz
tar xzf nginx-1.16.1.tar.gz
rm nginx-1.16.1.tar.gz
cd nginx-1.16.1

# Completely removes the annoying "server: nginx" header
# Without requiring you to download another module just to remove 1 line
# https://stackoverflow.com/questions/246227/how-do-you-change-the-server-header-returned-by-nginx
sed -i 's/static u_char ngx_http_server_string\[\] = "Server: nginx" CRLF;/static u_char ngx_http_server_string[] = "";/' src/http/ngx_http_header_filter_module.c

CONFIG="\
--prefix=/etc/nginx \
--sbin-path=/usr/sbin/nginx \
--modules-path=/usr/lib/nginx/modules \
--conf-path=/etc/nginx/nginx.conf \
--error-log-path=/var/log/nginx/error.log \
--http-log-path=/var/log/nginx/access.log \
--pid-path=/var/run/nginx.pid \
--lock-path=/var/run/nginx.lock \
--http-client-body-temp-path=/var/cache/nginx/client_temp \
--http-proxy-temp-path=/var/cache/nginx/proxy_temp \
--http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
--http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
--http-scgi-temp-path=/var/cache/nginx/scgi_temp \
--add-module=/usr/lib/nginx/git-modules/ngx_brotli \
--add-module=/usr/lib/nginx/git-modules/ngx_geoip2 \
--user=nginx \
--group=nginx \
--with-http_ssl_module \
--with-http_realip_module \
--with-http_addition_module \
--with-http_sub_module \
--with-http_dav_module \
--with-http_flv_module \
--with-http_mp4_module \
--with-http_gunzip_module \
--with-http_gzip_static_module \
--with-http_random_index_module \
--with-http_secure_link_module \
--with-http_stub_status_module \
--with-http_auth_request_module \
--with-http_xslt_module=dynamic \
--with-http_image_filter_module=dynamic \
--with-http_geoip_module=dynamic \
--with-http_perl_module=dynamic \
--with-threads \
--with-stream_geoip_module=dynamic \
--with-stream \
--with-stream_ssl_module \
--with-stream_ssl_preread_module \
--with-stream_realip_module \
--with-http_slice_module \
--with-mail \
--with-mail_ssl_module \
--with-compat \
--with-file-aio \
--with-http_v2_module"

echo "Compiling nginx..."
./configure $CONFIG &>/dev/null
make -j$(getconf _NPROCESSORS_ONLN) &>/dev/null
make install &>/dev/null

cd $LARAVEL_WORKDIR

cat <<'EOF' -> $NGINX_DEFAULT_CONF
# /etc/nginx/nginx.conf

user nginx;

# Set number of worker processes automatically based on number of CPU cores.
worker_processes auto;

# Enables the use of JIT for regular expressions to speed-up their processing.
pcre_jit on;

# Includes files with directives to load dynamic modules.
include /etc/nginx/modules/*.conf;

events {
  # The maximum number of simultaneous connections that can be opened by
  # a worker process.
  worker_connections 1024;
}

http {
  # Includes mapping of file name extensions to MIME types of responses
  # and defines the default type.
  include /etc/nginx/mime.types;
  default_type application/octet-stream;

  # Name servers used to resolve names of upstream servers into addresses.
  # It's also needed when using tcpsocket and udpsocket in Lua modules.
  #resolver 208.67.222.222 208.67.220.220;

  # Don't tell nginx version to clients.
  server_tokens off;

  # Specifies the maximum accepted body size of a client request, as
  # indicated by the request header Content-Length. If the stated content
  # length is greater than this size, then the client receives the HTTP
  # error code 413. Set to 0 to disable.
  client_max_body_size 1m;

  # Timeout for keep-alive connections. Server will close connections after
  # this time.
  keepalive_timeout 65;

  # Sendfile copies data between one FD and other from within the kernel,
  # which is more efficient than read() + write().
  sendfile on;

  # Don't buffer data-sends (disable Nagle algorithm).
  # Good for sending frequent small bursts of data in real time.
  tcp_nodelay on;

  # Causes nginx to attempt to send its HTTP response head in one packet,
  # instead of using partial frames.
  #tcp_nopush on;

  # Path of the file with Diffie-Hellman parameters for EDH ciphers.
  #ssl_dhparam /etc/ssl/nginx/dh2048.pem;

  # Specifies that our cipher suits should be preferred over client ciphers.
  ssl_prefer_server_ciphers on;

  # Enables a shared SSL cache with size that can hold around 8000 sessions.
  ssl_session_cache shared:SSL:2m;

  # Enable gzipping of responses.
  gzip on;

  # Set the Vary HTTP header as defined in the RFC 2616.
  gzip_vary on;

  # Specifies the main log format.
  log_format main '$remote_addr - $remote_user [$time_local] "$request" '
  		'$status $body_bytes_sent "$http_referer" '
  		'"$http_user_agent" "$http_x_forwarded_for"';

  # Sets the path, format, and configuration for a buffered log write.
  access_log /var/log/nginx/access.log main;

  # Includes virtual hosts configs.
  include /etc/nginx/conf.d/*.conf;
}
EOF


# Create nginx default configuration file
cat <<'EOF' -> $NGINX_DEFAULT_VIRTUAL_HOST
# NGINX_DEFAULT_VIRTUAL_HOST

LOG_FORMAT

MAXMIND_COUNTRY_MMDB
MAXMIND_ASN_MMDB
MAXMIND_CITY_MMDB

# Configures default error logger.
error_log /var/log/nginx/error.log debug;

variables_hash_bucket_size 128;
variables_hash_max_size 2048;

server {
  listen LARAVEL_NGINX_HTTP_PORT;
  server_name localhost;
  root LARAVEL_NGINX_ROOT;

  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-XSS-Protection "1; mode=block";
  add_header X-Content-Type-Options "nosniff";

  gzip on;
  gzip_vary on;
  GZIP_STATIC
  gzip_comp_level 9;
  gzip_types text/plain text/css text/javascript text/x-javascript application/javascript application/x-javascript;

  brotli on;
  BROTLI_STATIC
  brotli_comp_level 11;
  brotli_types text/plain text/css text/javascript text/x-javascript application/javascript application/x-javascript;

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

    # Just in case
    # https://github.com/wodby/docker4drupal/issues/335
    # https://serverfault.com/a/938042
    fastcgi_pass_header "x-accel-buffering";

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

sed -i 's/NGINX_DEFAULT_VIRTUAL_HOST/'${NGINX_DEFAULT_VIRTUAL_HOST//\//\\/}'/' $NGINX_DEFAULT_VIRTUAL_HOST

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_COUNTRY_MMDB", fs.readFileSync("'$MAXMIND_COUNTRY_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_COUNTRY_PARAMS", fs.readFileSync("'$FASTCGI_COUNTRY_PARAMS_TEMPLATE'").toString()))'

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_ASN_MMDB", fs.readFileSync("'$MAXMIND_ASN_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_ASN_PARAMS", fs.readFileSync("'$FASTCGI_ASN_PARAMS_TEMPLATE'").toString()))'

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("MAXMIND_CITY_MMDB", fs.readFileSync("'$MAXMIND_CITY_TEMPLATE'").toString()))'
node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("FASTCGI_CITY_PARAMS", fs.readFileSync("'$FASTCGI_CITY_PARAMS_TEMPLATE'").toString()))'

LOG_FORMAT="$(mktemp)"

if [ ! -z "$FASTCGI_CITY_PARAMS_TEMPLATE" ]
then
  if [ ! -z "$FASTCGI_ASN_PARAMS_TEMPLATE" ]
  then
    city_asn_log_format $LOG_FORMAT

  else
    city_log_format $LOG_FORMAT

  fi
elif [ ! -z "$FASTCGI_COUNTRY_PARAMS_TEMPLATE" ]
then
  if [ ! -z "$FASTCGI_ASN_PARAMS_TEMPLATE" ]
  then
    country_asn_log_format $LOG_FORMAT

  else
    country_log_format $LOG_FORMAT

  fi
elif [ ! -z "$FASTCGI_ASN_PARAMS_TEMPLATE" ]
then
  asn_log_format $LOG_FORMAT

fi

node -e 'fs.writeFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'", fs.readFileSync("'$NGINX_DEFAULT_VIRTUAL_HOST'").toString().replace("LOG_FORMAT", fs.readFileSync("'$LOG_FORMAT'").toString()))'
rm $LOG_FORMAT

SAFE_LARAVEL_PUBLIC_PATH=${LARAVEL_PUBLIC_PATH//\//\\/}

sed -i 's/LARAVEL_NGINX_HTTP_PORT/'$LARAVEL_HTTP_PORT'/' $NGINX_DEFAULT_VIRTUAL_HOST
sed -i 's/LARAVEL_NGINX_ROOT/'$SAFE_LARAVEL_PUBLIC_PATH'/' $NGINX_DEFAULT_VIRTUAL_HOST

if [ "$GZIP_STATIC_ON" == "true" ]
then
  sed -i 's/GZIP_STATIC/gzip_static on;/' $NGINX_DEFAULT_VIRTUAL_HOST
else
  sed -i 's/GZIP_STATIC//' $NGINX_DEFAULT_VIRTUAL_HOST
fi

if [ "$BROTLI_STATIC_ON" == "true" ]
then
  sed -i 's/BROTLI_STATIC/brotli_static on;/' $NGINX_DEFAULT_VIRTUAL_HOST
else
  sed -i 's/BROTLI_STATIC//' $NGINX_DEFAULT_VIRTUAL_HOST
fi

# Create the main nginx config file
cat <<'EOF' -> $NGINX_DEFAULT_CONF
# /etc/nginx/nginx.conf

user nginx;

# Set number of worker processes automatically based on number of CPU cores.
worker_processes auto;

# Enables the use of JIT for regular expressions to speed-up their processing.
pcre_jit on;

# Configures default error logger.
error_log /var/log/nginx/error.log warn;

# Includes files with directives to load dynamic modules.
include /etc/nginx/modules/*.conf;

events {
  # The maximum number of simultaneous connections that can be opened by
  # a worker process.
  worker_connections 1024;
}

http {
  # Includes mapping of file name extensions to MIME types of responses
  # and defines the default type.
  include /etc/nginx/mime.types;
  default_type application/octet-stream;

  # Name servers used to resolve names of upstream servers into addresses.
  # It's also needed when using tcpsocket and udpsocket in Lua modules.
  #resolver 208.67.222.222 208.67.220.220;

  # Don't tell nginx version to clients.
  server_tokens off;

  # Specifies the maximum accepted body size of a client request, as
  # indicated by the request header Content-Length. If the stated content
  # length is greater than this size, then the client receives the HTTP
  # error code 413. Set to 0 to disable.
  client_max_body_size 1m;

  # Timeout for keep-alive connections. Server will close connections after
  # this time.
  keepalive_timeout 65;

  # Sendfile copies data between one FD and other from within the kernel,
  # which is more efficient than read() + write().
  sendfile on;

  # Don't buffer data-sends (disable Nagle algorithm).
  # Good for sending frequent small bursts of data in real time.
  tcp_nodelay on;

  # Causes nginx to attempt to send its HTTP response head in one packet,
  # instead of using partial frames.
  #tcp_nopush on;

  # Path of the file with Diffie-Hellman parameters for EDH ciphers.
  #ssl_dhparam /etc/ssl/nginx/dh2048.pem;

  # Specifies that our cipher suits should be preferred over client ciphers.
  ssl_prefer_server_ciphers on;

  # Enables a shared SSL cache with size that can hold around 8000 sessions.
  ssl_session_cache shared:SSL:2m;

  # Includes virtual hosts configs.
  include /etc/nginx/conf.d/*.conf;
}
EOF

cat <<'EOF' -> /etc/init.d/nginx
#!/sbin/openrc-run

description="Nginx http and reverse proxy server"
extra_commands="checkconfig"
extra_started_commands="reload reopen upgrade"

cfgfile=${cfgfile-/etc/nginx/nginx.conf}
pidfile=/var/run/nginx.pid
command=/usr/sbin/nginx
command_args="-c $cfgfile"
required_files="$cfgfile"

depend() {
  need net
  use dns logger netmount
}

start_pre() {
  $command $command_args -t -q
}

checkconfig() {
  ebegin "Checking $RC_SVCNAME configuration"
  start_pre
  eend $?
}

reload() {
  ebegin "Reloading $RC_SVCNAME configuration"
  start_pre && start-stop-daemon --signal HUP --pidfile $pidfile
  eend $?
}

reopen() {
  ebegin "Reopening $RC_SVCNAME log files"
  start-stop-daemon --signal USR1 --pidfile $pidfile
  eend $?
}

upgrade() {
  start_pre || return 1

  ebegin "Upgrading $RC_SVCNAME binary"

  einfo "Sending USR2 to old binary"
  start-stop-daemon --signal USR2 --pidfile $pidfile

  einfo "Sleeping 3 seconds before pid-files checking"
  sleep 3

  if [ ! -f $pidfile.oldbin ]; then
  	eerror "File with old pid ($pidfile.oldbin) not found"
  	return 1
  fi

  if [ ! -f $pidfile ]; then
  	eerror "New binary failed to start"
  	return 1
  fi

  einfo "Sleeping 3 seconds before WINCH"
  sleep 3 ; start-stop-daemon --signal 28 --pidfile $pidfile.oldbin

  einfo "Sending QUIT to old binary"
  start-stop-daemon --signal QUIT --pidfile $pidfile.oldbin

  einfo "Upgrade completed"

  eend $? "Upgrade failed"
}

EOF

# Allow the service to be executed
chmod +x /etc/init.d/nginx

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

  chmod +x /etc/init.d/npm-run-watch

  sed -i 's/PWD/'$PWD_SAFE'/' /root/npm-run-watch.js

  # Watch for changes in the js/css files to rebundle
  rc-service npm-run-watch start

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

fi

# Start PHP-FPM
rc-service php-fpm7 start

# Start the prod server
rc-service nginx start

# Set these services to the default runlevel
rc-update add php-fpm7 default
rc-update add nginx default

# Install and enable Xdebug for code coverage with coveralls
pecl install xdebug
docker-php-ext-enable xdebug

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
