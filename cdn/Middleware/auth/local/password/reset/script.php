<?php
/**
 * Middleware/auth/local/password/reset/script.php
 *
 * Constructs a form to allow user to request a reset of account password
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

class PasswordResetPage extends \App\LoginForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'auth_local_password_reset_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Password Reset Request';
        $this->formTitle = 'Password Reset Request';
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
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- Find the user

            $user = new \Middleware\Table\StaffIdentityRecord;

            if (! $user->findByEmail($this->formVariables['email']->value)) {
                $this->formErrorMessage = 'An error was encountered while trying to reset your password';
                return false;
            }

        //-- make sure the user is enabled

            if ($user->enabled == VALUE_NO) {
                $this->formErrorMessage = 'An error was encountered while trying to reset your password';
                return false;
            }

        //-- generate a password reset token

            $expirationTime = \time() + 3600;

            $resetToken = \Framework\getGUID();

            $params = array(
                'password_reset_token' => $expirationTime . ':' . $resetToken
            );

            if (! $user->update($params)) {
                $this->formErrorMessage = 'An error was encountered while trying to reset your password';
                return false;
            } 

        //-- send email

            $message  = 'To reset your password, please click on the link below.<BR><BR>' . PHP_EOL;

            $message .= '<a href="' . \Framework\siteURL('auth/password/redeem?token=' . $resetToken) . '">Reset Password</a>';
            $message .= '<BR><BR>' . PHP_EOL;

            $message .= 'If you\'re having trouble, try copying and pasting the following URL into your browser:';
            $message .= 'message link goes here<BR><BR>';
            $message .= 'If you did not request this reset, you can ignore this email. It will expire in 5 minutes';

            \Middleware\mail($user->email,'Reset your password',$message);

        //-- everything ok, display success page

            $this->onSuccess = FORM_ON_SUCCESS_RENDER;
            $this->bodyContentFileName = 'success.php';
            return true;
    }
}

$page = new PasswordResetPage;
$page->process();


