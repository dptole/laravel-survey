#!/bin/bash
set -x

LARAVEL_HTTP_PORT=59314

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
  -v $(pwd):/app/ \
  -p $LARAVEL_HTTP_PORT:$LARAVEL_HTTP_PORT \
  -w /app/ \
  --restart always \
  --name php-laravel-survey \
  php:7-alpine \
  /app/docker/entrypoint.sh

sudo docker logs -f --tail 10 php-laravel-survey

