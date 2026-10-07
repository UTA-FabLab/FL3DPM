<?php
/**
 * app/lib/Tables.php
 *
 * Contains the tables classes specific to the applications
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App\Table;


//------------------------------------------------------------------------------
//-- Colleges Interface
//------------------------------------------------------------------------------

    class CollegesFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'              => 'College ID',
            'college'         => 'College Name',
            'enabled'         => 'Enabled',
        );
    }

    class CollegesTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'colleges';
            $this->asName = 'c';
            $this->indexField = 'id';
            $this->relatedField = 'id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_COLLEGES;
        }

        public function getFieldNames()
        {
            return CollegesFieldNames::getKeys();
        }

        protected function enabledNormalize($inValue = false)
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return \App\Normalize::yes_no(($inValue == false) ? $record['enabled'] : $inValue);
        }
    }

    class CollegesList extends CollegesTable
    {
        use \Framework\TableListInterface;
    }

    class CollegesRecord extends CollegesTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        public function findByCollege($inCollege)
        {
            $this->whereExpressions[] = $this->asName . '.college = :college';
            return $this->findRecord($this->fetchSQL(), array('college' => $inCollege));
        }

    }



//------------------------------------------------------------------------------
//-- Filaments Interface
//------------------------------------------------------------------------------

    class FilamentsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'      => 'Filament ID',
            'name'    => 'Name',
            'enabled' => 'Enabled',
        );
    }

    class FilamentsTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'filaments';
            $this->asName = 'f';
            $this->indexField = 'id';
            $this->relatedField = 'id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_FILAMENTS;
        }

        public function getFieldNames()
        {
            return FilamentsFieldNames::getKeys();
        }

        protected function enabledNormalize($inValue = false)
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return \App\Normalize::yes_no(($inValue == false) ?  $record['enabled'] : $inValue);
        }

    }

    class FilamentsList extends FilamentsTable
    {
        use \Framework\TableListInterface;
    }

    class FilamentsRecord extends FilamentsTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        public function findByName($inName)
        {
            $this->whereExpressions[] = $this->asName . '.name = :name';
            return $this->findRecord($this->fetchSQL(), array('name' => $inName));
        }

    }



//------------------------------------------------------------------------------
//-- Printers Interface
//------------------------------------------------------------------------------

    class PrintersFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'      => 'Printer ID',
            'name'    => 'Name',
            'enabled' => 'Enabled',
        );
    }

    class PrintersTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'printers';
            $this->asName = 'p';
            $this->indexField = 'id';
            $this->relatedField = 'id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PRINTERS;
        }

        public function getFieldNames()
        {
            return PrintersFieldNames::getKeys();
        }

        protected function enabledNormalize($inValue = false)
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return \App\Normalize::yes_no(($inValue == false) ? $record['enabled'] : $inValue);
        }

    }

    class PrintersList extends PrintersTable
    {
        use \Framework\TableListInterface;
    }

    class PrintersRecord extends PrintersTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        public function findByName($inName)
        {
            $this->whereExpressions[] = $this->asName . '.name = :name';
            return $this->findRecord($this->fetchSQL(), array('name' => $inName));
        }

    }



//------------------------------------------------------------------------------
//-- Projects Interface
//------------------------------------------------------------------------------

    class ProjectsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'            => 'Project ID',
            'year'          => 'Project Year',
            'type'          => 'Project Type',
            'status'        => 'Project Status',
            'cancel_reason' => 'Cancel Reason',
            'customer_id'   => 'Customer ID',
            'is_student'    => 'Is Student?',
            'email'         => 'Email',
            'first_name'    => 'First Name',
            'last_name'     => 'Last Name',
            'created'       => 'Created',
            'cancel_date'   => 'Cancel Date',
            'price'         => 'Price',
            'college_id'    => 'Collect ID',
            'category'      => 'Category',
        );
    }

    class ProjectsTable extends \Framework\TableInterface
    {
        protected $activePrintStatus;
        protected $activePrintID;

        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'projects';
            $this->asName = 'pr';
            $this->indexField = 'id';
            $this->relatedField = 'id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECTS;
            $this->activePrintStatus = false;
            $this->activePrintID = 0;
        }

        public function getFieldNames()
        {
            return ProjectsFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            //-- since this is using 3 fields to normalize, we cannot use the inValue
            public function idNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\Normalize::projectID($record['type'], $record['year'], $record['id']);
            }

            public function typeNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectTypes::getText(($inValue === false ? $record['type'] : $inValue));
            }

            public function statusNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectStatuses::getText(($inValue === false ? $record['status'] : $inValue));
            }

            public function cancel_reasonNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\CancelReasons::getText(($inValue === false ? $record['cancel_reasons'] : $inValue));
            }

            public function createdNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \Framework\Normalize::dateYMD(($inValue === false ? $record['created'] : $inValue));
            }

            public function cancel_dateNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);

                if ($inValue === false) {
                    $inValue = $record['cancel_date'];
                }

                if ($inValue == DEFAULT_UNDEFINED_DATE) {
                    return 'N/A';
                }

                return \Framework\Normalize::dateYMD($inValue);
            }

            public function is_studentNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \Framework\Normalize::yes_no(($inValue == false) ? $record['is_student'] : $inValue);
            }

            public function categoryNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectCategories::getText(($inValue == false) ?  $record['category'] : $inValue);
            }

            public function college_idNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                if ($inValue === false) {
                    $inValue = $record['college_id'];
                }
                if (is_int($inValue)) {
                    $inValue = intval($inValue);
                }
                if ($inValue < 1) {
                    return 'unknown';
                }
                $college = new \App\Table\CollegesRecord;
                if (! $college->findByID($inValue)) {
                    return 'unknown';
                }
                return $college->college;
            }

        //----------------------------------------------------------------------
        //-- Can perform functions

            public function canAddProject()
            {
                if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
                    return true;
                }

                return false;
            }

            public function canEditProject()
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);

                if ($record === false) {
                    return false;
                }

                switch($record['status']) {
                    case PROJECT_STATUS_ACTIVE:
                        break;

                    default:
                        return false;
                }

                if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
                    return true;
                }

                return false;
            }

            public function canManageFiles()
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);

                if ($record === false) {
                    return false;
                }

                switch($record['status']) {
                    case PROJECT_STATUS_ACTIVE:
                        break;
                    default:
                        return false;
                }

                switch($this->activePrintStatus()) {
                    case PROJECT_PRINT_STATUS_WAITING;
                    case PROJECT_PRINT_STATUS_PROBLEM;
                        return true;
                }

                return false;
            }

            public function canManageFilaments()
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);

                if ($record === false) {
                    return false;
                }

                switch($record['status']) {
                    case PROJECT_STATUS_ACTIVE:
                        break;
                    default:
                        return false;
                }

                switch($this->activePrintStatus()) {
                    case PROJECT_PRINT_STATUS_WAITING;
                    case PROJECT_PRINT_STATUS_PROBLEM;
                        return true;
                }

                return false;
            }


        //----------------------------------------------------------------------
        //-- Find active print status for a project

            public function activePrintStatus()
            {
                //-- If we already have the status, return

                    if ($this->activePrintID > 0) {
                        return $this->activePrintStatus;
                    }

                //-- Must have active record

                    if ($this->record === false) {
                        return PROJECT_PRINT_STATUS_UNDEFINED;
                    }

                //-- Find the projects prints

                    $projectPrints = new \App\Table\ProjectPrintsList;

                    $projectPrints->whereExpressions(array('pp.project_id = :project_id'));

                    $projectPrints->orderBy('created DESC');

                    $projectPrints->limitRows(1);

                    $params = array(
                        'project_id' => $this->record['id']
                    );

                    $projectPrints->query($params);

                    if ($projectPrints->count() == 0) {
                        $this->activePrintStatus = PROJECT_PRINT_STATUS_UNDEFINED;
                    } else {
                        $projectPrints->next();
                        $this->activePrintID     = $projectPrints->id;
                        $this->activePrintStatus = $projectPrints->status;
                    }

                    return $this->activePrintStatus;
            }

            public function activePrintID()
            {
                $this->activePrintStatus();
                return $this->activePrintID;
            }
    }

    class ProjectsList extends ProjectsTable
    {
        use \Framework\TableListInterface;
    }

    class ProjectsRecord extends ProjectsTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

    }



//------------------------------------------------------------------------------
//-- ProjectFilaments Interface
//------------------------------------------------------------------------------

    class ProjectFilamentsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'             => 'Project Filament ID',
            'project_id'     => 'Project ID',
            'filament_id'    => 'Filament ID',
            'filament_order' => 'Filament Order',
        );
    }

    class ProjectFilamentsTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'project_filaments';
            $this->asName = 'pf';
            $this->indexField = 'id';
            $this->relatedField = 'project_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECT_FILAMENTS;
        }

        public function getFieldNames()
        {
            return ProjectFilamentsFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            public function filament_idNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                if ($inValue === false) {
                    $inValue = $record['filament_id'];
                }
                if (is_int($inValue)) {
                    $inValue = intval($inValue);
                }
                if ($inValue < 1) {
                    return 'unknown';
                }
                $filament = new \App\Table\FilamentsRecord;
                if (! $filament->findByID($inValue)) {
                    return 'unknown';
                }
                return $filament->name;
            }

    }

    class ProjectFilamentsList extends ProjectFilamentsTable
    {
        use \Framework\TableListInterface;

        public function fetchSQLWithName()
        {
            $sql = 'SELECT ' . $this->selectFieldList();
            $sql .= ',f.name AS filament_name ';
            $sql .= $this->sqlFromClause();
            $sql .= ' JOIN ' . APP_MYSQL_DB . '.filaments AS f ON r.filament_id = f.id ';
            return $sql;
        }

        public function findByProjectID($inProjectID)
        {
            $this->whereExpressions[] = $this->asName . '.project_id = :project_id';
            return $this->query(array('project_id' => $inProjectID));
        }

    }

    class ProjectFilamentsRecord extends ProjectFilamentsTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->deleteAllowed = true;
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

    }



//------------------------------------------------------------------------------
//-- ProjectFiles Interface
//------------------------------------------------------------------------------

    class ProjectFilesFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'         => 'Project File ID',
            'project_id' => 'Project ID',
            'status'     => 'File Status',
            'file_name'  => 'File Name',
            'file_type'  => 'File Type',
            'file_size'  => 'File Size',
            'file_blob'  => 'File Blob',
            'uploaded'   => 'File Uploaded',
        );
    }

    class ProjectFilesTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'project_files';
            $this->asName = 'pr';
            $this->indexField = 'id';
            $this->relatedField = 'project_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECT_FILES;
        }

        public function getFieldNames()
        {
            return ProjectFilesFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            public function statusNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectFileStatuses::getText(($inValue === false ? $inValue = $record['status'] : $inValue));
            }

            public function file_sizeNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \Framework\decimalByteFormat(($inValue === false ? $inValue = $record['file_type'] : $inValue));
            }

            public function file_typeNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectFileTypes::getText(($inValue === false ? $inValue = $record['file_type'] : $inValue));
            }

            public function uploadedNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\Normalize::date_time(($inValue === false ? $inValue = $record['uploaded'] : $inValue));
            }
    }


    class ProjectFilesList extends ProjectFilesTable
    {
        use \Framework\TableListInterface;

        public function findByProjectID($inProjectID)
        {
            $this->whereExpressions[] = $this->asName . '.project_id = :project_id';
            return $this->query(array('project_id' => $inProjectID));
        }
    }

    class ProjectFilesRecord extends ProjectFilesTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        protected function filterLogFields(&$inFields)
        {   
            unset($inFields['file_blob']);
        }

    }



//------------------------------------------------------------------------------
//-- ProjectNotes Interface
//------------------------------------------------------------------------------

    class ProjectNotesFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'         => 'Project Note ID',
            'project_id' => 'Project ID',
            'print_id'   => 'Print ID',
            'note'       => 'Note',
            'posted'     => 'Posted',
            'posted_by'  => 'Posted By',

        );
    }

    class ProjectNotesTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'project_notes';
            $this->asName = 'pn';
            $this->indexField = 'id';
            $this->relatedField = 'project_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECT_NOTES;
        }

        public function getFieldNames()
        {
            return ProjectNotesFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            public function postedNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \Framework\Normalize::dateYMD(($inValue === false ? $record['posted'] : $inValue));
            }
    }

    class ProjectNotesList extends ProjectNotesTable
    {
        use \Framework\TableListInterface;
    }

    class ProjectNotesRecord extends ProjectNotesTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

    }



//------------------------------------------------------------------------------
//-- ProjectPrints Interface
//------------------------------------------------------------------------------

    class ProjectPrintsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'                  => 'Project Print ID',
            'project_id'          => 'Project ID',
            'printer_id'          => 'Printer ID',
            'file_id'             => 'File ID',
            'status'              => 'Print Status',
            'cancel_reason'       => 'Cancel Reason',
            'created'             => 'Created',
            'wait_list_number'    => 'Wait List #',
            'job_ticket_number'   => 'Job Ticket #',
            'is_reprint'          => 'Is Reprint?',
        );
    }

    class ProjectPrintsTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'project_prints';
            $this->asName = 'pp';
            $this->indexField = 'id';
            $this->relatedField = 'project_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECT_PRINTS;
        }

        public function getFieldNames()
        {
            return ProjectPrintsFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            public function statusNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\ProjectPrintStatuses::getText(($inValue === false ? $inValue = $record['status'] : $inValue));
            }

            public function createdNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\Normalize::date_time(($inValue === false ? $inValue = $record['created'] : $inValue));
            }

            public function is_reprintNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \App\Normalize::yes_no(($inValue == false) ?  $record['is_reprint'] : $inValue);
            }

            public function file_sizeNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);
                return \Framework\decimalByteFormat( ($inValue == false) ?  $record['file_size'] : $inValue);
            }

            public function printer_idNormalize($inValue = false)
            {
                $record = ($this->record === NULL ? $this->record() : $this->record);

                if ($inValue === false) {
                    $inValue = $record['printer_id'];
                }

                $printer = new \App\Table\PrintersRecord;
                if ($printer->findByID($inValue)) {
                    return $printer->name;
                }

                return 'unknown';
            }


    }

    class ProjectPrintsList extends ProjectPrintsTable
    {
        use \Framework\TableListInterface;

    }

    class ProjectPrintsRecord extends ProjectPrintsTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        public function findByWaitList($inWaitListNumber)
        {
            $this->whereExpressions[] = $this->asName . '.wait_list_number = :wait_list_number';
            return $this->findBy(array('wait_list_number' => $inWaitListNumber));
        }

        public function findByJobTicket($inJobTicketNumber)
        {
            $this->whereExpressions[] = $this->asName . '.job_ticket_number = :job_ticket_number';
            return $this->findBy(array('job_ticket_number' => $inJobTicketNumber));
        }
    }



//------------------------------------------------------------------------------
//-- Project Print Problems Interfaces
//------------------------------------------------------------------------------

    class ProjectPrintProblemsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'                  => 'Project Print Problem ID',
            'print_id'            => 'Print ID',
            'problem'             => 'Print Problem',
            'action_needed'       => 'Action Needed',
            'action_needed_date'  => 'Action Needed Reported',
            'action_taken'        => 'Action Taken',
            'action_taken_date'   => 'Action Taken Completed'
        );
    }


    class ProjectPrintProblemsTableInterface extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'project_print_problems';
            $this->asName = 'ppp';
            $this->indexField = 'id';
            $this->relatedField = 'print_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_FL3DPM_PROJECT_PRINT_PROBLEMS;
        }


        public function getFieldNames()
        {
            return ProjectPrintProblemsFieldNames::getKeys();
        }

        //----------------------------------------------------------------------
        //-- Normalization functions

            public function problemNormalize($inValue = false)
            {
                return \App\ProjectPrintProblems::getText(($inValue === false ? $this->_record['problem'] : $inValue));
            }

            public function action_neededNormalize($inValue = false)
            {
                return \App\ProjectPrintActionNeeded::getText(($inValue === false ? $this->_record['action_needed'] : $inValue));
            }

            public function action_needed_dateNormalize($inValue = false)
            {
                return \App\Normalize::date_time(($inValue === false ? $this->_record['action_needed_date'] : $inValue));
            }

            public function action_takenNormalize($inValue = false)
            {
                return \App\ProjectPrintActionTaken::getText(($inValue === false ? $this->_record['action_taken'] : $inValue));
            }

            public function action_taken_dateNormalize($inValue = false)
            {
                return \App\Normalize::date_time(($inValue === false ? $this->_record['action_taken_date'] : $inValue));
            }
    }


    class ProjectPrintProblemsRecord extends ProjectPrintProblemsTableInterface
    {
        use \Framework\TableRecordInterface;
   
        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }
    }


    class ProjectPrintProblemsList extends ProjectPrintProblemsTableInterface
    {
        use \Framework\TableListInterface;
    }

 
