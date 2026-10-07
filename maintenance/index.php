<?php
/**
 * maintenance/index.php
 *
 * Script to generate a maintenance notice
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

//-- start the session

    //-- The following 3 settings are used to help protect the session cookie

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_secure',   '1');
    ini_set('session.cookie_httponly', '1');

    session_start();

//-- setup the environment to display the maintenance page

    define('APP_ROOT', dirname(dirname(__FILE__)) . '/');

    require(APP_ROOT . 'app/instance/config.php');

    define('SITE_URL', dirname($_SERVER['REQUEST_URI']) . '/');

    define('CDN_URL', 'https://' . CDN_SERVER_NAME . CDN_PATH);

    require(MIDDLEWARE_ROOT . 'maintenance/index.php');

