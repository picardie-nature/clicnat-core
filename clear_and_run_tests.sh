#!/bin/bash
set -e

if [[ -z "$POSTGRES_DB_TEST" ]]; then
	exit 1
fi
cd res/sql;
echo "drop schema public cascade; create schema public;"|psql $POSTGRES_DB_TEST 
psql $POSTGRES_DB_TEST -f init.sql;
cd -

vendor/bin/phpunit -v
