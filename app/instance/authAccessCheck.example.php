<?php
/**
 * app/instance/authAccessCheck.example.php
 *
 * Instance specific authentication/authorization access checks
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

if (! $user->hasACL(array(APP_ACL_ACCESS))) {
    $errorPage->setErrMsg('[no app access]');
    $errorPage->render();
    exit;
}

