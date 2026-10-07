<?php
/**
 * index.php
 *
 * Main script for application
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


//-- define the web application root -- defined paths should have ending slash (/)

    define('APP_ROOT', dirname(__FILE__) . '/');


//-- Load in the instance configuration

    if (file_exists(APP_ROOT . '/app/instance/config.php')) {
        require_once(APP_ROOT . '/app/instance/config.php');
    } else {
        die('unable to load instance config');
    }


//-- Load in the instance custom message configuration

    if (file_exists(APP_ROOT . '/app/instance/messages_' . APP_LANG . '.php')) {
        require_once(APP_ROOT . '/app/instance/messages_' . APP_LANG . '.php');
    } else {
        die('unable to load instance message config');
    }


//-- Load the required libraries

    if (file_exists(APP_ROOT . '/app/config/lib.php')) {
        require_once(APP_ROOT . '/app/config/lib.php');
    } 


//-- process the request

    if (! \App\Request::init()) {
        session_write_close();
        exit;
    }

    $script = \App\Request::process();

    if ($script === false) {
        session_write_close();
        exit;
    }

//-- load the script

    require($script);

    session_write_close();

