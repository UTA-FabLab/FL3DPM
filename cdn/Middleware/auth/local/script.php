<?php
/**
 * Middleware/auth/local/script.php
 *
 * Middleware script that handles local authentication requests
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

$scriptDir = dirname(__FILE__);

$scriptPath = \App\Request::scriptPath();

$scriptOption = false;

if (str_starts_with($scriptPath, 'auth/')) {
    $scriptOption = substr($scriptPath,5);
}

switch($scriptOption) {

    // catch false before it matches empty string
    case false:
        \Framework\redirectIndex();
        break;

    case 'init':
        require($scriptDir . '/init/script.php');
        break;

    case 'login':
        require($scriptDir . '/login/script.php');
        break;

    case 'logout':
        require($scriptDir . '/logout/script.php');
        break;

    case 'password/reset':
        require($scriptDir . '/password/reset/script.php');
        break;

    case 'password/redeem':
        require($scriptDir . '/password/redeem/script.php');
        break;

    default:
        \Framework\redirectIndex();
        break;
}

