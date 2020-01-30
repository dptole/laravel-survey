#!/bin/sh
set -x

# TODO
# Show configuration screen in case not all variables were set [allow to disable]
# - Create function to detect if all env vars are set
# - - Return an array with the ones not set
# - - These vars work as groups, custom configuration required
# Set the prefix for the URL via the env file

# This file contains the list of commands necessary to setup the blog
# These commands are gonna be running inside the elm-mini-blog container

# Cleanup
# By removing this command the installation process will be faster but
# changes in the composer or npm files will be ignored
#rm -rf node_modules package-lock.json vendor composer.lock composer.phar composer-setup.php .env

# Remove old bundles if any (check webpack.mix.js to update)
rm -rf public/js/*.js public/css/*.css

# Install alpine packages
apk add curl bash vim nodejs npm mariadb mariadb-client openrc

# Config ~/.bashrc file
cat <<EOF -> ~/.bashrc
#!/bin/bash
export LARAVEL_SERVER=dev

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

# Install composer's dependencies
composer install

# Generate new APP_KEY on .env file
php artisan key:generate

# Start DB migrations
php artisan migrate

if [ "$LARAVEL_SERVER" == "dev" ]
then
  # Watch for changes in the js/css files to rebundle
  npm run watch &

  # Start the artisan server in the background (dev)
  php artisan serve --host=0.0.0.0 --port=$LARAVEL_HTTP_PORT &

else
  # Prod configurations are not working currently

  # https://laravel.com/docs/6.x/deployment#nginx

  # Install alpine packages
  apk add nginx php7-fpm

  # Start PHP-FPM
  rc-service php-fpm7 start

  cat <<EOF -> /etc/nginx/conf.d/default.conf
server {
    listen $LARAVEL_HTTP_PORT;
    server_name localhost;
    root $(pwd)/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    gzip on;
    gzip_types text/html text/css text/javascript application/javascript application/x-javascript;

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass localhost:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

  # Start nginx
  rc-service nginx start
fi

# Don't stop the container on server crash
set +x
sleep 3
while :;
do
  echo Idle...
  date +%F_%T
  sleep $((2**20))
done
