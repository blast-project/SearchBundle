#!/usr/bin/env sh
set -ev

# Database creation

###
### mysql
###

# (mysql service is started by default by travis for each build instance
# (mysql travis user is created by travis for each build instance
# mysql -u travis -e 'CREATE DATABASE travis;' -v

###
### postgresql
###

# needed in .travis.yml
#services:
#  - postgresql
# or here :  sudo /etc/init.d/postgresql start

# (we try to create a travis user)

# (we try to create a travis user)
psql -c "CREATE USER blast_test_user WITH PASSWORD 'blast_test';" -U postgres
psql -c 'ALTER ROLE blast_test_user WITH CREATEDB;' -U postgres

psql -c 'CREATE DATABASE blast_test;' -U postgres
psql -c 'ALTER DATABASE blast_test OWNER TO blast_test_user' -U postgres


psql -c 'CREATE EXTENSION "uuid-ossp";' -U postgres -d blast_test




###
###
###
