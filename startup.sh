#!/bin/bash

# Check if the SSL_CERTIFICATE and SSL_KEY environment variables are set
if [[ -z "${SSL_CERTIFICATE}" ]] || [[ -z "${SSL_KEY}" ]]; then
  echo "SSL_CERTIFICATE or SSL_KEY environment variable is not set"
  exit 1
fi

# Write the SSL certificate and key to files
echo "${SSL_CERTIFICATE}" | perl -pe 's/@@/\n/g; s/##/\r/g' > /etc/ssl/certs/ssl-cert.pem
cat /etc/ssl/certs/ssl-cert.pem

# Replace the newline and  carriage return placeholders with the actual characters
echo "${SSL_KEY}" | perl -pe 's/@@/\n/g; s/##/\r/g' > /etc/ssl/private/ssl-key.key
cat /etc/ssl/private/ssl-key.key

# Ensure the permissions are secure
chmod 644 /etc/ssl/certs/ssl-cert.pem
chmod 640 /etc/ssl/private/ssl-key.key

# Update the Apache SSL configuration to use the certificate and key files
echo "
<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-key.key
    # ... other SSL settings ...
</VirtualHost>
" > /etc/apache2/sites-available/ssl.conf

cat /etc/apache2/sites-available/ssl.conf

# Enable the SSL site
a2ensite ssl.conf

# Set the ServerName directive globally to suppress the related Apache warning
#echo "ServerName ${APP_HOST}" >> /etc/apache2/apache2.conf

echo "Starting Apache from startup.sh"

# Start Apache in the foreground
exec apachectl -D FOREGROUND
