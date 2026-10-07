<?php
/**
 * Middleware/auth/msoauth/check/script.php
 *
 * Handles the process of Microsoft OAuth authentication
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

function authCheckAccess()
{
    //-- setup the error page

        $errorPage = new \App\LoginErrorPage(dirname(__FILE__) . '/');
        $errorPage->setBodyContentFileName('notice-not-authorized.php');
        $errorPage->setAccessOverride();

    //-- Do we have a code from Microsoft

        $code = \App\Request::getQP('code',false);

        if ($code === false) {
            $errorPage->setErrMsg('[no code in query parameters]');
            $errorPage->render();
            return;
        }

    //-- Attempt to process the Microsoft response code

        $auth = new \MiddleWare\Auth;

        $auth->loadSessionOAuth();

        $idInfo = $auth->getAccessToken();

        if ($idInfo === false) {
            $errorPage->setErrMsg('[unable to get access token]');
            $errorPage->render();
            return;
        }

    //-- Extract the needed information

        $userEmail = $idInfo->preferred_username;
        $userName = $idInfo->name;

    //-- Attempt to find the user in the database and collect ACLs

        $user = new \App\User;

        if (! $user->findByEmail($userEmail)) {
            $errorPage->setErrMsg('[did not find user [' . $userEmail . ']]');
            $errorPage->render();
            return;
        }

        $user->collectACL();

    //-- Call application access checks

        $checkFile = APP_ROOT . 'app/instance/authAccessCheck.php';

        if (! file_exists($checkFile)) {
            $errorPage->setErrMsg('[authAcessCheck missing]');
            $errorPage->render();
            return;
        }

        if ((include $checkFile) === FALSE) {
            $errorPage->setErrMsg('[failed to include authAccessCheck]');
            $errorPage->render();
            return;
        }

    //-- See if logins are currently limited by app developer

        if (defined('APP_RESTRICT_LOGIN')) {
            if (APP_RESTRICT_LOGIN === true) {
                if ($userEmail != APP_EMAIL_DEVELOPER) {
                    $errorPage->setBodyContentFileName('login-restrictied.php');
                    $errorPage->setErrMsg('[not app developer]');
                    $errorPage->render();
                    return;
                }
            }
        }

    //-- Remember user

        $user->rememberUser($userEmail);

    //-- Redirect to site index

        if (array_key_exists('login_request_uri', $_SESSION)) {
            $redirectPage = $_SESSION['login_request_uri'];
            unset($_SESSION['login_request_uri']);
            \Framework\redirectPage($redirectPage);
        } else {
            //-- Redirect to the define MSOAuth redirect page
            \Framework\redirectPage(APP_AUTH_REDIRECT_PAGE);
        }

}


