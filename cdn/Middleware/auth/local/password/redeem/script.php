<?php
/**
 * Middleware/auth/local/password/redeem/script.php
 *
 * Constructs a form to allow user to reset account password
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

class PasswordRedeemPage extends \App\LoginForm
{
    protected $account;

    public function __construct()
    {
        $this->csrfTokenName = 'auth_local_password_reset_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Password Reset';
        $this->formTitle = 'Password Reset';
        $this->account = new \Middleware\Table\StaffIdentityRecord;
    }

    protected function userHasAccess()
    {
        return true;
    }


    protected function findRecord($inAccountID = false)
    {
       //-- if no account id passed in retrieve token from query parameters

            if ($inAccountID === false) {
    
                $token = \App\Request::getQP('token');
                if ($token === false) {
                    return false;
                }

                if (! $this->account->findByPasswordResetToken($token)) {
                    return false;
                }

                if ($this->account->enabled == VALUE_NO) {
                    return false;
                }

                return true;
            }

       //-- find the account by id

            if (! $this->account->findByID($inAccountID)) {
                return false;
            }

        return true;
    }

    protected function getRecordID()
    {
        return $this->account->id;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->required = true;

        $this->formVariables['email'] = new \MiddleWare\Form\Variable\Email;
        $this->formVariables['email']->required = true;
        $this->formVariables['email']->autoFocus = true;

        $this->formVariables['password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['password']->required = true;
        $this->formVariables['password']->runComplexityCheck = true;

        $this->formVariables['confirm_password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['confirm_password']->id = 'confirm_password';
        $this->formVariables['confirm_password']->name = 'confirm_password';
        $this->formVariables['confirm_password']->label = 'Confirm Password';
        $this->formVariables['confirm_password']->required = true;
        $this->formVariables['confirm_password']->runComplexityCheck = false;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['email']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->account->id;
    }

    protected function postValidate()
    {
        //-- does the account selected match the email address

            if ($this->formVariables['email']->value != $this->account->email) {
                $this->formErrorMessage = 'An error occurred while resetting your password.  ' .
                    'Please check to make sure that all values are correct';
                return false;
            }

        //-- does the password and confirm password match

            if ($this->formVariables['password']->value != $this->formVariables['confirm_password']->value) {
                $this->formVariables['confirm_password']->setInvalid('Confirm password does not match');
                return false;
            }

        return true;
    }

    protected function scriptUpdate()
    {
        // make sure that account is enabled

            if ($this->account->enabled == VALUE_NO) {
                // since it is not enabled, just go back to the login screen
                // we should not have gotten here
                return true;
            }


        // check to make sure that the password reset token has not expired

            $pieces = explode(':', $this->account->password_reset_token);

            if (count($pieces) != 2) {
                $this->formErrorMessage = 'An error was encountered while trying to update your password';
                return false;
            }

            if (! is_numeric($pieces[0])) {
                $this->formErrorMessage = 'An error was encountered while trying to update your password';
                return false;
            }

            $expirationTime = intval($pieces[0]);

            if ($expirationTime < \time()) {
                $this->formErrorMessage = 'An error was encountered while trying to update your password';
                return false;
            }


        // set the password

            $params = array(
                'password_hash'        => password_hash($this->formVariables['password']->value, PASSWORD_DEFAULT),
                'password_reset_token' => '**',
                'last_updated_on'      => \time(),
            );

            if (! $this->account->update($params)) {
                $this->formErrorMessage = 'An error was encountered while trying to update your password';
                return false;
            }

        //-- everything ok, display success page

            $this->onSuccess = FORM_ON_SUCCESS_RENDER;
            $this->bodyContentFileName = 'success.php';
            return true;
    }
}

$page = new PasswordRedeemPage;
$page->process();

