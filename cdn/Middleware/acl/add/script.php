<?php
/**
 * Middleware/acl/add/script.php
 *
 * Handles the process of adding ACL to a staff member that does not currently have ACL
 * NOTE: this process allows user to select a staff member, and then redirects user to edit acl page
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

class ACLAddPage extends \App\Form
{
    public function __construct()
    {
        $this->csrfTokenName = 'acl_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Grant Staff Member Access';
        $this->formTitle = 'Grant Staff Member Access';
        $this->useTokenRecordID = false; 
    }

    protected function userHasAccess()
    {
        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) {
            return true;
        }

        return false;
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
        $this->formVariables['id'] = new \Framework\Form\Variable\Select();
        $this->formVariables['id']->id = $this->idField;
        $this->formVariables['id']->name = $this->idField;
        $this->formVariables['id']->label = '<b>Staff Member</b>';
        $this->formVariables['id']->required = true;
        $this->formVariables['id']->autoFocus = true;
        $this->formVariables['id']->setInitialOption('-','Select a staff member');

        $acls = new \MiddleWare\Table\ACLList;
        $acls->query();

        $exclude = array();
        while ($acls->next()) {
            $exclude[$acls->user_id] = 1;
        }

        $this->formVariables['id']->setSelectOptions(\MiddleWare\getActiveStaff(INDEX_BY_USER_ID,$exclude));
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        $this->redirectPage = 'manage/acl/edit?id=' . $this->formVariables['id']->value . '&add=true';
        return true;
    }
}

$page = new ACLAddPage;
$page->process();


