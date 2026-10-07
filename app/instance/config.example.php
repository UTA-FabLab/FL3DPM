<?php
/**
 * app/instance/config.php
 *
 * Instance specific configurations 
 *
 * @package App
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;

//-- Set the following as needed

    // puts application into maintenance mode and disallows login
    define('MAINTENANCE_MODE',   false);

    // restricts login to the application developer email address
    define('APP_RESTRICT_LOGIN', false);

    // are errors displayed -- turn OFF in production
    define('APP_ERRORS_ON',      true);



//-- Redirect Email -- used for testing

    define('REDIRECT_ALL_EMAIL', false);
    define('REDIRECT_EMAIL_TO',  'admin@uos.edu');



//-- Application Developer Email Address

    define('APP_EMAIL_DEVELOPER', 'admin@uos.edu');



//-- Application From Email Address

    define('APP_EMAIL', 'fl3dpm@uos.edu');



//-- Application Settings

    // If the application is not setup at the web server root,
    // this is the subdirectory of the appalction
    define('APP_URL_PREFIX', '/fl3dpm/');

    // A setting of Dev enables display modification to show that the
    // application is running in a "dev" mode, and also enabled
    // test features.  Should not run application as a production
    // instance with this set to "dev".
    define('APP_ENVIRONMENT', 'dev'); // Either prod or dev

    define('APP_LANG',                  'en');
    define('APP_NAME',                  'fl3dpm');
    define('APP_UNIVERSITY',            'University of Somewhere');
    define('APP_UNIVERSITY_SHORT_NAME', 'UOS');
    define('APP_ENTITY',                'UOS Library');
    define('APP_FULL_NAME',             'FabLab 3D Print Management System');



//-- Redirect URL after Authentication (defaults to home page)

    define('APP_AUTH_REDIRECT_PAGE', '');



//-- Setup CDN Framework and Middleware

    // if config is being pulled in for cron
    // define needed environment variables
    if (! defined('APP_CRON')) {
        define('APP_SERVER_NAME', $_SERVER['SERVER_NAME']);
        define('CDN_SERVER_NAME', APP_SERVER_NAME);
    }

    define('CDN_PATH',        APP_URL_PREFIX .'cdn/');
    define('FRAMEWORK_ROOT',  APP_ROOT . 'cdn/Framework/');
    define('MIDDLEWARE_ROOT', APP_ROOT . 'cdn/Middleware/');



//-- Communication values (mainly used for display and emails)

    define('APP_TEAM_SVC_NAME',             'FabLab'); // used for communication
    define('APP_TEAM_NAME',                 'the FabLab Team'); // used for communication
    define('APP_TEAM_EMAIL',                'team@uos.edu'); // used for communication
    define('APP_TEAM_PHONE',                '(111) 555-1212'); // used for communication
    define('APP_TEAM_WEBSITE_URL',          'https://library.uos.edu/fablab'); // used for communication
    define('APP_TEAM_POLICY_URL',           'https://library.uos.edu/fablab/guidelines');
    define('APP_TEAM_ID_NAME',              'UOSID');
    define('APP_TEAM_LENDING_PROGRAM_NAME', 'Libary Tech Lending program');
    define('APP_FABAPP_NAME',               'FabApp');
    define('APP_FABAPP_URL',                'https://fab.uos.edu/index.php');

    // UTA has a cashless system for the students which has a designated name
    define('APP_TEAM_MONEY_NAME', 'UOSMoney');


//-- MariaDB definitions

    define('AUTO_CONNECT_DB',  true);
    define('APP_MYSQL_DB',     'fl3dpm');
    define('APP_MYSQL_USER',   'fl3dpm_user');
    define('APP_MYSQL_PASSWD', 'password_goes_here');
    define('APP_MYSQL_DSN',    'mysql:host=localhost;dbname=' . APP_MYSQL_DB);

    //-- this setting allows for user identity to be found in a separate database
    define('APP_MYSQL_IDENTITY_DB',         APP_MYSQL_DB);
    define('APP_MYSQL_IDENTITY_TABLE',      'identity');
    define('APP_MYSQL_IDENTITY_FIRST_NAME', 'first_name');
    define('APP_MYSQL_IDENTITY_LAST_NAME',  'last_name');
    define('APP_MYSQL_IDENTITY_ENABLED',    'enabled');



//-- Auth button paths

    define('APP_LOGIN_BUTTON_PATH',       'auth/init');
    define('APP_LOGOUT_BUTTON_PATH',      'auth/logout');
    define('APP_CLEAR_LOGIN_BUTTON_PATH', 'auth/clear');



//-- Auth Type configuration

    // Pick an authentication type
    // local - uses passwords in the identity table
    // msoauth - uses Microsoft Single Sign-On

    // define('APP_AUTH_LIB', 'local');
    // define('APP_AUTH_LIB', 'msoauth');



//-- MSOAuth Auth configurations -- uncomment and fill out if using MSOAuth

    // define('APP_OAUTH_TENANTID',      'tenant_id_goes_here');
    // define('APP_OAUTH_SCOPE',         'openid%20offline_access%20profile%20user.read');
    // define('APP_OAUTH_METHOD',        'secret');
    // define('APP_OAUTH_AUTH_CERTFILE', '/path/to/certificate.crt');
    // define('APP_OAUTH_AUTH_KEYFILE',  '/path/to/privatekey.pem');
    // define('APP_OAUTH_CLIENTID',      'client_id_goes_here');
    // define('APP_OAUTH_SECRET',        'client_secret_goes_here');
    // define('APP_OAUTH_REDIRECT_PATH', 'msoauth')


