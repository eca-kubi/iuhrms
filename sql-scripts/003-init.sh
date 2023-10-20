#!/bin/bash

set -e

# Print the environment variables for debugging purposes
echo "MYSQL_DATABASE: ${MYSQL_DATABASE}"
echo "MYSQL_USER: ${MYSQL_USER}"
echo "MYSQL_PASSWORD: ${MYSQL_PASSWORD}"
echo "MYSQL_ALLOWED_HOST: ${MYSQL_ALLOWED_HOST}"

temp_sql_file='/tmp/init.sql'

cat << EOF > $temp_sql_file
-- Create the user first, if it doesn't already exist
CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'${MYSQL_ALLOWED_HOST}' IDENTIFIED BY '${MYSQL_PASSWORD}';

-- Then grant privileges to that user
GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'${MYSQL_ALLOWED_HOST}';

FLUSH PRIVILEGES;
EOF

echo "Executing $temp_sql_file"
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" < $temp_sql_file
rm $temp_sql_file
