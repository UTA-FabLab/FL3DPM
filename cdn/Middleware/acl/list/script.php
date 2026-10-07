<?php
/**
 * Middleware/acl/list/script.php
 *
 * Handles the process of displaying a list of user granted acl
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

include(MIDDLEWARE_ROOT . 'DataTable/script.php');

class ACLList extends \MiddleWare\DataTable
{
    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->pageTitle = 'Access Permissions';
        $this->tableTitle = 'Access Permissions';
        $this->exportFileName = 'acl.csv';
        self::footIncludeJS( \Framework\cdnURL('Middleware/js/acl-list.js') );
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
        //-- Call parent

            parent::buildContent();

        //-- Find all acl records

            $this->records = array();

            $acls = new \MiddleWare\Table\ACLList;

            if (! $acls->query()) {
                return true;
            }

        //-- Build record list of users with ACL acls
        //-- only include staff once in list

            $ndx = 0;
            $included = array();

            while ($acls->next()) {
                $record = $acls->record();

                if ($this->filter($record, array('last_name' => true, 'first_name' => true, 'email' => true))) {
                    continue;
                }

                if (! array_key_exists($record['email'], $included)) {
                    $included[$record['email']] = $ndx;
                    $this->records[$ndx] = array(
                        'email'   => $record['email'],
                        'name'    => $record['last_name'] . ', ' . $record['first_name'],
                        'user_id' => $record['user_id'],
                        'manager' => false,
                    );
                    if ($record['tag'] == APP_ACL_MANAGE_ACL) {
                        $this->records[$ndx]['manager'] = true;
                    }
                    $ndx++;
                } else {
                    if ($record['tag'] == APP_ACL_MANAGE_ACL) {
                        $cNdx = $included[$record['email']];
                        $this->records[$cNdx]['manager'] = true;
                    }
                }
            }

        return true;
    }

    protected function renderEndToolBar()
    {
        \App\Render::printButton();
        \App\Render::exportButton();
        print '<div class="app-vr"></div>';
        \App\Render::iconButton(
            'addButton',
            'manage/acl/add',
            'btn',
            ICON_PERSON_ADD,
            'Add Access',
            'style="font-size: 2rem;"');
    }


    protected function getHeader()
    {
        $header = array(
            'name'  => array('title' => 'Name', 'cellStyle' => ''),
            'email' => array('title' => 'Email', 'cellStyle' => ''),
        );

        if ($this->withHeader === true) {
            $header['links'] = array('title' => '<span class="visually-hidden">Action</span>', 'cellStyle' => 'text-align: right');
        }

        return $header;
    }        


    protected function getRow()
    {
        $record = parent::getRow();

        if ($record === false) {
            return false;
        }

        if ($this->withHeader === true) {
            $record['name'] = '<a href="' . \Framework\siteURL('manage/acl/view?id=' . $record['user_id']) . '">' . $record['name'] . '</a>';

            if ($record['manager'] == 1) {
                $record['name'] = '<i class="' . ICON_PERSON_BADGE . '"></i> ' . $record['name'] ;
            }

            $record['links'] = 
                \App\Render::iconButton(
                    'editButton' . $record['user_id'],
                    'manage/acl/edit?id=' . $record['user_id'],
                    'btn',
                    ICON_EDIT,
                    'Edit Access',
                    'style="font-size: 1.5em;"', inPrint: false) .
                \App\Render::iconButton(
                    'trashButton' . $record['user_id'],
                    'manage/acl/remove?id=' . $record['user_id'],
                    'btn',
                    ICON_TRASH,
                    'Remove Access',
                    'style="font-size: 1.5em;"', inPrint: false);

        } 

        return $record;
    }

}


$page = new ACLList();

$page->render();


