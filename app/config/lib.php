<?php
/**
 * app/config/lib.php
 *
 * Load the required libraries
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;

//-- load the Framework

    require(FRAMEWORK_ROOT . 'lib/Common.php');
    require(FRAMEWORK_ROOT . 'lib/FormVariables.php');


//-- load the Middleware resources

    require(MIDDLEWARE_ROOT . 'lib/Common.php');
    require(MIDDLEWARE_ROOT . 'lib/FormVariables.php');
    require(MIDDLEWARE_ROOT . 'lib/Tables.php');
    require(MIDDLEWARE_ROOT . 'lib/Auth_' . APP_AUTH_LIB . '.php');


//-- load the Application  resources

    if (file_exists(APP_ROOT . 'app/instance/lib.php')) {
        require(APP_ROOT . 'app/instance/lib.php');
    }

    require(APP_ROOT . 'app/lib/Messages_' . APP_LANG . '.php');
    require(APP_ROOT . 'app/lib/Common.php');
    require(APP_ROOT . 'app/lib/FormVariables.php');
    require(APP_ROOT . 'app/lib/Tables.php');

