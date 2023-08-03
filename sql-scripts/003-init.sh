#!/bin/bash

set -e

temp_sql_file='/tmp/init.sql'

# Resolve hostname to IP
ip_address=$(getent hosts ${MYSQL_ALLOWED_HOST} | awk '{ print $1 }')

cat << EOF > $temp_sql_file
CREATE USER '${MYSQL_ROOT_USER}'@'$ip_address' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_ROOT_USER}'@'$ip_address';
FLUSH PRIVILEGES;
EOF

echo "Executing $temp_sql_file"
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" < $temp_sql_file
rm $temp_sql_file
