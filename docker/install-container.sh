#!/bin/bash
set -x

# LARAVEL_HTTP_PORT=TCP_PORT
LARAVEL_HTTP_PORT=59314

# LARAVEL_SERVER_ENV=dev|production
LARAVEL_SERVER_ENV=dev

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

# LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=false|<A LICENSE KEY ASSOCIATED WITH YOUR ACCOUNT>
test "$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY" ||
LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=false

# LARAVEL_MAXMIND_CITY_LICENSE_KEY=false|<A LICENSE KEY ASSOCIATED WITH YOUR ACCOUNT>
test "$LARAVEL_MAXMIND_CITY_LICENSE_KEY" ||
LARAVEL_MAXMIND_CITY_LICENSE_KEY=false

# LARAVEL_MAXMIND_ASN_LICENSE_KEY=false|<A LICENSE KEY ASSOCIATED WITH YOUR ACCOUNT>
test "$LARAVEL_MAXMIND_ASN_LICENSE_KEY" ||
LARAVEL_MAXMIND_ASN_LICENSE_KEY=false

############################
### END .env VARIABLES #####
############################

# Clean up
$dockerdir/remove-container.sh

# Install container
sudo docker run \
  -d \
  -e LARAVEL_HTTP_PORT=$LARAVEL_HTTP_PORT \
  -e LARAVEL_SERVER_ENV=$LARAVEL_SERVER_ENV \
  -e LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY=$LARAVEL_MAXMIND_COUNTRY_LICENSE_KEY \
  -e LARAVEL_MAXMIND_CITY_LICENSE_KEY=$LARAVEL_MAXMIND_CITY_LICENSE_KEY \
  -e LARAVEL_MAXMIND_ASN_LICENSE_KEY=$LARAVEL_MAXMIND_ASN_LICENSE_KEY \
  -e LARAVEL_WORKDIR=$LARAVEL_WORKDIR \
  -p $LARAVEL_HTTP_PORT:$LARAVEL_HTTP_PORT \
  -v $(pwd):$LARAVEL_WORKDIR \
  -w $LARAVEL_WORKDIR \
  --restart always \
  --name php-laravel-survey \
  php:7-alpine \
  $LARAVEL_WORKDIR/docker/entrypoint.sh

sudo docker logs -f --tail 10 php-laravel-survey

