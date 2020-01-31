#!/bin/bash
set -x

# LARAVEL_HTTP_PORT=TCP_PORT
LARAVEL_HTTP_PORT=59314

# LARAVEL_SERVER_ENV=dev|production
LARAVEL_SERVER_ENV=dev

localdir="$(dirname "$0")"
if [ "$localdir" == "." ]
then
  localdir=""
fi

dockerdir="$(pwd)/$localdir"
blogdir="$(dirname "$dockerdir")"

$dockerdir/remove-container.sh

sudo docker run \
  -d \
  -e LARAVEL_HTTP_PORT=$LARAVEL_HTTP_PORT \
  -e LARAVEL_SERVER_ENV=$LARAVEL_SERVER_ENV \
  -v $(pwd):/app/ \
  -p $LARAVEL_HTTP_PORT:$LARAVEL_HTTP_PORT \
  -w /app/ \
  --restart always \
  --name php-laravel-survey \
  php:7-alpine \
  /app/docker/entrypoint.sh

sudo docker logs -f --tail 10 php-laravel-survey

