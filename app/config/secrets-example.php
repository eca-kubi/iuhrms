<?php
/**IMPORTANT!!!!!
 * Don't forget to provide the right values that apply to your deployment environment.
 * See below examples of values you must provide.
 */
const DB_PASS = 'YOUR DATABASE PASSWORD';

# You must create an email client app in your email account and use the app password here
# The password you created for the email client app in your email account
const EMAIL_CLIENT_APP_PASSWORD = 'YOUR EMAIL CLIENT APP PASSWORD';

# A private key used to encrypt and decrypt data
# Use a secure hashing algorithm, for example: md5, sha256, sha512
define('PRIVATE_KEY', md5('your-private-key-value'));