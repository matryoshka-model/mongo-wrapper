#!/bin/bash

declare -a mongo_ext=("1.4.5" "1.5.0" "1.5.1" "1.5.2" "1.5.3" "1.5.3" "1.5.5" "1.5.6" "1.5.7" "1.5.8")

composer self-update
composer config --global github-oauth.github.com ${GH_TOKEN}
pecl uninstall mongo

for version in "${mongo_ext[@]}"
do
    echo " Installing mongo-${version}"
    yes "no" | pecl install mongo-${version}

    composer install

    vendor/bin/phpunit
done
