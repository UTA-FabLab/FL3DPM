<?php
/**
 * Middleware/acl/remove/script.php
 *
 * Handles the process of editing a staff member's ACL
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

class ACLRemovePage extends \App\Form
{
    protected $staffIdentity;

    public function __construct()
    {
        $this->csrfTokenName = 'acl_remove_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Access Permissions - Remove';
        $this->formTitle = 'Remove Staff Member Access';

        $this->staffIdentity = new \MiddleWare\Table\StaffIdentityRecord;
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
    }


    protected function loadReadOnlyValues()
    {
        $this->formVariables['staff']->value = $this->staffIdentity->fullname();
    }


    protected function loadWriteValues() {

        $this->formVariables['id']->value = $this->staffIdentity->id;

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


        //-- Find all existing records for the user

            $staffACL = new \MiddleWare\Table\ACLList;
            $staffACL->whereExpressions(array('acl.user_id = :user_id'));

            $params = array(
                'user_id' => $this->staffIdentity->id
            );

            if (! $staffACL->query($params)) {
                \Framework\redirectPage('manage/acl/list');
                return;
            }

            $existingACL = array();
            while($staffACL->next()) {
                $existingACL[$staffACL->tag] = $staffACL->id;
            }


        //-- Remove all records which are not in the new ACL list
        //-- We are removing one at a time for logging

            foreach($existingACL as $aclTag => $aclID) {

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


        //-- Proceed to view ACL list

            $this->redirectPage = 'manage/acl/list';
            return true;
    }


    public function render()
    {
        parent::render();
    }
}

$page = new ACLRemovePage;
$page->process();


