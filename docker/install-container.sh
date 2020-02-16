#!/bin/bash
set -x

# LARAVEL_HTTP_PORT=TCP_PORT
LARAVEL_HTTP_PORT=59314

# LARAVEL_SERVER_ENV=dev|production
LARAVEL_SERVER_ENV=production

# Workdir when you access the container
LARAVEL_WORKDIR=/app

localdir="$(dirname "$0")"
if [ "$localdir" == "." ]
then
  localdir=""
fi
dockerdir="$(pwd)/$localdir"

# Load env variables from a .env file, if present
[ -e "$localdir/.env" ] && . "$localdir/.env"

############################
### BEGIN .env VARIABLES ###
############################

# Maxmind GeoIP2
# https://dev.maxmind.com/geoip/geoipupdate/
# The keys bellow will be provided to PHP via fastcgi and nginx's nginx-mod-http-geoip2 module

# Don't change these variables, they are here to provide a default value
# in case they were not set
# Create a new docker/.env file from docker/.env.example and edit their values instead
LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=${LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY-false}
LARAVEL_MAXMIND_ASN_LICENSE_KEY=${LARAVEL_MAXMIND_ASN_LICENSE_KEY-false}
LARAVEL_MAXMIND_CITY_LICENSE_KEY=${LARAVEL_MAXMIND_CITY_LICENSE_KEY-false}
GZIP_STATIC_ON=${GZIP_STATIC_ON-false}
BROTLI_STATIC_ON=${BROTLI_STATIC_ON-false}
LARAVEL_DOXYGEN_ENABLED=${LARAVEL_DOXYGEN_ENABLED-false}

if [ -e "$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" ] && [ "mmdb" == "${LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY##*.}" ]
then
  LARAVEL_MAXMIND_COUNTRY_MMDB=/root/maxmind/$(basename $LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY)
  LARAVEL_MAXMIND_COUNTRY_VOLUME="-v $LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY:$LARAVEL_MAXMIND_COUNTRY_MMDB"
fi

if [ -e "$LARAVEL_MAXMIND_ASN_LICENSE_KEY" ] && [ "mmdb" == "${LARAVEL_MAXMIND_ASN_LICENSE_KEY##*.}" ]
then
  LARAVEL_MAXMIND_ASN_MMDB=/root/maxmind/$(basename $LARAVEL_MAXMIND_ASN_LICENSE_KEY)
  LARAVEL_MAXMIND_ASN_VOLUME="-v $LARAVEL_MAXMIND_ASN_LICENSE_KEY:$LARAVEL_MAXMIND_ASN_MMDB"
fi

if [ -e "$LARAVEL_MAXMIND_CITY_LICENSE_KEY" ] && [ "mmdb" == "${LARAVEL_MAXMIND_CITY_LICENSE_KEY##*.}" ]
then
  LARAVEL_MAXMIND_CITY_MMDB=/root/maxmind/$(basename $LARAVEL_MAXMIND_CITY_LICENSE_KEY)
  LARAVEL_MAXMIND_CITY_VOLUME="-v $LARAVEL_MAXMIND_CITY_LICENSE_KEY:$LARAVEL_MAXMIND_CITY_MMDB"
fi

############################
### END .env VARIABLES #####
############################

# Clean up
$dockerdir/remove-container.sh

# Install container
sudo docker run \
  -d \
  -e UPLOAD_COVERALLS=false \
  -e LARAVEL_HTTP_PORT=$LARAVEL_HTTP_PORT \
  -e LARAVEL_SERVER_ENV=$LARAVEL_SERVER_ENV \
  -e LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY \
  -e LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY \
  -e LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY \
  -e LARAVEL_WORKDIR=$LARAVEL_WORKDIR \
  -e LARAVEL_MAXMIND_COUNTRY_MMDB=$LARAVEL_MAXMIND_COUNTRY_MMDB \
  -e LARAVEL_MAXMIND_ASN_MMDB=$LARAVEL_MAXMIND_ASN_MMDB \
  -e LARAVEL_MAXMIND_CITY_MMDB=$LARAVEL_MAXMIND_CITY_MMDB \
  -e GZIP_STATIC_ON=$GZIP_STATIC_ON \
  -e BROTLI_STATIC_ON=$BROTLI_STATIC_ON \
  -e LARAVEL_DOXYGEN_ENABLED=$LARAVEL_DOXYGEN_ENABLED \
  -p $LARAVEL_HTTP_PORT:$LARAVEL_HTTP_PORT \
  $LARAVEL_MAXMIND_COUNTRY_VOLUME \
  $LARAVEL_MAXMIND_ASN_VOLUME \
  $LARAVEL_MAXMIND_CITY_VOLUME \
  -v $(pwd):$LARAVEL_WORKDIR \
  -w $LARAVEL_WORKDIR \
  --restart always \
  --name php-laravel-survey \
  php:7-alpine \
  $LARAVEL_WORKDIR/docker/entrypoint.sh

sudo docker logs -f --tail 10 php-laravel-survey

