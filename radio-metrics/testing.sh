#!/bin/bash -e

rm -f test.db
./vendor/bin/phinx migrate -e development
./vendor/bin/phpunit tests
rm -f test.db
