<?php
/**
 * Middleware/auth/msoauth/script.php
 *
 * Middleware script that handles request to the Microsoft OAuth management scripts
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

if (str_starts_with($scriptPath, 'auth')) {
    $scriptOption = substr($scriptPath,5);
}

// FIXIT: temporary for testing using libauth
if ($scriptOption === false) {
    if (str_starts_with($scriptPath, 'msoauth')) {
        $scriptOption = 'check';
    }
}

switch($scriptOption) {

    // catch false before it matches empty string
    case false:
        \Framework\redirectIndex();
        break;

    case 'check':
        require($scriptDir . '/check/script.php');
        authCheckAccess();
        break;

    case 'clear':
        require($scriptDir . '/clear/script.php');
        break;

    case 'init':
        require($scriptDir . '/init/script.php');
        break;

    default:
        \Framework\redirectIndex();
        break;
}


