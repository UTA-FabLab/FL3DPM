<?php
/**
 * Middleware/acl/view/script.php
 *
 * Handles the process of viewing user ACL
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

define('ICON_HAS_ACL', 'bi bi-check-square-fill');
define('ICON_SUB_ACL', 'bi bi-check-square');
define('ICON_NO_ACL',  'bi bi-square');

class ACLViewPage extends \App\Page
{
    protected $staffIdentity;
    protected $staffACL;
    protected $manageACL;
    protected $acls;

    public function __construct()
    {
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Access Permissions - View';

        $this->staffIdentity = new \MiddleWare\Table\StaffIdentityRecord;
        $this->staffACL = new \MiddleWare\Table\ACLList;
        $this->manageACL = ICON_NO_ACL;

        \Framework\NavStack::push();
    }

    protected function userHasAccess()
    {
        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) {
            return true;
        }

        return false;
    }

    protected function buildContent()
    {
        //-- collect the user id

            $userID = trim(strval(\App\Request::getQP('id')));

            if ($userID === false) {
                \Framework\redirectIndex();
                return false;
            }


        //-- Find the staff identity record

            $this->staffIdentity = new \MiddleWare\Table\StaffIdentityRecord;

            if (! $this->staffIdentity->findByID($userID)) {
                \Framework\redirectPage('manage/acl/list');
                return false;
            }


        //-- find the staff ACL acls

            $this->staffACL = new \MiddleWare\Table\ACLList;
            $this->staffACL->whereExpressions(array('user_id = :user_id'));

            $params = array(
                'user_id' => $this->staffIdentity->id
            );

            if (! $this->staffACL->query($params)) {
                \Framework\redirectPage('manage/acl/list');
                return false;
            }


        //-- Build the ACL tree

            $this->manageACL = ICON_NO_ACL;
            $this->acls = array();
            $this->viewBuildACLTree();

        return true;
    }

    protected function viewSubTreeCheck($inACLTree, $inChecked=false)
    {
        foreach($inACLTree as $aclName => $aclSub) {
            if ($inChecked) {
                $this->acls[$aclName] = ICON_SUB_ACL;
            }

            if (is_array($aclSub)) {
                if ($inChecked) {
                    $this->viewSubTreeCheck($aclSub, true);
                } else {
                    $this->viewSubTreeCheck($aclSub, ($this->acls[$aclName] == ICON_HAS_ACL ? true: false));
                }
            }
        }
    }

    protected function viewBuildACLTree()
    {
        //-- build the working acl list

            $this->acls = array();

            foreach(APP_ACL_LIST as $aclName => $aclDef) {
                $this->acls[$aclName] = ICON_NO_ACL;
            }            


        //-- pull in the information from the database

            $records = $this->staffACL->records();

            foreach($records as $record) {
                if (array_key_exists($record['tag'], $this->acls)) {
                    $this->acls[ $record['tag'] ] = ICON_HAS_ACL;
                }
            }

        //-- check sub acl items

            $this->viewSubTreeCheck(APP_ACL_TREE);
    }

    protected function viewDisplayACL($inACLTree)
    {
        foreach($inACLTree as $aclName => $aclSub) {
            print '<ul style="list-style-type: none">' . PHP_EOL;
            print '    <li><i class="' . $this->acls[$aclName] . '"></i> ';
            print APP_ACL_LIST[$aclName]['label'];
            if (is_array($aclSub)) {
                print PHP_EOL;
                $this->viewDisplayACL($aclSub);
            }
            print '</li>' . PHP_EOL;
            print '</ul>' . PHP_EOL;
        }
    }

}

$page = new ACLViewPage();

$page->render();


