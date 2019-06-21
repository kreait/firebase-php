#!/usr/bin/env bash
set -ex

if [ "${UNIT_TESTS}" = "1" ]; then
  echo "Running unit tests"
  vendor/bin/phpunit --testsuite unit
else
  echo "Not running unit tests"
fi

if [ "${STATIC_ANALYSIS}" = "1" ]; then
  echo "Running static analysis"
  vendor/bin/phpstan analyse src -c phpstan.neon --level=7 --no-progress -vvv
else
  echo "Not running static analysis"
fi

if [ "${ALL_TESTS}" = "1" ]; then
  echo "Running Sonar Scanner"
  vendor/bin/phpunit --coverage-clover=coverage-report.clover --log-junit=test-report.xml
  sonar-scanner
else
  echo "Not running code coverage"
fi
