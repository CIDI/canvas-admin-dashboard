<?php

/**
 * Configuration
 *
 * For more info about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 */

/**
 * Configuration for: Error reporting
 * Useful to show every little problem during development, but only show hard errors in production
 */
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set("display_errors", 1);

/**
 * Configuration for: Project URL
 * Put your URL here, for local development "127.0.0.1" or "localhost" (plus sub-folder) is fine
 */
define('URL', '/');
define('STATIC_URL', '/canvasCustomTools/canvas-admin-dashboard/');

/**
 * Configuration for: Views
 *
 * PATH_VIEWS is the path where your view files are. Don't forget the trailing slash!
 * PATH_VIEW_FILE_TYPE is the ending of your view files, like .php, .twig or similar.
 */
define('PATH_VIEWS', 'application/views/');
define('PATH_VIEW_FILE_TYPE', '.twig');

/**
 * Configuration for: uploads
 *
 * PATH_UPLOADS is the path that the report CSV files will be uploaded to for processing
 */
define('PATH_UPLOADS', realpath(dirname(__DIR__) . '/models/import_data/'));

/**
 * Configuration for: Database
 * This is the place where you define your database credentials, database type etc.
 */
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'db_name');
define('DB_USER', 'db_user');
define('DB_PASS', '');

define('LTI_SHARED_SECRET', 'secretpasscode');


// Encryption Password and salt used for encrypting OAuth tokens in database
define('ENCRYPTION_KEY', 'someString');
define('ENCRYPTION_SALT', 'someString');

// Customize timezone for database timestamps
define('NOW', gmdate("Y-m-d H:i:s"));