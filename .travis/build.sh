#!/usr/bin/env bash
set -ex

if [ "${UNIT_TESTS}" = "1" ]; then
  echo "Running unit tests"
  vendor/bin/phpunit --testsuite unit
else
  echo "Not running unit tests"
fi

if [ "${INTEGRATION_TESTS}" = "1" ]; then
  echo "Running integration tests"
  vendor/bin/phpunit --testsuite integration
else
  echo "Not running integration tests"
fi

if [ "${STATIC_ANALYSIS}" = "1" ]; then
  echo "Running static analysis"
  vendor/bin/phpstan analyse src -c phpstan.neon --level=6 --no-progress -vvv
else
  echo "Not running static analysis"
fi

if [ "${CODE_COVERAGE}" = "1" ]; then
  echo "Running code coverage"
  vendor/bin/phpunit --coverage-clover coverage.clover

  wget https://scrutinizer-ci.com/ocular.phar
  php ocular.phar code-coverage:upload --format=php-clover coverage.clover
else
  echo "Not running code coverage"
fi
