<?php
/**
 * app/pages/manage/accounts/list/script.php
 *
 * Handles the process of displaying a list of user identities
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

include(MIDDLEWARE_ROOT . 'DataTable/script.php');

class AccountsList extends \MiddleWare\DataTable
{
    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->tableID = 'accountList';
        $this->pageTitle = 'User Accounts';
        $this->tableTitle = 'User Accounts';
        $this->exportFileName = 'accounts.csv';
        self::footIncludeJS( \Framework\siteURL('assets/js/manage-accounts-list.js') );
    }


    protected function userHasAccess()
    {
        if (APP_AUTH_LIB != 'local') {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_ACL))) {
            return true;
        }
        return false;
    }


    protected function buildContent()
    {
        //-- Call parent

            parent::buildContent();

        //-- find the accounts

            $accounts = new \Middleware\Table\StaffIdentityList;

            $accounts->query();

            while($accounts->next()) {
                $record = $accounts->normalizeRecord();
                unset($record['password_hash']);
                unset($record['password_reset_token']);
                $this->records[] = $record;
            }

        return true;
    }


    protected function renderEndToolBar()
    {
        \App\Render::printButton();
        \App\Render::exportButton();
        print '<div class="app-vr"></div>';
        \App\Render::addButton('manage/accounts/add','Add Account','');
    }

    protected function getHeader()
    {
        $header = array(
            'email'  => array('title' => 'Email', 'cellStyle' => 'vertical-align: middle;'),
            'last_name'  => array('title' => 'Last Name', 'cellStyle' => 'vertical-align: middle;'),
            'first_name'  => array('title' => 'First Name', 'cellStyle' => 'vertical-align: middle;'),
            'enabled'  => array('title' => 'Enabled', 'cellStyle' => 'vertical-align: middle;'),
        );

        if ($this->withHeader === true) {
            $header['links'] = array('title' => '<span class="visually-hidden">Action</span>', 'cellStyle' => 'text-align: right');
        }

        return $header;
    }        


    protected function getRow()
    {
        $this->recordNdx++;

        if ($this->recordNdx >= count($this->records)) {
            return false;
        }

        $record = $this->records[$this->recordNdx];

        if ($this->withHeader === true) {
            $record['links'] =
                \App\Render::iconButton(
                    'editButton' . $record['id'],
                    'manage/accounts/edit?id=' . $record['id'],
                    'btn',
                    ICON_EDIT,
                    'Edit Access',
                    'style="font-size: 1.5em;"', inPrint: false);
        }

        return $record;
    }

}

$page = new AccountsList();
$page->render();


