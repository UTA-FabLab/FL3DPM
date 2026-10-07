<?php
/**
 * app/pages/prints/list/script.php
 *
 * Handles the process of displaying a list of project prints
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

define('VIEW_PRINTS_COMBINED', 'C');
define('VIEW_PRINTS_SEPARATE', 'S');

define('VIEW_PRINTS_LIST', array(
    VIEW_PRINTS_COMBINED,
    VIEW_PRINTS_SEPARATE
));

class PrintsListsPage extends \App\Page
{
    protected $viewMode;
    protected $showAll;
    protected $noteCounts;
    protected $filamentCounts;
    protected $listRecords;
    protected $jobTicketURL;

    public function __construct()
    {
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';

        $this->pageTitle = 'Prints';
        $this->formTitle = 'Prints';

        $this->viewMode       = VIEW_PRINTS_SEPARATE;
        $this->showAll        = false;
        $this->noteCounts     = array();
        $this->filamentCounts = array();
        $this->listRecords    = array();

        $this->jobTicketURL = \Middleware\getConfigValue('FABAPP_JOB_TICKET_URL');
        if ($this->jobTicketURL == '') {
            $this->jobTicketURL = false;
        }

        \Framework\NavStack::push(true);
    }

    public function buildHeadIncludes()
    {
        parent::buildHeadIncludes();
        $this->headIncludeSS( \Framework\cdnURL('vendor/DataTables/datatables.css') );
    }

    public function buildFootIncludes()
    {
        parent::buildFootIncludes();
        $this->footIncludeJS( \Framework\cdnURL('vendor/DataTables/datatables.min.js') );
        $this->footIncludeJS( \Framework\siteURL('assets/js/prints-list.js') );
    }

    protected function buildContent()
    {
        //-- Call parent

            parent::buildContent();

        //-- Collect View Mode

            $this->viewMode = \App\Request::getOptionQP('view', VIEW_PRINTS_LIST, VIEW_PRINTS_SEPARATE);

            $this->showAll  = \App\Request::getBoolQP('showAll');

            // if we arenot viewing combined, then don't show all
            if ($this->viewMode != VIEW_PRINTS_COMBINED) {
                $this->showAll = false;
            }

        //-- Find all of the prints

            $params = array();

            $projectPrints = new \App\Table\ProjectPrintsList;

            $projectPrints->selectExpressions(
                array_merge(
                    $projectPrints->selectFieldList(inAsArray: true),
                    array(
                        'p.type AS project_type',
                        'p.year AS project_year',
                        'p.status AS project_status',
                        'p.created AS project_created',
                        'pf.file_name',
                        'pf.file_size',
                    )
                )
            );

            $projectPrints->joinClauses(array(
                    'LEFT JOIN ' . APP_MYSQL_DB . '.projects AS p ON p.id = pp.project_id',
                    'LEFT JOIN ' . APP_MYSQL_DB . '.project_files AS pf ON pf.id = pp.file_id',
            ));

            if (! $this->showAll) {
                $whereExpressions = array(
                    'p.status = :project_status AND (pp.status = :statusW OR pp.status = :statusP OR pp.status = :statusB)'
                );

                $projectPrints->whereExpressions($whereExpressions);

                $params = array(
                    'project_status' => PROJECT_STATUS_ACTIVE,
                    'statusW' => PROJECT_PRINT_STATUS_WAITING,
                    'statusP' => PROJECT_PRINT_STATUS_PRINTING,
                    'statusB' => PROJECT_PRINT_STATUS_PROBLEM,
                );
            }


            $projectPrints->orderBy('pp.is_reprint DESC, p.created');

            if (! $projectPrints->query($params)) {
                return false;
            }

            while ($projectPrints->next()) {
                $this->listRecords[] = $projectPrints->record();
            }

        //-- Find note counts for projects

            $projectNotes = new \App\Table\ProjectsList;

            $projectNotes->selectExpressions(
                array(
                    $projectNotes->asName() . '.id',
                    'count(pn.id) AS note_count',
                )
            );

            $projectNotes->joinClauses(array(
                'LEFT JOIN ' . APP_MYSQL_DB . '.project_notes AS pn ON pn.project_id = ' . $projectNotes->asName() . '.id'
            ));

            $whereExpressions = array(
                $projectNotes->asName() . '.status = :project_status',
            );

            $projectNotes->whereExpressions($whereExpressions);

            $projectNotes->groupBy($projectNotes->asName() . '.id');

            $params = array(
                'project_status' => PROJECT_STATUS_ACTIVE
            );

            if ($projectNotes->query($params)) {
                while ($projectNotes->next()) {
                    $this->noteCounts[$projectNotes->id] = $projectNotes->note_count;
                }
            }

        //-- Find filament counts for projects

            $projectFilaments = new \App\Table\ProjectsList;

            $projectFilaments->selectExpressions(
                array(
                    $projectFilaments->asName() . '.id',
                    'count(pf.id) AS filament_count',
                )
            );

            $projectFilaments->joinClauses(array(
                'LEFT JOIN ' . APP_MYSQL_DB . '.project_filaments AS pf ON pf.project_id = ' . $projectFilaments->asName() . '.id',
            ));

            $whereExpressions = array(
                $projectFilaments->asName() . '.status = :project_status',
            );

            $projectFilaments->whereExpressions($whereExpressions);

            $projectFilaments->groupBy($projectFilaments->asName() . '.id');

            $params = array(
                'project_status' => PROJECT_STATUS_ACTIVE
            );

            if ($projectFilaments->query($params)) {
                while ($projectFilaments->next()) {
                    $this->filamentCounts[$projectFilaments->id] = $projectFilaments->filament_count;
                }
            }

        return true;
    }

    protected function userHasAccess()
    {
        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
            return true;
        }
        return false;
    }
}

$page = new PrintsListsPage;
$page->render();

