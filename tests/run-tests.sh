#!/bin/bash
set -x

# CREATE_DB=<true|false>
CREATE_DB=true

# CLEAR_CONFIG_CACHE=<true|false>
CLEAR_CONFIG_CACHE=true

if [ ! -e vendor/bin/phpunit ]
then
  echo 'PHPUnit is not installed' &&
  exit 1
fi

if [ ! -e .env ]
then
  if [ -e /tmp/.env.not.testing ]
  then
    # Create a .env file from the previous backup
    cp /tmp/.env.not.testing .env
  else
    # There was no .env file and neither its backup
    # Copy from .env.testing
    cp .env.testing .env
  fi
fi

# Backup the .env file if its not from a testing environment
( ! grep APP_ENV=testing .env ) &&
cp .env /tmp/.env.not.testing

# Create .env file for the testing
cp .env.testing .env

if [ "$CREATE_DB" == "true" ]
then
  # Temporary sqlite database
  if ! touch storage/testing.sqlite
  then
    echo 'Unable to create storage/testing.sqlite' &&
    exit 2
  fi

  # Perform migration using the storage/testing.sqlite file
  php artisan migrate --env=testing --database=sqlite_testing --force
fi

# https://laravel.com/docs/6.x/testing#environment
# The testing environment variables may be configured in the phpunit.xml
# file, but make sure to clear your configuration cache using the
# config:clear Artisan command before running your tests!
if [ "$CLEAR_CONFIG_CACHE" == "true" ]
then
  if ! php artisan config:clear
  then
    echo 'Error removing cache' &&
    exit 3
  fi
fi

# Run the tests (using the .env file & phpunit.xml)
vendor/bin/phpunit

# Store the error code from the previous command because
# clean up is necessary
echo $? > /tmp/phpunit-testing-error-code

if [ -e /tmp/.env.not.testing ]
then
  # Return the original .env file
  # There was a .env file before the tests started
  cp /tmp/.env.not.testing .env

  if [ "$CLEAR_CONFIG_CACHE" == "true" ]
  then
    # Recreate the cache using the .env file
    if ! php artisan config:cache
    then
      echo 'Error creating cache'
      exit 4
    fi
  fi

elif grep APP_ENV=testing .env &> /dev/null
then
  # Remove the .env file because its made for testing
  rm .env
fi

if [ -e storage/testing.sqlite ]
then
  # Clean up
  if ! rm storage/testing.sqlite
  then
    echo 'Unable to remove storage/testing.sqlite' &&
    exit 5
  fi
fi

exit $(cat /tmp/phpunit-testing-error-code)
