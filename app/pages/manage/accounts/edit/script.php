<?php
/**
 * app/pages/manages/accounts/edit/script.php
 *
 * Handles the process of editing a new user account
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


class AccountEditForm extends \App\Form
{
    protected $account;

    public function __construct()
    {
        $this->defaultCancelPage = 'manage/accounts/list';
        $this->csrfTokenName = 'manage_accounts_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Edit User Account';
        $this->formTitle = 'Edit User Account';
        $this->account = new \Middleware\Table\StaffIdentityRecord;
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
        $this->formVariables['id'] = new \MiddleWare\Form\Variable\RecordID;
        $this->formVariables['id']->required = true;

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

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->account->id;
        $this->formVariables['email']->value = $this->account->email;
        $this->formVariables['first_name']->value = $this->account->first_name;
        $this->formVariables['last_name']->value = $this->account->last_name;
        $this->formVariables['enabled']->value = $this->account->enabled;
    }

    protected function findRecord($inAccountID = false)
    {
       //-- if no account id passed in retrieve from the query parameters

            if ($inAccountID === false) {
                $inAccountID = \App\Request::getQP('id');
                if ($inAccountID === false) {
                    return false;
                }
            }

       //-- find the account by id

            if (! $this->account->findByID($inAccountID)) {
                return false;
            }

        return true;
    }


    protected function canEdit()
    {
        return true;
    }

    protected function getRecordID()
    {
        return $this->account->id;
    }

    protected function postValidate()
    {
        // See if an account with that email address already exists

            $account = new \Middleware\Table\StaffIdentityRecord;

            if ($account->findByEmail($this->formVariables['email']->value)) {
                if ($account->id != $this->account->id) {
                    $this->formVariables['email']->setInvalid('A user account with that email address already exists');
                    return false;
                }
            }

        return true;
    }

    protected function scriptUpdate()
    {
        $params = array(
            'email'                => $this->formVariables['email']->value,
            'first_name'           => $this->formVariables['first_name']->value,
            'last_name'            => $this->formVariables['last_name']->value,
            'enabled'              => $this->formVariables['enabled']->value,
            'last_updated_on'      => \time()
        );

        if (! $this->account->update($params)) {
            $this->formErrorMessage = 'Unable to update user account';
            return false;
        }

        $this->redirectPage  = 'manage/accounts/list';
        return true;
    }
}


$page = new AccountEditForm;
$page->process();

