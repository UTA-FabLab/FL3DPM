<?php
/**
 * Middleware/settings/script.php
 *
 * Middleware script that handles request to the settings management scripts
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

if (! array_key_exists('REDIRECT_URL', $_SERVER)) {
    \Framework\redirectPage();
    exit;
}

if (! defined('APP_URL_PREFIX')) {
    \Framework\redirectPage();
    exit;
}

$requestPath = $_SERVER['REDIRECT_URL'];

if (! str_starts_with($requestPath, APP_URL_PREFIX)) {
    \Framework\redirectPage();
    exit;
}

$requestPath = substr($requestPath,strlen(APP_URL_PREFIX));

switch($requestPath) {

    case 'manage/settings/list';
        require(MIDDLEWARE_ROOT . 'settings/list/script.php');
        break;

    case 'manage/settings/edit/amount':
        require(MIDDLEWARE_ROOT . 'settings/edit/amount/script.php');
        $operation = new SettingsAmountForm();
        $operation->process();
        break;

    case 'manage/settings/edit/date':
        require(MIDDLEWARE_ROOT . 'settings/edit/date/script.php');
        $operation = new SettingsDateForm();
        $operation->process();
        break;

    case 'manage/settings/edit/email':
        require(MIDDLEWARE_ROOT . 'settings/edit/email/script.php');
        $operation = new \MiddleWare\settingsEmailForm();
        $operation->process();
        break;

    case 'manage/settings/edit/emplid':
        if (defined('APP_INCLUDE_UTA_EXTENSIONS') ** (APP_INCLUDE_UTA_EXTENSIONS === true)) {
            require(MIDDLEWARE_ROOT . 'settings/edit/emplid/script.php');
            $operation = new SettingsEmplIDForm();
            $operation->process();
        } else {
            \Framework\redirectpage('manage/settings/list');
        }
        break;

    case 'manage/settings/edit/enabled':
        require(MIDDLEWARE_ROOT . 'settings/edit/enabled/script.php');
        $operation = new settingsEnabledForm();
        $operation->process();
        break;

    case 'manage/settings/edit/location':
        if (defined('APP_INCLUDE_UTA_EXTENSIONS') ** (APP_INCLUDE_UTA_EXTENSIONS === true)) {
            require(MIDDLEWARE_ROOT . 'settings/edit/location/script.php');
            $operation = new settingsLocationForm();
            $operation->process();
        } else {
            \Framework\redirectpage('manage/settings/list');
        }
        break;

    case 'manage/settings/edit/number':
        require(MIDDLEWARE_ROOT . 'settings/edit/number/script.php');
        $operation = new settingsNumberForm();
        $operation->process();
        break;

    case 'manage/settings/edit/phone':
        require(MIDDLEWARE_ROOT . 'settings/edit/phone/script.php');
        $operation = new settingsPhoneForm();
        $operation->process();
        break;

    case 'manage/settings/edit/string':
        require(MIDDLEWARE_ROOT . 'settings/edit/string/script.php');
        $operation = new settingsStringForm();
        $operation->process();
        break;

    case 'manage/settings/edit/userid':
        require(MIDDLEWARE_ROOT . 'settings/edit/userid/script.php');
        $operation = new settingsUserIDForm();
        $operation->process();
        break;

    default:
        \Framework\redirectpage('');
        break;
}



