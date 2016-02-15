#!/usr/bin/env bash
set -e

echo 'Running unit tests.'

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" ]]; then
  ./vendor/bin/phpunit --verbose --coverage-clover build/logs/clover.xml --coverage-text
else
  ./vendor/bin/phpunit --verbose
fi

echo ''
echo ''
echo ''
echo 'Testing for Coding Styling Compliance.'
echo 'All code should follow PSR standards.'
./vendor/bin/php-cs-fixer fix ./ -vv --dry-run --config-file=$(dirname $0)/../.php_cs