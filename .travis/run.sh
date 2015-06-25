#!/usr/bin/env bash

set -eo pipefail;

mkdir -p $HOME/logs

declare -a mongo_ext=("1.4.5") # "1.5.0" "1.5.1" "1.5.2" "1.5.3" "1.5.3" "1.5.5" "1.5.6" "1.5.7" "1.5.8" "1.6.0" "1.6.1" "1.6.2" "1.6.3" "1.6.4" "1.6.5" "1.6.6" "1.6.7" "1.6.8" "1.6.9")

echo "> UPDATING: pecl"
pecl channel-update pecl.php.net >> $HOME/logs/pecl.update.log
echo "> UNINSTALLING: (travis-ci) mongo"
pecl uninstall mongo >> $HOME/logs/mongo.travis.uninstall.log

for version in "${mongo_ext[@]}"
do
    echo "> INSTALLING: mongo-ext ${version}"
    yes "no" | pecl install mongo-${version} >> "$HOME/logs/mongo-${version}.install.log"

    echo "> INSTALLING: dependencies"
    composer install
    echo "> RUN: test against mongo-ext ${version}"
    vendor/bin/phpunit
done
