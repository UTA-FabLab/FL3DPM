<?php
/**
 * manage/printers/list/script.php
 *
 * Handles the process of displaying a list of printers
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

class PrintersList extends \MiddleWare\DataTable
{
    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->tableID = 'printerList';
        $this->pageTitle = 'Printers';
        $this->tableTitle = 'Printers';
        $this->exportFileName = 'printers.csv';
        self::footIncludeJS( \Framework\siteURL('assets/js/manage-printers-list.js') );
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

        //-- find the printers 

            $printers = new \App\Table\PrintersList;

            $printers->query();

            while($printers->next()) {
                $record = $printers->record();
                if ($this->filter($record, array('name' => true, 'enabled' => true))) {
                    continue;
                }
                $record['enabled'] = $printers->normalize('enabled');
                $this->records[] = $record;
            }

        return true;
    }


    protected function renderEndToolBar()
    {
        \App\Render::printButton();
        \App\Render::exportButton();
        print '<div class="app-vr"></div>';
        \App\Render::addButton('manage/printers/add','Add Printer','');
    }


    protected function getHeader()
    {
        $header = array(
            'name' => array('title' => 'Name', 'cellStyle' => 'vertical-align: middle;'),
            'enabled' => array('title' => 'Enabled', 'cellStyle' => 'vertical-align: middle;'),
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
                    'manage/printers/edit?id=' . $record['id'],
                    'Edit Printer ' . $record['name'],
                    strval($record['id']),
                    inPrint: false);
        }

        return $record;
    }

}

$page = new PrintersList();
$page->render();


