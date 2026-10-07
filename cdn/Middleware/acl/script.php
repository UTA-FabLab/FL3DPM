<?php
/**
 * Middlware/acl/script.php
 *
 * Middleware script that handles request to the ACL management scripts
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

    case 'manage/acl/list':
        require(MIDDLEWARE_ROOT . 'acl/list/script.php');
        break;

    case 'manage/acl/add':
        require(MIDDLEWARE_ROOT . 'acl/add/script.php');
        break;

    case 'manage/acl/edit':
        require(MIDDLEWARE_ROOT . 'acl/edit/script.php');
        break;

    case 'manage/acl/remove':
        require(MIDDLEWARE_ROOT . 'acl/remove/script.php');
        break;

    case 'manage/acl/view':
        require(MIDDLEWARE_ROOT . 'acl/view/script.php');
        break;

    default:
        \Framework\redirectPage();
        break;
}


