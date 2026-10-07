<?php
/**
 * app/pages/manages/accounts/add/script.php
 *
 * Handles the process of adding a new user account
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


class AccountAddForm extends \App\Form
{
    public function __construct()
    {
        $this->defaultCancelPage = 'manage/accounts/list';
        $this->csrfTokenName = 'manage_accounts_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Add User Account';
        $this->formTitle = 'Add User Account';
        $this->postCollectID = false;
        $this->useTokenRecordID = false; 
    }

    protected function userHasAccess()
    {
        if (APP_AUTH_LIB != 'local') {
            return false;
        }

        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) {
            return true;
        }

        return false;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['email'] = new \MiddleWare\Form\Variable\UniversityEmail;
        $this->formVariables['email']->required = true;

        $this->formVariables['first_name'] = new \MiddleWare\Form\Variable\FirstName;
        $this->formVariables['first_name']->required = true;

        $this->formVariables['last_name'] = new \MiddleWare\Form\Variable\LastName;
        $this->formVariables['last_name']->required = true;

        $this->formVariables['enabled'] = new \MiddleWare\Form\Variable\Enabled;
        $this->formVariables['enabled']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['email']->autoFocus = true;
    }

    protected function findRecord($inUserID = false)
    {
        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function postValidate()
    {
        // See if an account with that email address already exists

            $account = new \Middleware\Table\StaffIdentityRecord;

            if ($account->findByEmail($this->formVariables['email']->value)) {
                $this->formVariables['email']->setInvalid('A user account with that email address already exists');
                return false;
            }

        return true;
    }

    protected function scriptUpdate()
    {
        $account = new \Middleware\Table\StaffIdentityRecord;

        $params = array(
            'email'                => $this->formVariables['email']->value,
            'first_name'           => $this->formVariables['first_name']->value,
            'last_name'            => $this->formVariables['last_name']->value,
            'password_hash'        => '**locked**',
            'password_reset_token' => '**locked**',
            'enabled'              => $this->formVariables['enabled']->value,
            'last_updated_on'      => \time()
        );

        if (! $account->insert($params)) {
            $this->formErrorMessage = 'Unable to create user account';
            return false;
        }

        $this->redirectPage  = 'manage/accounts/list';
        return true;
    }
}


$page = new AccountAddForm;
$page->process();

