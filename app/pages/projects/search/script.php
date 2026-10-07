<?php
/**
 * projects/search/script.php
 *
 * Handles the process of displaying a list of projects by search criteria
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

define('SEARCH_PROJECT_ID',          '0');
define('SEARCH_PROJECT_YEAR',        '1');
define('SEARCH_PROJECT_CUSTOMER_ID', '2');
define('SEARCH_PROJECT_LASTNAME',    '3');
define('SEARCH_PROJECT_FIRSTNAME',   '4');
define('SEARCH_PROJECT_EMAIL',       '5');
define('SEARCH_PROJECT_WAIT',        '6');
define('SEARCH_PROJECT_JOB',         '7');

define('SEARCH_PROJECTS_LIST', array(
    SEARCH_PROJECT_ID => 'Project ID',
    SEARCH_PROJECT_YEAR => 'Project Year',
    SEARCH_PROJECT_CUSTOMER_ID => 'Customer ID',
    SEARCH_PROJECT_LASTNAME => 'Last Name',
    SEARCH_PROJECT_FIRSTNAME => 'First Name',
    SEARCH_PROJECT_EMAIL => 'Email',
    SEARCH_PROJECT_WAIT => 'Wait Ticket',
    SEARCH_PROJECT_JOB => 'Job Number',
));


class ProjectsList extends \MiddleWare\DataTable
{
    protected $searchMode;
    protected $searchTerm;

    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->tableID = 'projectSearchList';
        $this->pageTitle = 'Projects - Search';
        $this->tableTitle = 'Projects - Search';
        $this->exportFileName = 'projects.csv';
        self::footIncludeJS( \Framework\siteURL('assets/js/projects-search.js') );
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

        //-- Collect Search Mode

            $this->searchMode = \App\Request::getQP('searchMode');

            switch($this->searchMode) {
                case SEARCH_PROJECT_ID:
                case SEARCH_PROJECT_YEAR:
                case SEARCH_PROJECT_CUSTOMER_ID:
                case SEARCH_PROJECT_LASTNAME:
                case SEARCH_PROJECT_FIRSTNAME:
                case SEARCH_PROJECT_EMAIL:
                case SEARCH_PROJECT_WAIT:
                case SEARCH_PROJECT_JOB:
                    break;
                default:
                    $this->searchMode = SEARCH_PROJECT_ID;
            }

        //-- Collect Search Term

            $this->searchTerm = \App\Request::getQP('searchTerm');

            if ($this->searchTerm === false) {
                $this->searchTerm = '';
            }

        //-- Build the query

            $projects = new \App\Table\ProjectsList;

            $sql = true;
            $selectExpressions = array();
            $joinClauses = array();
            $whereExpressions = array();
            $params = array();

            switch($this->searchMode) {

                case SEARCH_PROJECT_ID:
                    if (strpos($this->searchTerm,'-') === false) {
                        $terms = array($this->searchTerm);
                    } else {
                        $terms = explode('-',$this->searchTerm);
                    }

                    switch(count($terms)) {

                        case 1:
                            $whereExpressions[] = 'id LIKE :id';
                            $params['id'] = '%' . $terms[0] . '%';
                            break;

                        case 2:
                            if (is_numeric($terms[0])) {
                                $whereExpressions[] = 'year LIKE :year';
                                $whereExpressions[] = 'AND id LIKE :id';
                                $params['year'] = '%' . $terms[0] . '%';
                                $params['id'] = '%' . $terms[1] . '%';
                            } else {
                                $whereExpressions[] = 'year LIKE :year';
                                $params['year'] = '%' . $terms[1] . '%';
                            }
                            break;

                        case 3:
                            $whereExpressions[] = 'year LIKE :year';
                            $whereExpressions[] = 'AND id LIKE :id';
                            $params['year'] = '%' . $terms[1] . '%';
                            $params['id'] = '%' . $terms[2] . '%';
                            break;

                        default:
                            $sql = false;
                            break;
                    }

                    break;

                case SEARCH_PROJECT_YEAR:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $whereExpressions[] = 'year LIKE :year';
                        $params['year'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_CUSTOMER_ID:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $whereExpressions[] = 'customer_id LIKE :customer_id';
                        $params['customer_id'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_LASTNAME:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $whereExpressions[] = 'last_name LIKE :last_name';
                        $params['last_name'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_FIRSTNAME:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $whereExpressions[] = 'first_name LIKE :first_name';
                        $params['first_name'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_EMAIL:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $whereExpressions[] = 'email LIKE :email';
                        $params['email'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_WAIT:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $selectExpressions[] = 'pp.wait_list_number ';
                        $joinClauses[] = 'RIGHT JOIN project_prints AS pp ON pp.project_id = pr.id';
                        $whereExpressions[]= 'pp.wait_list_number LIKE :wait_list_number';
                        $params['wait_list_number'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                case SEARCH_PROJECT_JOB:
                    if (strlen($this->searchTerm) < 2) {
                        $sql = false;
                        $this->searchError = 'Must provide at least 2 characters';
                    } else {
                        $selectExpressions[] = 'pp.job_ticket_number ';
                        $joinClauses[] = 'RIGHT JOIN project_prints AS pp ON pp.project_id = pr.id';
                        $whereExpressions[] = 'pp.job_ticket_number LIKE :job_ticket_number';
                        $params['job_ticket_number'] = '%' . $this->searchTerm . '%';
                    }
                    break;

                default:
                    $sql = false;
            }


            if ($sql === false) {
                return false;
            }


            $projects->selectExpressions(array_merge($projects->selectFieldList(inAsArray: true), $selectExpressions));

            $projects->joinClauses($joinClauses);

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

                if ($this->filter($record,array(
                    'pid' => true,
                    'status' => true,
                    'wait_list_number' => true,
                    'job_ticket_number' => true,
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
        print '<form>' . PHP_EOL;
        print '    <div class="float-start app-datatable-toolbar-start">' . PHP_EOL;
        print '        <div class="row">' . PHP_EOL;
        print '            <div class="col-sm-3 app-datatable-toolbar-field">' . PHP_EOL;
        print '                <label for="searchMode" class="col-sm-2 col-form-label">View:</label>' . PHP_EOL;
        print '            </div>' . PHP_EOL;
        print '            <div class="col-sm-9 app-datatable-toolbar-field">' . PHP_EOL;
        print '                <select id="searchMode" class="form-select" aria-label="Project View Selection">' . PHP_EOL;
        foreach(SEARCH_PROJECTS_LIST as $searchType => $searchText) {
            print '                    <option value="' . $searchType . '"' . ($this->searchMode == $searchType ? ' selected' : '') . '>' . $searchText . '</option>' . PHP_EOL;
        }
        print '                </select>' . PHP_EOL;
        print '            </div>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
        print '    <div class="float-start app-datatable-toolbar-start">' . PHP_EOL;
        print '        <div class="row">' . PHP_EOL;
        print '            <div class="col-sm-2 app-datatable-toolbar-field">' . PHP_EOL;
        print '                <label for="searchTerm" class="col-form-label">For: </label>' . PHP_EOL;
        print '            </div>' . PHP_EOL;
        print '            <div class="col-sm-9 app-datatable-toolbar-field">' . PHP_EOL;
        print '                <input type="search" id="searchTerm" name="searchTerm" value="' . $this->searchTerm . '" class="form-control">' . PHP_EOL;
        print '            </div>' . PHP_EOL;
        print '            <div class="col-sm-1">' . PHP_EOL;

        \App\Render::searchButton('#','Project Search');

        print '                <span class="visually-hidden"><input type="submit" value="Submit Search"></span>' . PHP_EOL;
        print '            </div>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
        print '</form>' . PHP_EOL;
    }

    protected function renderEndToolBar()
    {
        print '<div class="app-datatable-toolbar-end">' . PHP_EOL;
        \App\Render::printButton();
        \App\Render::exportButton();
        print '</div>' . PHP_EOL;
    }


    protected function getHeader()
    {
        switch($this->searchMode) {
            case SEARCH_PROJECT_WAIT:
                $header = array(
                    'pid'  => array('title' => 'Project ID', 'cellStyle' => ''),
                    'wait_list_number'  => array('title' => 'Wait #', 'cellStyle' => ''),
                    'name'  => array('title' => 'Name', 'cellStyle' => ''),
                    'email'  => array('title' => 'Email', 'cellStyle' => ''),
                    'status'  => array('title' => 'Status', 'cellStyle' => ''),
                );
                break;

            case SEARCH_PROJECT_JOB:
                $header = array(
                    'pid'  => array('title' => 'Project ID', 'cellStyle' => ''),
                    'job_ticket_number'  => array('title' => 'Wait #', 'cellStyle' => ''),
                    'name'  => array('title' => 'Name', 'cellStyle' => ''),
                    'email'  => array('title' => 'Email', 'cellStyle' => ''),
                    'status'  => array('title' => 'Status', 'cellStyle' => ''),
                );
                break;

            default:
                $header = array(
                    'pid'  => array('title' => 'Project ID', 'cellStyle' => ''),
                    'name'  => array('title' => 'Name', 'cellStyle' => ''),
                    'email'  => array('title' => 'Email', 'cellStyle' => ''),
                    'status'  => array('title' => 'Status', 'cellStyle' => ''),
                );
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
            $record['links'] =  '';
        }

        return $record;
    }

}

$page = new ProjectsList();
$page->render();


