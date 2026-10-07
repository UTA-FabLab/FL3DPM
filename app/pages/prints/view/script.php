<?php
/**
 * app/pages/prints/view/script.php
 *
 * Handles the process of viewing a print job
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

class PrintsViewPage extends \App\Page
{
    protected $projectPrint;
    protected $project;
    protected $projectPrintFile;
    protected $projectFilaments;
    protected $projectPrintProblems;
    protected $projectNotes;
    protected $jobTicketURL;

    protected $currentTab;

    public function __construct()
    {
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Prints - View';
        $this->formTitle = 'Prints - View';
        $this->footIncludeJS(\Framework\siteURL('assets/js/print-view.js'));
        $this->jobTicketURL = \Middleware\getConfigValue('FABAPP_JOB_TICKET_URL');
    }


    protected function buildContent()
    {
        //-- see if we have a selected tab

            $this->currentTab = \App\Request::getQP('tab');

            switch($this->currentTab) {
                case 'contact':
                case 'filaments':
                case 'file':
                case 'problems':
                case 'status':
                case 'notes':
                    break;

                default:
                    $this->currentTab = 'status';
            }

        //-- collect the project id number

            $projectPrintID = \App\Request::getQP('id');
            if ($projectPrintID === false) {
                return false;
            }

            $projectPrintID = trim($projectPrintID);
            if ($projectPrintID == '') {
                return false;
            }

        //-- Find the project print record 
 
            $this->projectPrint = new \App\Table\ProjectPrintsRecord;
            if (! $this->projectPrint->findByID($projectPrintID)) {
                return false;
            }

        //-- Find the project record
 
            $this->project = new \App\Table\ProjectsRecord;
            if (! $this->project->findByID($this->projectPrint->project_id)) {
                return false;
            }

        //-- Find the project print file

            $this->projectPrintFile = new \App\Table\ProjectFilesRecord();
            if (! $this->projectPrintFile->findByID($this->projectPrint->file_id)) {
                return false;
            }

        //-- Find the projects filaments

            $this->projectFilaments = new \App\Table\ProjectFilamentsList;

            $this->projectFilaments->joinClauses(array(
                'LEFT JOIN filaments AS f ON f.id = ' . $this->projectFilaments->asName() . '.filament_id',
            ));

            $this->projectFilaments->selectExpressions(
                array_merge(
                    $this->projectFilaments->selectFieldList(inAsArray: true),
                    array(
                        'f.name',
                    )
                )
            );

            $this->projectFilaments->whereExpressions(array($this->projectFilaments->asName() . '.project_id = :project_id'));
            $this->projectFilaments->orderBy('filament_order ASC');

            $params = array(
                'project_id' => $this->project->id
            );

            $this->projectFilaments->query($params);

        //-- Find the projects print problems

            $this->projectPrintProblems = new \App\Table\ProjectPrintProblemsList();
 
            $this->projectPrintProblems->whereExpressions(array($this->projectPrintProblems->asName() . '.print_id = :print_id'));
            $this->projectPrintProblems->orderBy('action_needed_date ASC');
 
            $params = array(
                'print_id' => $this->projectPrint->id
            );

            $this->projectPrintProblems->query($params);

        //-- find the notes
 
            $this->projectNotes = new \App\Table\ProjectNotesList;

            $this->projectNotes->joinClauses(array(
                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' .APP_MYSQL_IDENTITY_TABLE . ' AS i ON i.id = ' . $this->projectNotes->asName() . '.posted_by',
                'LEFT JOIN ' . APP_MYSQL_DB . '.project_prints AS pp ON pp.id = ' . $this->projectNotes->asName() . '.print_id'
            ));

            $this->projectNotes->selectExpressions(
                array_merge(
                    $this->projectNotes->selectFieldList(inAsArray: true),
                    array(
                        'i.' . APP_MYSQL_IDENTITY_FIRST_NAME,
                        'i.' . APP_MYSQL_IDENTITY_LAST_NAME,
                        'pp.wait_list_number',
                        'pp.job_ticket_number',
                        'pp.status',
                        'pp.cancel_reason',
                    )
                )
            );

            $this->projectNotes->whereExpressions(array($this->projectNotes->asName() . '.project_id = :project_id'));
            $this->projectNotes->orderBy('posted DESC');

            $params = array(
                'project_id' => $this->projectPrint->project_id
            );

            $this->projectNotes->query($params);

        //-- push the url on the stack

            \Framework\NavStack::push();

        return true;
    }

    protected function userHasAccess()
    {
        if (! \App\Request::$user->isRealUser()) {
            return false;
        }

        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
            return true;
        }

        return false;
    }

}

$page = new PrintsViewPage;
$page->render();

