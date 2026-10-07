<?php
/**
 * Middleware/acl/edit/script.php
 *
 * Handles the process of editing a staff member's ACL
 *
 * NOTE: this can also be used to add ACL to a staff member
 *       that does not currently have any ACL assigned
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

//--------------------------------------------------------------------
//-- Define custom Checkbox form class for rendering

    class ACLCheckboxFormVariable extends \Framework\Form\Variable\Checkbox
    {
        public function render($inClasses = '', $inInsideForm = true)
        {
            $labelClasses = array('form-check-label','app-form-check-label');
            $inputClasses = array('form-check-input');
            print '<div class="form-check">';
            $this->renderElement($inputClasses);
            print ' &nbsp; ';
            $this->renderLabel($labelClasses);
            print '</div>' . PHP_EOL;
        }
    }



class ACLEditPage extends \App\Form
{
    protected $staffIdentity;
    protected $newACLS;

    public function __construct()
    {
        $this->csrfTokenName = 'acl_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Access Permissions - Edit';
        $this->formTitle = 'Edit Staff Member Access';

        $this->staffIdentity = new \MiddleWare\Table\StaffIdentityRecord;
        $this->newACLS = array();
        $this->cancelPage  = 'manage/acl/list';
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
        //-- if no staff id passed in, then try to grab from query parameters

            if ($inRecordID === false) {
                $inRecordID = trim(strval(\App\Request::getQP('id')));
                if ($inRecordID === false) {
                    return false;
                }
            }

        //-- Find the identity record

            $this->staffIdentity = new \MiddleWare\Table\StaffIdentityRecord;

            if (! $this->staffIdentity->findByID($inRecordID)) {
                return false;
            }

        //-- establish the cancel and redirect

            $this->cancelPage = 'manage/acl/list';
            $this->redirectPage = 'manage/acl/view?id=' . $this->staffIdentity->id;

        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['id'] = new \Framework\Form\Variable\Text();
        $this->formVariables['id']->id = $this->idField;
        $this->formVariables['id']->name = $this->idField;
        $this->formVariables['id']->label = $this->idField;
        $this->formVariables['id']->hidden = true;
        $this->formVariables['id']->required = true;

        $this->formVariables['staff'] = new \Framework\Form\Variable\Text();
        $this->formVariables['staff']->id = 'staff';
        $this->formVariables['staff']->name = 'staff';
        $this->formVariables['staff']->label = '<b>Staff Member</b>';
        $this->formVariables['staff']->readOnly = true;

        foreach(APP_ACL_LIST as $aclName => $aclDef) {
            $this->formVariables[$aclName] = new \MiddleWare\ACLCheckboxFormVariable();
            $this->formVariables[$aclName]->name = $aclName;
            $this->formVariables[$aclName]->label = APP_ACL_LIST[$aclName]['label'];
        }
    }


    protected function loadReadOnlyValues()
    {
        $this->formVariables['staff']->value = $this->staffIdentity->fullname();
    }


    protected function loadWriteValues() {

        //-- Set the staff identity

            $this->formVariables['id']->value = $this->staffIdentity->id;

        //-- Collect the ACLs

            $staffACL = new \MiddleWare\Table\ACLList;
            $staffACL->whereExpressions(array('acl.user_id = :user_id'));

            if (! $staffACL->query(array('user_id' => $this->staffIdentity->id))) {
                \Framework\redirectPage('manage/acl/list');
                return;
            }

            $acls = $staffACL->records();

            if (count($acls) > 0) {
                $this->cancelPage = 'manage/acl/view?id=' . $this->staffIdentity->id;
            }


        //-- Push the values into the form variables defined by the application

            foreach($acls as $acl) {
                if (array_key_exists($acl['tag'], $this->formVariables)) {
                    $this->formVariables[$acl['tag']]->checked = true;
                }
            }
    }

    protected function getRecordID()
    {
        return $this->staffIdentity->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- User can only do this is they are the real user (if function exists);

            if (! \App\Request::$user->isRealUser()) {
                $this->formErrorMessage = 'Not allowed to update while impersonating';
                return false;
            }

        //-- Collect the ACLs to be created by the application

            $this->newACLS = array();

            foreach(APP_ACL_LIST as $aclName => $aclDef) {
                $this->newACLS[$aclName] = ($this->formVariables[$aclName]->checked ? true : false);
            }

            $this->editCollectSubTreeCheck(APP_ACL_TREE);

        //-- Check if TAG-access needs to be set

            if (! array_key_exists(APP_ACL_ACCESS, $this->newACLS)) {
                $this->newACLS[APP_ACL_ACCESS] = false;
            }

            foreach($this->newACLS as $tag => $val) {
                if ($val === true) {
                    $this->newACLS[APP_ACL_ACCESS] = true;
                    break;
                }
            }

        //-- Find all existing records for the user

            $staffACL = new \MiddleWare\Table\ACLList;
            $staffACL->whereExpressions(array('acl.user_id = :user_id'));

            if (! $staffACL->query(array('user_id' => $this->staffIdentity->id))) {
                \Framework\redirectPage('manage/acl/list');
                return;
            }

            $existingACL = array();
            while($staffACL->next()) {
                $existingACL[$staffACL->tag] = $staffACL->id;
            }

        //-- Remove all records which are not in the new ACL list

            foreach($existingACL as $aclTag => $aclID) {
                if (array_key_exists($aclTag, $this->newACLS)) {
                    if ($this->newACLS[$aclTag] === true) {
                        continue;
                    }
                }

                $prevACL = new \MiddleWare\Table\ACLRecord;

                if (! $prevACL->findByID($aclID)) {
                    $this->formErrorMessage = 'There was an error while removing staff member\'s ACLs';
                    return false;
                }


                if (! $prevACL->delete()) {
                    $this->formErrorMessage = 'There was an error while removing staff member\'s ACLs';
                    return false;
                }                    
            }

        //-- Add all new ACLs if they do not already exist

            foreach($this->newACLS as $acl => $enabled) {

                if ($enabled !== true) {
                    continue;
                }

                if (array_key_exists($acl, $existingACL)) {
                    continue;
                }

                $newACL = new \MiddleWare\Table\ACLRecord;

                $params = array(
                    'user_id' => $this->staffIdentity->id,
                    'tag'     => $acl
                );


                if (! $newACL->insert($params)) {
                    $this->formErrorMessage = 'There was an error while adding staff member\'s ACLs';
                    return false;
                }
            }


        $this->redirectPage = 'manage/acl/view?id=' . $this->formVariables['id']->value;
        return true;
    }

    protected function editRenderACLFormVariables($inACLTree)
    {
        foreach($inACLTree as $aclName => $aclSub) {
            print '<ul style="list-style-type: none">' . PHP_EOL;
            print '    <li>' . PHP_EOL;
            print $this->formVariables[$aclName]->render() . PHP_EOL;
            if (is_array($aclSub)) {
                print PHP_EOL;
                $this->editRenderACLFormVariables($aclSub);
            }
            print '</li>' . PHP_EOL;
            print '</ul>' . PHP_EOL;
        }
    }


    protected function editCollectSubTreeCheck($inACLTree, $inChecked=false)
    {
        foreach($inACLTree as $aclName => $aclSub) {
            if ($inChecked) {
                $this->newACLS[$aclName] = true;
            }

            if (is_array($aclSub)) {
                if ($inChecked) {
                    $this->editCollectSubTreeCheck($aclSub, true);
                } else {
                    $this->editCollectSubTreeCheck($aclSub, ($this->newACLS[$aclName] == true ? true : false));
                }
            }
        }
    }


    public function render()
    {
        $this->footIncludeJS(\Framework\siteURL('assets/js/acl-edit.js'));
        parent::render();
    }
}

$page = new ACLEditPage;
$page->process();


