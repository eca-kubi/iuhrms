#!/bin/bash

set -e

temp_sql_file='/tmp/init.sql'

# Get the IP address of the container's primary network interface (usually eth0)
IP_ADDRESS=$(ip -o -f inet addr show eth0 | awk '{print $4}' | cut -d'/' -f1)

# Extract the first two octets and append ".*.*" to create the wildcard pattern
WILDCARD_PATTERN=$(echo $IP_ADDRESS | cut -d'.' -f1,2,3).%

cat << EOF > $temp_sql_file
-- For the Docker Network
CREATE USER IF NOT EXISTS '${MYSQL_ROOT_USER}'@'${WILDCARD_PATTERN}' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_ROOT_USER}'@'${WILDCARD_PATTERN}';

-- For the Specific Network Domain
CREATE USER IF NOT EXISTS '${MYSQL_ROOT_USER}'@'%.${NETWORK_DOMAIN}' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_ROOT_USER}'@'%.${NETWORK_DOMAIN}';

FLUSH PRIVILEGES;
EOF

echo "Executing $temp_sql_file"
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" < $temp_sql_file
rm $temp_sql_file
