#!/bin/bash

# e causes to exit when one commands returns non-zero
# v prints every line before executing
set -ev

if [ -n "$TRAVIS_BUILD_DIR" ]; then
    CI_PROJECT_DIR=$TRAVIS_BUILD_DIR
fi

# set symlink so it seems as if this is a factual laravel installation
ln -s $CI_PROJECT_DIR/vendor/ $CI_PROJECT_DIR/vendor/laravel/laravel/vendor

# delete "old" tenancy configurations if they exist
if [ -f "$CI_PROJECT_DIR/vendor/laravel/laravel/config/tenancy.php" ]; then
    rm "$CI_PROJECT_DIR/vendor/laravel/laravel/config/tenancy.php"
fi
if [ -f "$CI_PROJECT_DIR/vendor/laravel/laravel/config/webserver.php" ]; then
    rm "$CI_PROJECT_DIR/vendor/laravel/laravel/config/webserver.php"
fi

cd $CI_PROJECT_DIR