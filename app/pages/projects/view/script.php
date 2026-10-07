<?php
/**
 * app/pages/projects/view/script.php
 *
 * Handles the process of viewing a project
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

class ProjectsViewPage extends \App\Page
{
    protected $project;
    protected $projectFiles;
    protected $projectFilaments;
    protected $projectPrints;
    protected $projectNotes;
    protected $projectHistory;
    protected $currentTab;

    public function __construct()
    {
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Projects - View';
        $this->formTitle = 'View Project';
        $this->footIncludeJS(\Framework\siteURL('assets/js/project-view.js'));
    }


    protected function buildContent()
    {
        //-- Pull in the project ID

            $projectID = \App\Request::getQP('id');

            if (! is_numeric($projectID)) {
                return false;
            }

        //-- see if we have a selected tab

            $this->currentTab = \App\Request::getQP('tab');

            switch($this->currentTab) {

                case 'filaments':
                case 'files':
                case 'notes':
                case 'prints':
                case 'status':
                case 'history':
                    break;

                default:
                    $this->currentTab = 'status';
            }

        //-- Find the project record 

            $this->project = new \App\Table\ProjectsRecord;

            if (! $this->project->findByID($projectID)) {
                return false;
            }

        //-- Find the projects files

            $this->projectFiles = new \App\Table\ProjectFilesList;
            $this->projectFiles->whereExpressions(array('project_id = :project_id'));
            $this->projectFiles->orderBy('uploaded DESC, status');

            $params = array(
                'project_id' => $projectID
            );

            $this->projectFiles->query($params);

        //-- Find the projects filaments

            $this->projectFilaments = new \App\Table\ProjectFilamentsList;

            $this->projectFilaments->selectExpressions(
                array_merge(
                    $this->projectFilaments->selectFieldList(inAsArray: true),
                    array(
                        'f.name',
                    )
                )
            );

            $this->projectFilaments->joinClauses(array(
                'LEFT JOIN filaments AS f ON f.id = ' . $this->projectFilaments->asName() . '.filament_id',
            ));

            $this->projectFilaments->whereExpressions(array($this->projectFilaments->asName() . '.project_id = :project_id'));

            $this->projectFilaments->orderBy('filament_order ASC');

            $params = array(
                'project_id' => $projectID
            );

            $this->projectFilaments->query($params);


        //-- Find the projects prints

            $this->projectPrints = new \App\Table\ProjectPrintsList;

            $this->projectPrints->selectExpressions(
                array_merge(
                    $this->projectPrints->selectFieldList(inAsArray: true),
                    array(
                        'pf.file_name',
                        'pf.file_type',
                        'pf.file_size',
                    )
                )
            );

            $this->projectPrints->joinClauses(array(
                'LEFT JOIN project_files AS pf ON pp.file_id = ' . $this->projectPrints->asName() . '.id',
            ));

            $this->projectPrints->whereExpressions(array($this->projectPrints->asName() . '.project_id = :project_id'));
            $this->projectPrints->orderBy('created DESC');

            $params = array(
                'project_id' => $projectID
            );

            $this->projectPrints->query($params);


        //-- Find the notes

            $this->projectNotes = new \App\Table\ProjectNotesList;

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

            $this->projectNotes->joinClauses(array(
                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . ' AS i ON i.id = ' . $this->projectNotes->asName() . '.posted_by',
                'LEFT JOIN ' . APP_MYSQL_DB . '.project_prints AS pp ON pp.id = ' . $this->projectNotes->asName() . '.print_id',
            ));

            $this->projectNotes->whereExpressions(array($this->projectNotes->asName() . '.project_id = :project_id'));
            $this->projectNotes->orderBy('posted DESC');

            $params = array(
                'project_id' => $projectID,
            );

            $this->projectNotes->query($params);


        //-- find the history

            $changeLog = new \App\ChangeLog;

            $this->projectHistory = $changeLog->getLogEntriesByRelated(array(
                    LOG_TABLE_FL3DPM_PROJECTS,
                    LOG_TABLE_FL3DPM_PROJECT_FILAMENTS,
                    LOG_TABLE_FL3DPM_PROJECT_FILES,
                    LOG_TABLE_FL3DPM_PROJECT_PRINTS,
                    LOG_TABLE_FL3DPM_PROJECT_PRINT_PROBLEMS
                ), 
                $projectID);

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

$page = new ProjectsViewPage;
$page->render();

