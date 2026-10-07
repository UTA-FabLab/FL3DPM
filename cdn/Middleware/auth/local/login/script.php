<?php
/**
 * Middleware/auth/local/login/script.php
 *
 * Constructs a form to allow user to authenticate locally
 *
 * @package Middleware;
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App;

class UserLoginPage extends \App\LoginForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'auth_local_login_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Login';
        $this->formTitle = 'Login';
        $this->postCollectID = false;
        $this->postCallFindRecord = false;
        $this->useTokenRecordID = false; 
    }

    protected function userHasAccess()
    {
        return true;
    }

    protected function findRecord($inRecordID=false)
    {
        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['email'] = new \MiddleWare\Form\Variable\Email;
        $this->formVariables['email']->required = true;
        $this->formVariables['email']->autoFocus = true;

        $this->formVariables['password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['password']->required = true;
        $this->formVariables['password']->runComplexityCheck = false;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- Find the user and load ACLs

            $user = new \App\User;

            if (! $user->findByEmail($this->formVariables['email']->value)) {
                $this->formErrorMessage = 'Unable to authenticate';
                return false;
            }

            if (! $user->authenticate($this->formVariables['password']->value)) {
                $this->formErrorMessage = 'Unable to authenticate';
                return false;
            }

            $user->collectACL();

        //-- Setup the error page

            $errorPage = new \App\LoginErrorPage(dirname(__FILE__) . '/');
            $errorPage->setBodyContentFileName('notice-not-authorized.php');
            $errorPage->setAccessOverride();

        //-- Call application access checks

            $checkFile = APP_ROOT . 'app/instance/authAccessCheck.php';

            if (! file_exists($checkFile)) {
                $this->formErrorMessage = 'Unable to authenticate';
                return false;
            }

            if ((include $checkFile) === FALSE) {
                $this->formErrorMessage = 'Unable to authenticate';
                return false;
            }

        //-- See if logins are currently limited by app developer

            if (defined('APP_RESTRICT_LOGIN')) {
                if (APP_RESTRICT_LOGIN === true) {
                    if ($this->formVariables['email']->value != APP_EMAIL_DEVELOPER) {
                        $this->formErrorMessage = 'Logins are current restricted to application developers.  Please try again later';
                        return false;
                    }
                }
            }

        //-- Remember user

            $user->rememberUser($this->formVariables['email']->value);

        //-- Redirect to site index (or previously requested page)

            if (array_key_exists('login_request_uri', $_SESSION)) {
                $nextPage = $_SESSION['login_request_uri'];
                unset($_SESSION['login_request_uri']);
                $this->redirectPage = $nextPage;
            } else {
                $this->redirectPage = APP_AUTH_REDIRECT_PAGE;
            }

        return true;
    }
}

$page = new UserLoginPage;
$page->process();


