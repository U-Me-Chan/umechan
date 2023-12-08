#!/bin/bash -e

rm -f test.db
./vendor/bin/phinx migrate -e development
./vendor/bin/phpunit src
rm -f test.db
