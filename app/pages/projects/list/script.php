<?php
/**
 * projects/list/script.php
 *
 * Handles the process of displaying a list of projects
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

define('SHOW_PROJECTS_ALL',      'ALL');
define('SHOW_PROJECTS_ACTIVE',   '0');
define('SHOW_PROJECTS_CLOSED',    PROJECT_STATUS_CLOSED);

define('SHOW_PROJECTS_LIST', array(
    SHOW_PROJECTS_ALL,
    SHOW_PROJECTS_ACTIVE,
    SHOW_PROJECTS_CLOSED,
));

class ProjectsList extends \MiddleWare\DataTable
{
    protected $showMode;

    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->tableID = 'projectList';
        $this->pageTitle = 'Projects';
        $this->tableTitle = 'Projects';
        $this->exportFileName = 'projects.csv';
        self::footIncludeJS( \Framework\siteURL('assets/js/projects-list.js') );
    }


    protected function userHasAccess()
    {
        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
            return true;
        }
        return false;
    }


    protected function buildContent()
    {
        //-- Call parent

            parent::buildContent();

        //-- Collect Show Mode

            $this->showMode = \App\Request::getQP('showMode');

            switch($this->showMode) {
                case SHOW_PROJECTS_ALL:
                case SHOW_PROJECTS_ACTIVE:
                case SHOW_PROJECTS_CLOSED:
                    break;
                default:
                $this->showMode = SHOW_PROJECTS_ACTIVE;
            }


        //-- find the projects 

            $projects = new \App\Table\ProjectsList;

            $projects->selectExpressions(
                array_merge(
                    $projects->selectFieldList(inAsArray: true),
                    array(
                        'pp.id AS print_id',
                        'pp.job_ticket_number',
                        'pp.wait_list_number',
                        'pp.status AS print_status',
                    )
                )
            );

            $projects->joinClauses(array(
                'LEFT JOIN project_prints AS pp ON pr.id = pp.project_id',
                'LEFT JOIN project_prints AS pp2 ON pr.id = pp2.project_id AND pp.id < pp2.id'
            ));


            $whereExpressions = array();
            $params = array();

            switch($this->showMode) {
                case SHOW_PROJECTS_ALL:
                    break;

                case SHOW_PROJECTS_CLOSED:
                    $whereExpressions[] = 'pr.status = :status';
                    $params['status'] = $this->showMode;
                    break;

                default:
                    $whereExpressions[] = 'pr.status = :status';
                    $params['status'] = PROJECT_STATUS_ACTIVE;
                    break;
            }

            if (count($whereExpressions) == 0) {
                $whereExpressions[] = 'pp2.id IS NULL';
            } else {
                $whereExpressions[] = 'AND pp2.id IS NULL';
            }

            $projects->whereExpressions($whereExpressions);

            $projects->query($params);

            while($projects->next()) {
                $record = $projects->record();
                $record['orig'] = $record;
                $record['type'] = $projects->normalize('type');
                $record['status'] = $projects->normalize('status');
                $record['cancel_reason'] = $projects->normalize('cancel_reason');
                $record['is_student'] = $projects->normalize('is_student');
                $record['created'] = $projects->normalize('created');
                $record['cancel_date'] = $projects->normalize('cancel_date');
                $record['category'] = $projects->normalize('category');
                $record['name'] = $projects->last_name . ', ' . $projects->first_name;
                $record['pid'] = \App\Normalize::projectID($record['orig']['type'],$record['year'],$record['id']);


                if (is_string($record['print_status'])) {
                    $record['print_status'] = \App\ProjectPrintStatuses::getText($record['print_status']);
                } else {
                    $record['print_status'] = 'unknown';
                }

                if ($this->filter($record,array(
                    'pid' => true,
                    'status' => true,
                    'wait_list_number' => true,
                    'job_ticket_number' => true,
                    'print_status' => true,
                    'name' => true,
                    'email' => true,
                    'created' => true))) {
                    continue;
                }

                $this->records[] = $record;
            }

        return true;
    }


    protected function renderStartToolBar()
    {
        print '<div class="float-start app-datatable-toolbar-start">' . PHP_EOL;
        print '    <div class="row">' . PHP_EOL;
        print '        <div class="col-sm-3 app-datatable-toolbar-field">' . PHP_EOL;
        print '            <label for="showMode" class="col-sm-2 col-form-label">View:</label>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '        <div class="col-sm-9 app-datatable-toolbar-field">' . PHP_EOL;
        print '            <select id="showMode" class="form-select" aria-label="Project View Selection">' . PHP_EOL;
        print '                <option value="ALL"' . ($this->showMode == SHOW_PROJECTS_ALL ? ' selected' : '') . '>All</option>' . PHP_EOL;
        print '                <option value="0"' . ($this->showMode == SHOW_PROJECTS_ACTIVE ? ' selected' : '') . '>Active</option>' . PHP_EOL;
        print '                <option value="C"' . ($this->showMode == SHOW_PROJECTS_CLOSED ? ' selected' : '') . '>Closed</option>' . PHP_EOL;
        print '            </select>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
        print '</div>' . PHP_EOL;
    }


    protected function renderEndToolBar()
    {
        \App\Render::searchButton('projects/search','Project Search');
        print '<div class="app-vr"></div>';
        \App\Render::printButton();
        \App\Render::exportButton();
        print '<div class="app-vr"></div>';
        \App\Render::addButton('projects/add','Add Project','');
    }

    protected function getHeader()
    {
        $header = array(
            'pid'  => array('title' => 'Project ID', 'cellStyle' => 'vertical-align: middle;'),
            'status'  => array('title' => 'Status', 'cellStyle' => 'vertical-align: middle;'),
            'wait_list_number'  => array('title' => 'Wait #', 'cellStyle' => 'vertical-align: middle;'),
            'job_ticket_number'  => array('title' => 'Ticket #', 'cellStyle' => 'vertical-align: middle;'),
            'print_status'  => array('title' => 'Print Status', 'cellStyle' => 'vertical-align: middle;'),
            'name'  => array('title' => 'Name', 'cellStyle' => 'vertical-align: middle;'),
            'email'  => array('title' => 'Email', 'cellStyle' => 'vertical-align: middle;'),
            'created'  => array('title' => 'Created', 'cellStyle' => 'vertical-align: middle;'),
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
            $record['pid'] = '<a href="' . \Framework\siteURL('projects/view') . '?id=' . $record['id'] . '">' . $record['pid'] . '</a>';

            if ($record['orig']['status'] != PROJECT_STATUS_CLOSED) {
                if (is_numeric($record['print_id'])) {
                    $printStatus = '<a href="';
                    switch($record['orig']['print_status']) {
                        case PROJECT_PRINT_STATUS_PROBLEM:
                            $printStatus .= \Framework\siteURL('prints/view?id=' . $record['print_id'] . '&tab=problems');
                            break;
                        default:
                            $printStatus .= \Framework\siteURL('prints/view?id=' . $record['print_id']);
                            break;
                    }
                    $printStatus .= '">';
                    $printStatus .= $record['print_status'];
                    $printStatus .= '</a>';
                } else {
                    $printStatus = 'unknown';
                }
                $record['print_status'] = $printStatus;
            }

            $record['links'] =  '';
        }

        return $record;
    }

}

$page = new ProjectsList();
$page->render();


