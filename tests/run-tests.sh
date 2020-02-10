#!/bin/bash
set -x

[ -e vendor/bin/phpunit ] ||
( echo 'PHPUnit is not installed' &&
  exit 1
)

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

# Temporary sqlite database
touch storage/testing.sqlite ||
( echo 'Unable to create storage/testing.sqlite' &&
  exit 2
)

# Perform migration using the storage/testing.sqlite file
php artisan migrate --env=testing --database=sqlite_testing --force

# Clear cache to avoid issues with the testing
php artisan config:clear ||
( echo 'Error removing cache' &&
  exit 3
)

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

  # Recreate the cache using the .env file
  php artisan config:cache ||
  ( echo 'Error creating cache' &&
    exit 4
  )

elif grep APP_ENV=testing .env &> /dev/null
then
  # Remove the .env file because its made for testing
  rm .env
fi

# Clean up
rm storage/testing.sqlite ||
( echo 'Unable to remove storage/testing.sqlite' &&
  exit 5
)

exit $(cat /tmp/phpunit-testing-error-code)
