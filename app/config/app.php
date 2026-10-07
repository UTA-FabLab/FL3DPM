<?php
/**
 * app/config/app.php
 *
 * General application initializations common amoung instances
 *
 * @package App
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;


// Application version

    define('APP_VERSION','2026.08.01.01');


// Session key used as index into $_SESSION where the user information is stored

    define('APP_SESSION_KEY', 'ula_session_fl3dpm');
    define('APP_SESSION_KEY_FIELD', 'email');


// Define location of application pages

    define('APP_PAGES_ROOT', 'app/pages/');


// Require login to access pages

    define('APP_REQUIRE_LOGIN', true);

    define('APP_LOGIN_PATH', 'login');


// NavStack Tag

    define('APP_NAVSTACK_TAG', 'fl3dpm');


// pages that do not require login

    define('APP_LOGIN_EXEMPT', array(
        'login' => 1,
        'keepsession' => 1,
        'auth/check'  => 1,
        'auth/clear'  => 1,
        'auth/init'   => 1,
        'auth/login'  => 1,
        'auth/password/reset'  => 1,
        'auth/password/redeem' => 1,
    ));


// Used to handle route rewrites if route is overloaded

    define('APP_REWRITES', array(
        'manage/acl' => 'manage/acl',
        'manage/settings' => 'manage/settings',
        'auth' => 'auth',
    ));


