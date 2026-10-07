<?php
/**
 * app/pages/projects/add/script.php
 *
 * Handles the process of adding a new project
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

define('PROJECT_TICKET_TYPE_WAIT_LIST',  0);
define('PROJECT_TICKET_TYPE_JOB_TICKET', 1);

include(APP_ROOT . 'app/lib/ProjectForm.php');

class ProjectAddPage extends \App\ProjectForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Add Project';
        $this->formTitle = 'Add Project';
        $this->postCollectID = false;
        $this->useTokenRecordID = false; 
    }

    protected function findRecord($inRecordID=false)
    {
        return true;
    }

    protected function canEdit()
    {
        return true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        //-- operation specific configuration

            $this->formVariables['customer_id']->required = true;
            $this->formVariables['email']->required = true;
            $this->formVariables['first_name']->required = true;
            $this->formVariables['last_name']->required = true;
            $this->formVariables['cancel_date']->optional = true;
            $this->formVariables['cancel_date']->optionalCallValidate = true;
            $this->formVariables['college_id']->required = true;
            $this->formVariables['printer_id']->required = true;
            $this->formVariables['category']->required = true;

            $this->formVariables['college_id']->loadSelectOptions();
            $this->formVariables['printer_id']->loadSelectOptions();
            $this->formVariables['category']->loadSelectOptions();

            $this->formVariables['is_affiliated'] = new \App\Form\Variable\IsAffiliated;
            $this->formVariables['is_affiliated']->required = true;

            $this->formVariables['ticket_type'] = new \App\Form\Variable\TicketType;
            $this->formVariables['ticket_type']->optional = true;

            $this->formVariables['ticket_number'] = new \App\Form\Variable\TicketNumber;
            $this->formVariables['ticket_number']->optional = true;

            $this->formVariables['stl_file'] = new \App\Form\Variable\STLFile;
            $this->formVariables['stl_file']->required = true;

            $this->formVariables['gcode_file'] = new \App\Form\Variable\GCodeFile;
            $this->formVariables['gcode_file']->required = true;

            $this->formVariables['filament_id'] = new \App\Form\Variable\FilamentID;
            $this->formVariables['filament_id']->loadSelectOptions(inIncludeColorSwap: true);
            $this->formVariables['filament_id']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['is_affiliated']->autoFocus = true;
    }

    protected function postValidate()
    {
        //-- Can only create projects for Unversity affiliated Student, Faculty, Staff

            if ($this->formVariables['is_affiliated']->value != VALUE_YES) {
                $this->formErrorMessage = CUSTOM_MESSAGE_001;
                return false;
            }

        //-- check that cancel date is in the fure if provided

            if ($this->formVariables['cancel_date']->ts != DEFAULT_UNDEFINED_DATE) {
                if ($this->formVariables['cancel_date']->ts < \time()) {
                    $this->formVariables['cancel_date']->setInvalid('Cancel date must be in the future');
                    return false;
                }
            }

        //-- make sure there is not already an open project using this customer id

            $projects = new \App\Table\ProjectsList();
            $projects->whereExpressions(array('customer_id = :customer_id'));

            $params = array(
                'customer_id' => $this->formVariables['customer_id']->value
            );

            if (! $projects->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_010;
                return false;
            }

            while ($projects->next()) {
                switch($projects->status) {
                    case PROJECT_STATUS_CLOSED:
                        break;
                    default:
                        $this->formErrorMessage = ERROR_MESSAGE_012 . ' (' . $projects->normalize('id') . ')';
                        return false;
                }
            }

        //-- make sure there is not already an open project using this email

            $projects = new \App\Table\ProjectsList();
            $projects->whereExpressions(array('email= :email'));

            $params = array(
                'email' => $this->formVariables['email']->value
            );

            if (! $projects->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_010;
                return false;
            }

            while ($projects->next()) {
                switch($projects->status) {
                    case PROJECT_STATUS_CLOSED:
                        break;
                    default:
                        $this->formErrorMessage = ERROR_MESSAGE_012 . ' (' . $projects->normalize('id') . ')';
                        return false;
                }
            }

        //-- make sure a print record does not already have that wait or job ticket number

            $prints = new \App\Table\ProjectPrintsList();

            //-- make sure we have a ticket number

                if (trim($this->formVariables['ticket_number']->value) == '') {
                    $this->formVariables['ticket_number']->setInvalid();
                    $this->formErrorMessage = ERROR_MESSAGE_013;
                    return false;
                }

            //-- make sure wait list # or Job # is specified

                switch ($this->formVariables['ticket_type']->value) {
                    case PROJECT_TICKET_TYPE_WAIT_LIST:
                        $prints->whereExpressions(array('wait_list_number = :wait_list_number'));
                        $params = array(
                            'wait_list_number' => $this->formVariables['ticket_number']->value
                        );
                        break;

                    case PROJECT_TICKET_TYPE_JOB_TICKET:
                        $prints->whereExpressions(array('job_ticket_number = :job_ticket_number'));
                        $params = array(
                            'job_ticket_number' => $this->formVariables['ticket_number']->value
                        );
                        break;

                    default:
                        $this->formVariables['ticket_type']->setInvalid();
                        $this->formErrorMessage = ERROR_MESSAGE_014;
                        return false;
                }

                if (! $prints->query($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_010;
                    return false;
                }

                if ($prints->count() > 0) {
                    switch ($this->formVariables['ticket_type']->value) {
                        case PROJECT_TICKET_TYPE_WAIT_LIST:
                            $this->formErrorMessage = ERROR_MESSAGE_015;
                            break;

                        case PROJECT_TICKET_TYPE_JOB_TICKET:
                            $this->formErrorMessage = ERROR_MESSAGE_016;
                            break;

                        default:
                            $this->formErrorMessage = ERROR_MESSAGE_010;
                    }

                    return false;
                }

        return true;
    }


    protected function scriptUpdate()
    {
        //-- pull in the gCode file

            if ($this->formVariables['gcode_file']->collected) {
                $gcodeFileContents = file_get_contents($this->formVariables['gcode_file']->fileTmpName);
                if ($gcodeFileContents === false) {
                    $this->formErrorMessage = ERROR_MESSAGE_017;
                    return false;
                }
            } else {
                $this->formErrorMessage = ERROR_MESSAGE_018;
                return false;
            }

        //-- pull in the STL file

            if ($this->formVariables['stl_file']->collected) {
                $stlFileContents = file_get_contents($this->formVariables['stl_file']->fileTmpName);
                if ($stlFileContents === false) {
                    $this->formErrorMessage = ERROR_MESSAGE_019;
                    return false;
                }
            } else {
                $this->formErrorMessage = ERROR_MESSAGE_020;
                return false;
            }

        //-- Add the project record

            $updateTime = \time();

            $params = array(
                'year'        => date('Y', \time()),
                'type'        => PROJECT_TYPE_3D_PRINT,
                'status'      => PROJECT_STATUS_ACTIVE,
                'customer_id' => $this->formVariables['customer_id']->value,
                'email'       => $this->formVariables['email']->value,
                'first_name'  => $this->formVariables['first_name']->value,
                'last_name'   => $this->formVariables['last_name']->value,
                'cancel_date' => $this->formVariables['cancel_date']->ts,
                'created'     => $updateTime,
                'college_id'  => $this->formVariables['college_id']->value,
                'category'    => $this->formVariables['category']->value
            );

            switch ($this->formVariables['ticket_type']->value) {

                case PROJECT_TICKET_TYPE_WAIT_LIST:
                    $projectPrintStatus = PROJECT_PRINT_STATUS_WAITING;
                    $waitTicket = trim($this->formVariables['ticket_number']->value);
                    $jobTicket = '';
                    break;

                case PROJECT_TICKET_TYPE_JOB_TICKET:
                    $projectPrintStatus = PROJECT_PRINT_STATUS_PRINTING;
                    $jobTicket = trim($this->formVariables['ticket_number']->value);
                    $waitTicket = '';
                    break;

                default:
                    $this->formErrorMessage = ERROR_MESSAGE_014;
                    return false;

            }

            $project = new \App\Table\ProjectsRecord();

            if (! $project->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_010;
                return false;
            }

            $newProjectID = $project->lastInsertID();

        //-- Store the STL file

            $updateTime++;

            $projectFile = new \App\Table\ProjectFilesRecord();

            $params = array(
                'project_id' => $newProjectID,
                'status'     => PROJECT_FILE_STATUS_ENABLED, 
                'file_name'  => $this->formVariables['stl_file']->value,
                'file_type'  => PROJECT_FILE_TYPE_STL,
                'file_size'  => $this->formVariables['stl_file']->fileSize,
                'file_blob'  => base64_encode($stlFileContents),
                'uploaded'   => $updateTime,
            );

            if (! $projectFile->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_021;
                return false;
            }

        //-- Store the gCode file

            $updateTime++;

            $params = array(
                'project_id' => $newProjectID,
                'status'     => PROJECT_FILE_STATUS_ENABLED, 
                'file_name'  => $this->formVariables['gcode_file']->value,
                'file_type'  => PROJECT_FILE_TYPE_GCODE,
                'file_size'  => $this->formVariables['gcode_file']->fileSize,
                'file_blob'  => base64_encode($gcodeFileContents),
                'uploaded'   => $updateTime,
            );

            if (! $projectFile->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_021;
                return false;
            }

            $gCodeFileID = $projectFile->lastInsertID();

        //-- add the project filament (if not color swap)

            if ($this->formVariables['filament_id']->value != 'c') {
                $projectFilament = new \App\Table\ProjectFilamentsRecord();

                $params = array(
                    'project_id'     => $newProjectID,
                    'filament_id'    => $this->formVariables['filament_id']->value,
                    'filament_order' => 1
                );

                if (! $projectFilament->insert($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_010;
                    return false;
                }
            }

        //-- create the print ticket

            $updateTime++;

            $projectPrint = new \App\Table\ProjectPrintsRecord();

            $params = array(
                'project_id'        => $newProjectID,
                'printer_id'        => $this->formVariables['printer_id']->value,
                'file_id'           => $gCodeFileID,
                'status'            => $projectPrintStatus,
                'created'           => $updateTime,
                'wait_list_number'  => $waitTicket,
                'job_ticket_number' => $jobTicket
            );

            if (! $projectPrint->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_010;
                return false;
            }

        //-- redirect

            $this->redirectPage  = 'projects/view?id=' . $newProjectID;

            if ($this->formVariables['filament_id']->value == 'c') {
                $this->redirectPage .= '&tab=filaments';
            }

            return true;
    }
}

$page = new ProjectAddPage;
$page->process();


