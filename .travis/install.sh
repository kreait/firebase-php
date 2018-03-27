#!/usr/bin/env bash
set -ev

composer update $COMPOSER_FLAGS --profile --ansi --prefer-dist --no-interaction --optimize-autoloader --no-suggest --no-progress
