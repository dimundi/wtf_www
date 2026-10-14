#!/bin/sh
set -eu
export MYSQL_PWD="$MYSQL_PASSWORD"
count=$(mysql -u "$MYSQL_USER" -D "$MYSQL_DATABASE" -Nse 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')
if [ "$count" != 0 ]; then
    echo 'Import przerwany: lokalna baza nie jest pusta.' >&2
    exit 1
fi
gzip -t /tmp/whatthefrog-database.sql.gz
gzip -dc /tmp/whatthefrog-database.sql.gz > /tmp/whatthefrog-database.sql
mysql -u "$MYSQL_USER" -D "$MYSQL_DATABASE" < /tmp/whatthefrog-database.sql
mysql -u "$MYSQL_USER" -D "$MYSQL_DATABASE" -Nse 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()'
