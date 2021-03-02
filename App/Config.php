<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 5.4
 */
class Config
{
    /**
     * Database type
     * Options:  mysql, oci, odbc, pgsql, sqlite, sqlsrv
     * @var string
     */
    const DB_TYPE = 'mysql';

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = '';

    /**
     * Database user
     * @var string
     */
    const DB_USER = '';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    /**
     * Secret key for hashing
     * @var boolean
     */
    const SECRET_KEY = 'ThisIsMySecretKey!';

    /**
     * Gmail Sender email address
     * @var string
     */
    const EMAIL_ADDR = '';

    /**
     * Gmail Sender password
     * @var string
     */
    const EMAIL_PWD = '';

    /**
     * Mailgun API key
     *
     * @var string
     */
    const MAILGUN_API_KEY = 'your-mailgun-api-key';

    /**
     * Mailgun domain
     *
     * @var string
     */
    const MAILGUN_DOMAIN = 'your-mailgun-domain';
}
