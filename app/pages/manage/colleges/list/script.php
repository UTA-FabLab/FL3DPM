<?php
/**
 * manage/colleges/list/script.php
 *
 * Handles the process of displaying a list of colleges
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

class CollegesList extends \MiddleWare\DataTable
{
    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->tableID = 'collegeList';
        $this->pageTitle = 'Colleges';
        $this->tableTitle = 'Colleges';
        $this->exportFileName = 'colleges.csv';
        self::footIncludeJS( \Framework\siteURL('assets/js/manage-colleges-list.js') );
    }


    protected function userHasAccess()
    {
        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS))) {
            return true;
        }
        return false;
    }


    protected function buildContent()
    {
        //-- Call parent

            parent::buildContent();

        //-- find the colleges 

            $colleges = new \App\Table\CollegesList;

            $colleges->query();

            while($colleges->next()) {
                $record = $colleges->record();
                if ($this->filter($record, array('college' => true, 'enabled' => true))) {
                    continue;
                }
                $record['enabled'] = $colleges->normalize('enabled');
                $this->records[] = $record;
            }

        return true;
    }


    protected function renderEndToolBar()
    {
        \App\Render::printButton();
        \App\Render::exportButton();
        print '<div class="app-vr"></div>';
        \App\Render::addButton('manage/colleges/add','Add College','');
    }


    protected function getHeader()
    {
        $header = array(
            'college'  => array('title' => 'College', 'cellStyle' => 'vertical-align: middle;'),
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
            $record['links'] = \App\Render::editButton(
                    'manage/colleges/edit?id=' . $record['id'],
                    'Edit College ' . $record['college'],
                    strval($record['id']), false);
        }

        return $record;
    }

}

$page = new CollegesList();
$page->render();


