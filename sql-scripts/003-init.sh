#!/bin/bash

set -e

temp_sql_file='/tmp/init.sql'

# Grant access to the root user from the gateway IP address and the IP address of the container hosting the php app (iuhrms)
GATEWAY_IP=$(ip route | awk '/default/ {print $3}')
MYSQL_ALLOWED_HOST_IP=$(getent hosts "${MYSQL_ALLOWED_HOST}" | awk '{ print $1 }')

cat << EOF > $temp_sql_file
-- For the Docker Host
CREATE USER IF NOT EXISTS '${MYSQL_ROOT_USER}'@'$GATEWAY_IP' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_ROOT_USER}'@'$GATEWAY_IP';

-- For the PHP Application Container
CREATE USER IF NOT EXISTS '${MYSQL_ROOT_USER}'@'${MYSQL_ALLOWED_HOST_IP}' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_ROOT_USER}'@'${MYSQL_ALLOWED_HOST_IP}';

FLUSH PRIVILEGES;
EOF

echo "Executing $temp_sql_file"
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" < $temp_sql_file
rm $temp_sql_file
