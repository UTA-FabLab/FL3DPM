<?php
/**
 * app/pages/user/password/script.php
 *
 * Handles the process of updating a user password for local authenticated accounts
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

class UserPasswordPage extends \App\Form
{
    protected $userAccount;

    public function __construct()
    {
        $this->csrfTokenName = 'user_password_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Change Password';
        $this->formTitle = 'Change Password';
        $this->userAccount = new \MiddleWare\Table\StaffIdentityRecord;
        $this->postCollectID = false; // we are using current user so need to collect idField
        $this->postCallFindRecord = true; // but we still need to find the identity record
        $this->useTokenRecordID = false; 
    }

    protected function userHasAccess()
    {
        if (APP_AUTH_LIB != 'local') {
            return false;
        }

        return true;
    }

    protected function findRecord($inRecordID=false)
    {
        $this->userAccount = new \MiddleWare\Table\StaffIdentityRecord;
        $this->userAccount->debug();

        if (! $this->userAccount->findByID(\App\Request::$user->userID)) {
            if ($this->userAccount->error) {
                return false;
            }
        }

        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['current_password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['current_password']->required = true;
        $this->formVariables['current_password']->id = 'current_password';
        $this->formVariables['current_password']->name = 'current_password';
        $this->formVariables['current_password']->label = 'Current Password';
        $this->formVariables['current_password']->runComplexityCheck = false;
        $this->formVariables['current_password']->autoFocus = true;

        $this->formVariables['new_password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['new_password']->required = true;
        $this->formVariables['new_password']->id = 'new_password';
        $this->formVariables['new_password']->name = 'new_password';
        $this->formVariables['new_password']->label = 'New Password';
        $this->formVariables['new_password']->runComplexityCheck = true;

        $this->formVariables['confirm_password'] = new \MiddleWare\Form\Variable\Password;
        $this->formVariables['confirm_password']->id = 'confirm_password';
        $this->formVariables['confirm_password']->name = 'confirm_password';
        $this->formVariables['confirm_password']->label = 'Confirm Password';
        $this->formVariables['confirm_password']->required = true;
        $this->formVariables['confirm_password']->runComplexityCheck = false;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- check that current password is the correct password

            if (! password_verify($this->formVariables['current_password']->value, $this->userAccount->password_hash)) {
                $this->formVariables['current_password']->setInvalid('incorrect password');
                return false;
            }


        //-- check that the new password is not the same as the current password

            if ($this->formVariables['new_password']->value == $this->formVariables['current_password']->value) {
                $this->formVariables['new_password']->setInvalid('new password cannot be the same as the current password');
                return false;
            }

        //-- check that the confirm password matches the new password

            if ($this->formVariables['confirm_password']->value != $this->formVariables['new_password']->value) {
                $this->formVariables['confirm_password']->setInvalid('confirm password does not match the new password');
                return false;
            }

        //-- update the record

            $params = array(
                'password_hash'        => password_hash($this->formVariables['new_password']->value, PASSWORD_DEFAULT),
                'password_reset_token' => '**',
                'last_updated_on'      => \time(),
            );

            if (! $this->userAccount->update($params)) {
                $this->formErrorMessage = 'There was error while updating the password';
                return false;
            }

        //-- everything ok, display success page

            $this->onSuccess = FORM_ON_SUCCESS_RENDER;
            $this->bodyContentFileName = 'success.php';
            return true;
    }
}

$page = new UserPasswordPage;
$page->process();

