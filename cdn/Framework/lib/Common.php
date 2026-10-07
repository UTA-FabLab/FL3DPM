<?php
/**
 * Framework/lib/Common.php
 *
 * Contains Framework procedures and classes
 *
 * @package Framework
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace Framework;


//---------------------------------------------------------------------------------
//-- Defines Change Log Types
//--
//-- Variable state for storing and viewing change log records
//---------------------------------------------------------------------------------

    define('CHANGE_LOG_TYPE_ADD',            'A');
    define('CHANGE_LOG_TYPE_CHANGE',         'C');
    define('CHANGE_LOG_TYPE_DELETE',         'D');
    define('CHANGE_LOG_TYPE_EDIT',           'E');
    define('CHANGE_LOG_TYPE_IMPORT_ADD',     'IA');
    define('CHANGE_LOG_TYPE_IMPORT_UPDATE',  'IU');
    define('CHANGE_LOG_TYPE_NOTE',           'N');
    define('CHANGE_LOG_TYPE_UNKNOWN',        'U');

    // NOTE: this class needs to be extended in the application to be used.
    //       functions below will reference \App\ChangeLogTypes;
    class ChangeLogTypes
    {
        use \Framework\VariableState;

        protected static $values = array(
            CHANGE_LOG_TYPE_ADD           => array('text' => 'Add',            'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_CHANGE        => array('text' => 'Change',         'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_DELETE        => array('text' => 'Delete',         'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_EDIT          => array('text' => 'Edit',           'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_IMPORT_ADD    => array('text' => 'Import Add',     'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_IMPORT_UPDATE => array('text' => 'Import Update',  'selectable' => true, 'assignable' => true),
            CHANGE_LOG_TYPE_NOTE          => array('text' => 'Note',           'selectable' => true, 'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Defines Change Log -- Log Record Types
//------------------------------------------------------------------------------

    define('LOG_RECORD_TYPE_FIELD_LIST', 'F');
    define('LOG_RECORD_TYPE_NOTE',       'N');


//------------------------------------------------------------------------------
//-- Class to manage the process of logging and viewing changes
//--   note: must be extended
//------------------------------------------------------------------------------

    class ChangeLog
    {
        protected $tableName;
        protected $debugOn;
    
        //----------------------------------------------------------------------
        //-- Constructor
    
            public function __construct()
            {
                //-- this should be set to the string value of a table ex: testapp.change_log
                $this->tableName = false;
            }

            public function debugOn()
            {
                $this->debugOn = true;
            }
    

        //----------------------------------------------------------------------
        //-- Adds a change log entry to the change log for fields that have
        //-- been inserted, updated or deleted
        //--
        //-- inLogType       - the change log type (see definitions above)
        //-- inForTable      - Log Table Identifier
        //-- inTableKey      - key to record being changed (string)
        //-- inRelatedKeyID  - key to related record being changed (string)
        //--                   ex: id of the record who's information is being
        //--                   changed (ex: department being changed, could be 0)
        //--                   this allows for finding all records for a given
        //--                   record if the inTableKey is not the record's ID
        //-- inFields        - array of fields
        //-- inFileBlob      - file blob that will be base64 encoded (if not empty)
    
            public function logFields(string $inLogType, string $inForTable, string $inTableKey, string $inRelatedKey, array $inFields, string $inFileBlob = '')
            {
                if (! is_string($this->tableName)) {
                    if ($this->debugOn) print '[ChangeLog:logFields tableName not string]';
                    return false;
                }
    
                if (! \App\ChangeLogTypes::valid($inLogType)) {
                    if ($this->debugOn) print '[ChangeLog:logFields inLogType not valid]';
                    return false;
                }
    
                if (defined('APP_CRON')) { 
                    $userID = 0;
                    $remoteAddr = 'localhost:cron';
                } else {
                    $userID = \App\Request::$user->userID;
                    $remoteAddr = \Framework\clientIP();
                    if ($remoteAddr === false) {
                        if ($this->debugOn) print '[ChangeLog:logFields remoteAddr false]';
                        return false;
                    }
                }
   
                $logRecord = array(
                    'type'      => LOG_RECORD_TYPE_FIELD_LIST,
                    'logType'   => $inLogType,
                    'fields'    => $inFields,
                    'client_ip' => $remoteAddr
                );
    
                $query = 'INSERT INTO ' . $this->tableName . '(' .
                            'for_table,' .
                            'table_key,' .
                            'related_key,' .
                            'changed_by,' .
                            'log_type,' .
                            'log_data, ' .
                            'timestamp' .
                        ') VALUES (' .
                            ':for_table,' .
                            ':table_key,' .
                            ':related_key,' .
                            ':changed_by,' .
                            ':log_type,' .
                            ':log_data,' .
                            ':timestamp' .
                        ')';
    
                $logParams = array(
                    'for_table'    => $inForTable,
                    'table_key'    => $inTableKey,
                    'related_key'  => $inRelatedKey,
                    'changed_by'   => $userID,
                    'log_type'     => $inLogType,
                    'log_data'     => json_encode($logRecord),
                    'timestamp'    => time()
                );

                if (\App\Request::$db->execute($query, $logParams)) {
                    return true;
                }

                if ($this->debugOn) {
                    print '[ChangeLog:logFields failed db execute]';
                }

                return false;
            }
    
    
        //----------------------------------------------------------------------
        //-- Adds a note log entry to the change log
        //--
        //-- inForTable      - Log Table Identifier
        //-- inTableKey      - key to record being changed (string)
        //-- inRelatedKeyID  - key to related record being changed (string)
        //--                   ex: id of the record who's information is being
        //--                   changed (ex: department being changed, could be 0)
        //--                   this allows for finding all records for a given
        //--                   record if the inTableKey is not the record's ID
        //-- inNote          - string to log
    
            public function logNote(string $inForTable, string $inTableKey, string $inRelatedKey, string $inNote)
            {
                if (! is_string($this->tableName)) {
                    return false;
                }
    
                if (! is_string($inForTable)) {
                    return false;
                }
    
                $remoteAddr = \Framework\clientIP();
    
                if ($remoteAddr === false) {
                    return false;
                }
    
                $logRecord = array(
                    'type'      => LOG_RECORD_TYPE_NOTE,
                    'logType'   => \App\ChangeLogTypes::getText(CHANGE_LOG_TYPE_NOTE),
                    'note'      => $inNote,
                    'client_ip' => $remoteAddr
                );
    
                $query = 'INSERT INTO ' . $this->tableName . '(' .
                            'for_table,' .
                            'table_key,' .
                            'related_key,' .
                            'changed_by,' .
                            'log_type,' .
                            'log_data, ' .
                            'timestamp' .
                        ') VALUES (' .
                            ':for_table,' .
                            ':table_key,' .
                            ':related_key,' .
                            ':changed_by,' .
                            ':log_type,' .
                            ':log_data,' .
                            ':timestamp' .
                        ')';
    
                $logParams = array(
                    'for_table'    => $inForTable,
                    'table_key'    => $inTableKey,
                    'related_key'  => $inRelatedKey,
                    'changed_by'   => \App\Request::$user->userID,
                    'log_type'     => CHANGE_LOG_TYPE_NOTE,
                    'log_data'     => json_encode($logRecord),
                    'timestamp'    => time()
                );
    
                return \App\Request::$db->execute($query, $logParams);
            }

    
        //----------------------------------------------------------------------
        //-- Retrieves log entries by related key
        //--
        //-- inRelatedKey - key ID related to record that was created
        //-- inForTable   - Log Table Identifier
    
            function getLogEntriesByRelated(array $inForTable, string $inRelatedKey)
            {
                if (count($inForTable) == 0) {
                    return array();
                }

                if (count($inForTable) > 1) {
                    $params = array_merge(array($inRelatedKey), $inForTable);

                    $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . 
                                    ' as i ON l.changed_by = i.id ' .
                                'WHERE l.related_key = ? AND ' .
                                'l.for_table IN ('.str_pad('',count($inForTable)*2-1,'?,').') ' .
                                'ORDER BY timestamp DESC';
                } else {
                    $params = array(
                        'for_table'   => $inForTable[0],
                        'related_key' => $inRelatedKey
                    );
    
                    $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . 
                                    ' as i ON l.changed_by = i.id ' .
                                'WHERE l.for_table = :for_table AND l.related_key = :related_key ' .
                                'ORDER BY timestamp DESC';
                }
  
                return \App\Request::$db->fetchAll($query, $params);
            }
    
   

        //----------------------------------------------------------------------
        //-- Retrieves log entries for a table (or by relation)
        //--
        //-- inForTable      - Log Table Identifier
        //-- inKey           - Table Key ID
        //-- inRelatedTables - array of related tables

            public function getLogEntriesByTable(string $inForTable, string $inKey, array $inRelatedTables = array())
            {
                $whereConditions = array();
                $whereConditions[] = '(l.for_table = :for_table AND l.table_key = :table_key)';

                $params = array(
                    'for_table' => $inForTable,
                    'table_key' => $inKey
                );

                $ndx = 1;
                foreach($inRelatedTables as $table) {
                    $whereConditions[] = '(l.for_table = :for_table' . $ndx. ' AND l.related_key = :related_key' . $ndx . ')';
                    $params['for_table' . $ndx] = $table;
                    $params['related_key' . $ndx] = $inKey;
                    $ndx++;
                }

                $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                            'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . 
                                ' as i ON l.changed_by = i.id ' .
                            'WHERE ' . implode(' OR ', $whereConditions) . ' ORDER BY timestamp DESC';

                return \App\Request::$db->fetchAll($query, $params);
            }


        //----------------------------------------------------------------------
        //-- Retrieves log entries by table key
        //--
        //-- inForTable - Log Table Identifier
        //-- inTableKey - tabled ID related to record that was created
    
            public function getLogEntriesByTableKey(string $inForTable, string $inTableKey)
            {
                if (! is_string($this->tableName)) {
                    return false;
                }
    
                $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                            'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . 
                                ' as i ON l.changed_by = i.id ' .
                            'WHERE l.for_table = :for_table AND l.table_key = :table_key ORDER BY timestamp DESC';
    
                $params = array(
                    'for_table' => $inForTable,
                    'table_key' => $inTableKey
                );
    
                return \App\Request::$db->fetchAll($query, $params);
            }
    
   

        //----------------------------------------------------------------------
        //-- Retrieves log entries by timestamp range
        //--
            function getLogEntriesByTimestamp(string $inForTable, int $inStartTimestamp, int $inEndTimestamp)
            {
                if (is_array($inForTable)) {
                    $params = array_merge(array($inRelatedKey),array($inStartTimestamp), array($inEndTimestamp), $inForTable);

                    $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . 
                                    ' as i ON l.changed_by = i.id ' .
                                'WHERE l.related_key = ? AND ' .
                                    'l.timestamp >= ? AND ' .
                                    'l.timestamp <= ? AND ' .
                                    'l.for_table IN ('.str_pad('',count($inForTable)*2-1,'?,').') ' .
                                'ORDER BY timestamp DESC';
                } else {
                    $params = array(
                        'for_table'       => $inForTable,
                        'start_timestamp' => $inStartTimestamp,
                        'end_timestamp'   => $inEndTimestamp
                    );
    
                    $query = 'SELECT l.*, i.first_name, i.last_name FROM ' . $this->tableName . ' AS l ' .
                                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE .         
                                    ' as i ON l.changed_by = i.id ' .
                                'WHERE l.for_table = :for_table AND ' .
                                    'l.timestamp >= :start_timestamp AND ' .
                                    'l.timestamp <= :end_timestamp ' .
                                'ORDER BY timestamp DESC';
                }

                return \App\Request::$db->fetchAll($query, $params);
            }
    
    
 
        //----------------------------------------------------------------------
        //-- Display log entry with fields
    
            protected function displayFieldsLogEntry(array $inLogData, bool $inReturnOutput=false)
            {
                $output = '';
    
                if ( array_key_exists('fields', $inLogData)) {
                    if (is_array($inLogData['fields'])) {
                        $output .= '<table class="table table-bordered">' . "\n";
                        foreach($inLogData['fields'] as $fieldName => $fieldValue) {
                            $output .= '<tr>';
                            $output .= '<th scope="row" width="20%">' . $fieldValue['label'] . '</th>';
                            $output .= '<td>' . $fieldValue['old'] . ' => ' . $fieldValue ['new'] . '</td>';
                            $output .= '</tr>';
                            $output .= "\n";
                        }
                        $output .= '</table>' . "\n";
                    }
                }
    
                if ($inReturnOutput === true) {
                    return $output;
                }
    
                print $output;
                return;
            }
    
    
    
        //----------------------------------------------------------------------
        //-- Display Log Entry
    
            public function displayLogEntry(string $inLogEntry, bool $inReturnOutput=false)
            {
                if (! is_string($inLogEntry)) {
                    if ($inReturnOutput === true) {
                        return '';
                    }
                    return;
                }
    
                $logData = json_decode($inLogEntry, true);
    
                if ($logData === null) {
                    if ($inReturnOutput === true) {
                        return $inLogEntry;
                    }
                    print $inLogEntry;
                    return;
                }
    
                if (! array_key_exists('type', $logData)) {
                    if ($inReturnOutput === true) {
                        return '';
                    }
                    return;
                }
    
                switch($logData['type']) {
    
                    case 'F':
                        if ($inReturnOutput === true) {
                            return $this->displayFieldsLogEntry($logData, true);
                        }
                        $this->displayFieldsLogEntry($logData);
                        break;
    
                    case 'N':
                        if ($inReturnOutput === true) {
                            return $logData['note'];
                        }
                        print $logData['note'];
                        break;
    
                }
    
                if ($inReturnOutput === true) {
                    return '';
                }
            }
    
    
        //----------------------------------------------------------------------
        //-- Return string of log entry with fields
    
            protected function toStringFieldsLogEntry(string $inLogData)
            {
                $output = '';
    
                if ( array_key_exists('fields', $inLogData)) {
                    if (is_array($inLogData['fields'])) {
                        $lineCount = 0;
                        foreach($inLogData['fields'] as $fieldName => $fieldValue) {
                            $lineCount++;
                            if ($lineCount > 1) {
                                $output .= "\n";
                            }
                            $output .= $fieldValue['label'] . ': ';
                            $output .= $fieldValue['old'] . ' => ' . $fieldValue ['new'];
                        } 
                    }
                }
    
                return $output;
            }
    
    
        //----------------------------------------------------------------------
        //-- Return string of log entry 
    
            public function toStringLogEntry(string $inLogEntry)
            {
                if (! is_string($inLogEntry)) {
                    return '';
                }
    
                $logData = json_decode($inLogEntry, true);
    
                if ($logData === null) {
                    return $inLogEntry;
                }
    
                switch($logData['type']) {
    
                    case 'F':
                        return $this->toStringFieldsLogEntry($logData, true);
                        break;
    
                    case 'N':
                        return $logData['note'];
                        break;
    
                }
    
                return '';
            }
    
    }



//---------------------------------------------------------------------------------
//-- Defines
//---------------------------------------------------------------------------------

    define('DEFAULT_UNDEFINED_DATE', -2208967200);

    define('KIBIBYTE', 1024);
    define('MEBIBYTE', 1048576);
    define('GIBIBYTE', 1073741824);
    define('TEBIBYTE', 1099511627776); 
    define('PEBIBYTE', 1125899906842624);

    define('KILOBYTE', 1000);
    define('MEGABYTE', 1000000);
    define('GIGABYTE', 1000000000);
    define('TERABYTE', 1000000000000); 
    define('PETABYTE', 1000000000000000);

    define('TIMEFRAME_ONE_HOUR', 3600);
    define('TIMEFRAME_ONE_DAY',  86400);
    define('TIMEFRAME_ONE_WEEK', 604800);



//---------------------------------------------------------------------------------
//-- Traits used to define table field full text names
//---------------------------------------------------------------------------------

    trait FieldNames
    {
        protected static $constructed = false;

        protected static function construct()
        {
            self::$constructed = true;
        }

        public static function get()
        {
            return self::$values;
        }

        public static function getText(string $inFieldName)
        {
            if (! self::$constructed) {
                static::construct();
            }

            if (! is_string($inFieldName)) {
                return $inFieldName;
            }

            if ($inFieldName[0] == ':') {
                $inFieldName = substr($inFieldName,1);
            }

            if (array_key_exists($inFieldName, self::$fieldNames)) {
                return self::$fieldNames[$inFieldName];
            }

            return $inFieldName;
        }

        public static function getKeys()
        {
            return array_keys(self::$fieldNames);
        }
    }



//---------------------------------------------------------------------------------
//-- Form handles the process of displaying and processing form submission
//---------------------------------------------------------------------------------

    define('FORM_ACTION_RENDER',   0);
    define('FORM_ACTION_REDIRECT', 1);
    define('FORM_ACTION_GOINDEX',  2);
    define('FORM_ACTION_CANCEL',   4);
    define('FORM_ACTION_EXIT',     5);

    define('FORM_ON_SUCCESS_REDIRECT', 0);
    define('FORM_ON_SUCCESS_RENDER',   1);
    define('FORM_ON_SUCCESS_GOINDEX',  2);

    class Form extends Page
    {
        protected $debug = false;
        protected $useCSRF = true;
        protected $csrfTokenName = null;
        protected $csrfTokenValue = null;
        protected $useTokenRecordID = true;
        protected $formTitle = '';
        protected $postCollectID = true;
        protected $idField = 'id';
        protected $postCallFindRecord = false;
        protected $useTransactions = true;
        protected $rollbackTransaction = true;
        protected $defaultCancelPage = null;
        protected $defaultTab = null;
        protected $cancelPage = '';
        protected $redirectPage = '';
        protected $linkTagExtra = '';
        protected $formVariables = array();
        protected $formErrorMessage = '';
        protected $onSuccess = FORM_ON_SUCCESS_REDIRECT;
        protected $onScriptProcessFailure = FORM_ACTION_RENDER;
        protected $defaultErrMsg = 'There was an error while processing your request';
        protected $onFindRecordFailure = FORM_ACTION_GOINDEX;


        //-------------------------------------------------------
        //-- Constructor

             public function __construct()
             {
                parent::__construct();

                $this->bodyContentFileName = 'form.php';

                $this->defaultCancelPage = ($this->defaultCancelPage === null) ? '' : $this->defaultCancelPage;
                $this->defaultTab =($this->defaultTab === null) ? '' : $this->defaultTab;

                $this->cancelPage = \Framework\NavStack::getBackURL(inGetTop: true, inNoPrefix: true);

                if ($this->cancelPage === '') {
                    $this->cancelPage = $this->defaultCancelPage;
                } else {
                    if ($this->defaultTab != '') {
                        if (strpos($this->cancelPage,'tab=') === false) {
                            if (strpos($this->cancelPage,'?') === false) {
                                $this->cancelPage .= '?tab='. $this->defaultTab;
                            } else {
                                    $this->cancelPage .= '&tab='. $this->defaultTab;
                            }
                        }
                    }
                }

                $this->redirectPage = $this->cancelPage;

                if ($this->csrfTokenName !== null) {
                    $this->setupCSRF();
                }
             }


        //-------------------------------------------------------
        //-- Setup the form to allow processing of CSRF token

            protected function setupCSRF(bool $inOverride=false)
            {
                if (! is_string($this->csrfTokenName)) {
                    if ($this->debug) print '[setupCSRF: token name not string]';
                    return;
                }

                $this->formVariables['csrf'] = new \Framework\Form\Variable\CSRF;

                if ((! \App\Request::isPOST()) || ($inOverride === true)) {
                    $this->csrfTokenValue = \Framework\getGUID();
                    $_SESSION[$this->csrfTokenName] = $this->csrfTokenValue;
                    $this->formVariables['csrf']->value = $this->csrfTokenValue;
                }
            }


        //-------------------------------------------------------
        //-- Function that process the Get/Post request for the form

            public function process()
            {
                if (! $this->userHasAccess()) {
                    \Framework\redirectIndex();
                    return;
                }

                if (\App\Request::isPOST()) {
                    $returnAction = $this->processPOST();
                } else {
                    $returnAction = $this->processGET();
                }

                switch($returnAction) {
                    case FORM_ACTION_RENDER:
                        $this->render();
                        return;

                    case FORM_ACTION_REDIRECT:
                        \Framework\redirectPage($this->redirectPage);
                        return;

                    case FORM_ACTION_CANCEL:
                        \Framework\redirectPage($this->cancelPage . $this->linkTagExtra);
                        return;

                    case FORM_ACTION_EXIT:
                        return;
                }

                //-- FORM_ACTION_GOINDEX (or any mis-assignments)
                \Framework\redirectIndex();
            }


        //---------------------------------------------------------------------
        //-- Function that determines if the current user has access to the form.
        //--    NOTE: implement in child class

            protected function userHasAccess()
            {
                if ($this->debug) die('[userHasAccess: default method]');
                return false;
            }


        //-------------------------------------------------------
        //-- Function to handle the processing of GET requests 

            protected function processGET()
            {
                $this->buildFormVariables();

                if (! $this->findRecord()) {
                    if ($this->debug) die('[processGET: failed findRecord]');
                    return $this->onFindRecordFailure;
                }

                if (! $this->getPreCanEdit()) {
                    if ($this->debug) die('[processGET: failed getPreCanEdit]');
                    return FORM_ACTION_GOINDEX;
                }


                if (! $this->canEdit()) {
                    if ($this->debug) die('[processGET: failed canEdit]');
                    return FORM_ACTION_GOINDEX;
                }

                $this->customizeFormVariables();
                $this->loadReadOnlyValues();
                $this->loadWriteValues();

                if ($this->useTokenRecordID) {
                    $recordID = $this->getRecordID();
                    if ($recordID == 0) {
                        return FORM_ACTION_GOINDEX;
                    }
                    $_SESSION[$this->csrfTokenName . '_id'] = $recordID;
                }

                return FORM_ACTION_RENDER;
            }


        //-------------------------------------------------------
        //-- Used to get the record ID being edited form the
        //-- child class
        //-- NOTE: implement in child class

            protected function getRecordID()
            {
                return 0;
            }


        //-------------------------------------------------------
        //-- Find the record that we are wanting to edit
        //-- NOTE: implement in child class

            protected function findRecord($inRecordID=false)
            {
                return false;
            }


        //-------------------------------------------------------
        //-- Use to preform custom code needed before canEdit
        //-- This function is called by processGET
        //-- NOTE: implement in child class (if needed)

            protected function getPreCanEdit()
            {
                return true;
            }


        //-------------------------------------------------------
        //-- Does basic check for editability based on record status
        //-- NOTE: returns false to force implementation in child class

            protected function canEdit()
            {
                if ($this->debug) print '[canEdit: default method]';
                return false;
            }


        //-------------------------------------------------------
        //-- Build the form variables 
        //-- NOTE: implement in child class

            protected function buildFormVariables()
            {
            }


        //-------------------------------------------------------
        //-- Customize form variables
        //-- NOTE: implement in child class (if needed)

            protected function customizeFormVariables()
            {
            }


        //-------------------------------------------------------
        //-- Load read only values into the form variables
        //-- This function is called for both GET and POST
        //-- NOTE: implement in child class (if needed)

            protected function loadReadOnlyValues()
            {
            }


        //-------------------------------------------------------
        //-- Load changeable values into the form variables
        //--   This function is called only by GET
        //--   NOTE: implement in child class (if needed)

            protected function loadWriteValues()
            {
            }


        //---------------------------------------------------
        //-- Function to handle the processing of POST requests 

            protected function processPOST()
            {
                $this->buildFormVariables();

                if ($this->postCollectID) {

                    if (! array_key_exists($this->idField, $this->formVariables)) {
                        if ($this->debug) die('[processPOST: idField not found]');
                        return FORM_ACTION_GOINDEX;
                    }

                    $this->formVariables[$this->idField]->collect();

                    if (! $this->formVariables[$this->idField]->valid) {
                        if ($this->debug) die('[processPOST: idField collect not valid]');
                        return FORM_ACTION_GOINDEX;
                    }

                    if (! $this->findRecord($this->formVariables[$this->idField]->value)) {
                        if ($this->debug) die('[processPOST: failed findRecord postCollectID]');
                        return FORM_ACTION_GOINDEX;
                    }
                } else {
                    if ($this->postCallFindRecord) {
                        if (! $this->findRecord()) {
                            if ($this->debug) die('[processPOST: failed findRecord postCallFindRecord]');
                            return FORM_ACTION_GOINDEX;
                        }
                    }
                }

                if (! $this->postPreCanEdit()) {
                    if ($this->debug) die('[processPOST: failed prePostCanEdit]');
                    return FORM_ACTION_GOINDEX;
                }

                if (! $this->canEdit()) {
                    if ($this->debug) die('[processPOST: failed canEdit]');
                    return FORM_ACTION_GOINDEX;
                }

                $this->customizeFormVariables();

                $this->loadReadOnlyValues();

                //-- Check the csrf token

                    if (! $this->checkCSRF()) {
                        if ($this->debug) die('[processPOST: failed checkCSRF]');
                        $this->redirectPage = $this->cancelPage;
                        return FORM_ACTION_REDIRECT;
                    }

                //-- Collect the fields from the form, and validate

                    if (! $this->collectFormVariables()) {
                        if ($this->debug) {
                            print '<pre>';
                            print_r( $this->formVariables );
                            print '</pre>';
                            die('[processPOST: failed to collect form variables]');
                        }
                        return FORM_ACTION_RENDER;
                    }

                // check to make sure record ID in post matches stored session record ID

                    if ($this->useTokenRecordID) {
                        if ($this->getRecordID() == 0) {
                            return FORM_ACTION_REDIRECT;
                        }
                        if (! array_key_exists($this->csrfTokenName . '_id', $_SESSION)) {
                            return FORM_ACTION_REDIRECT;
                        }
                        if ($_SESSION[$this->csrfTokenName . '_id'] != $this->getRecordID()) {
                            return FORM_ACTION_REDIRECT;
                        }
                    }

                //-- validate post variables

                    if (! $this->postValidate()) {
                        if ($this->debug) print '[processPOST: failed postValidate]';
                        return FORM_ACTION_RENDER;
                    }

                //-- Begin the transaction

                    if ($this->useTransactions) {
                        if (! \App\Request::$db->beginTransaction()) {
                            if ($this->debug) print '[processPOST: failed beginTrasaction]';
                            $this->formErrorMessage = $this->defaultErrMsg;
                            return FORM_ACTION_RENDER;
                        }
                    }

                //-- Handle updates and creations

                    if (! $this->scriptUpdate()) {

                        if ($this->debug) print '[processPOST: failed scriptUpdate]';

                        if ($this->useTransactions) {
                            if ($this->rollbackTransaction === true) {
                                \App\Request::$db->rollbackTransaction();
                            } else {
                                \App\Request::$db->commitTransaction();
                            }
                        }

                        if (! $this->postFailedPreRender()) {
                            return FORM_ACTION_GOINDEX;
                        }

                        return $this->onScriptProcessFailure;
                    }

                //-- Commit the transaction

                    if ($this->useTransactions) {
                        \App\Request::$db->commitTransaction();
                    }

                //-- redirect page to view page

                    switch($this->onSuccess) {

                        case FORM_ON_SUCCESS_GOINDEX:
                            return FORM_ACTION_GOINDEX;
                            break;

                        case FORM_ON_SUCCESS_RENDER:
                            return FORM_ACTION_RENDER;
                            break;

                        default:
                            return FORM_ACTION_REDIRECT;
                    }

            }


        //-------------------------------------------------------
        //-- Use to preform custom code needed before canEdit
        //--   This function is called by processPOST
        //--   NOTE: implement in child class (if needed)

            protected function postPreCanEdit()
            {
                return true;
            }


        //-------------------------------------------------------
        //-- Checks form Post to see if CSRF token valid

            protected function checkCSRF()
            {
                if ($this->useCSRF === false) {
                    return true;
                }

                if (! is_string($this->csrfTokenName)) {
                    if ($this->debug) print '[checkCSRF: token name not string]';
                    return false;
                }

                if (! array_key_exists($this->csrfTokenName, $_SESSION)) {
                    if ($this->debug) print '[checkCSRF: token not in SESSION]';
                    return false;
                }

                if (! array_key_exists('csrf', $this->formVariables)) {
                    if ($this->debug) print '[checkCSRF: csrf not in formVariables]';
                    return false;
                }

                $this->formVariables['csrf']->collect();

                if ($this->formVariables['csrf']->value == $_SESSION[$this->csrfTokenName]) {
                    return true;
                }

                if ($this->debug) print '[checkCSRF: token mismatch]';

                return false;
            }


        //-------------------------------------------------------
        //-- Function to handle collecting of form variable

            protected function collectFormVariables(bool $inForceCollection=false)
            {
                foreach($this->formVariables as $key => $var)
                {
                    if ($this->formVariables[$key]->collect) {
                        if ((! $this->formVariables[$key]->collected) || ($inForceCollection === true)) {
                            $this->formVariables[$key]->collect();
                        }
                    }
                }

                foreach($this->formVariables as $key => $var)
                {
                    if ($this->formVariables[$key]->required) {
                        if (! $this->formVariables[$key]->valid) {
                            $this->formVariables[$key]->autoFocus = true;
                            if ($this->debug) {
                                print '[processPOST: collect not ok [' . $key. ']]';
                            }
                            return false;
                        }
                    }
                }

                return true;
            }


        //-------------------------------------------------------
        //-- Performs validation before update record
        //--   NOTE: returns false to force implementation in child class

            protected function postValidate()
            {
                if ($this->debug) print '[postValidate: default method]';
                return false;
            }


        //-------------------------------------------------------
        //-- Perform the application update based on submission
        //--   NOTE: implement in child class

            protected function scriptUpdate()
            {
            }


        //-------------------------------------------------------
        //-- Perform code before a failed update
        //--   NOTE: implement in child class (if needed)

            protected function postFailedPreRender()
            {
                return true;
            }

    } 
 
 

//----------------------------------------------------------------------
//-- Returns a timestamp for today with hour, minute, second set to 0
//----------------------------------------------------------------------

    function getTodayTS(bool $inEndOfDay=false)
    {
        $now = getdate();

        if ($inEndOfDay === true) {
            return mktime(23,59,59,$now['mon'],$now['mday'],$now['year']);
        }

        return mktime(0,0,0,$now['mon'],$now['mday'],$now['year']);
    }


//----------------------------------------------------------------------
//-- Returns a timestamp from a date string
//----------------------------------------------------------------------

    function getTS(string $inDateTimeStr, bool $inZeroHour = false, bool $inEOD = false)
    {
        $inDateTimeStr = trim($inDateTimeStr);

        if ($inDateTimeStr === '') {
            return false;
        }

        $d = date_parse($inDateTimeStr);

        if ($d['error_count'] > 0) {
            return false;
        }

        if ($d['hour'] === false) {
            $d['hour'] = 0;
        }

        if ($d['minute'] === false) {
            $d['minute'] = 0;
        }

        if ($d['second'] === false) {
            $d['second'] = 0;
        }

        if ($inZeroHour === true) {
            $d['hour'] = 0;
            $d['minute'] = 0;
            $d['second'] = 0;
        } else if ($inEOD === true) {
            $d['hour'] = 23;
            $d['minute'] = 59;
            $d['second'] = 59;
        }

        return mktime($d['hour'],$d['minute'],$d['second'],$d['month'],$d['day'],$d['year']);
    }


//----------------------------------------------------------------------
//-- Generate a full URL site link using 
//-- APP_PROTOCOL, APP_SERVER_NAME & APP_URL_PREFIX
//----------------------------------------------------------------------

    function siteURL(string $inPath = '', string $inType = 'none')
    {
        $url = APP_PROTOCOL . APP_SERVER_NAME;

        if (defined('APP_PORT')) {
            $url .= ':' . APP_PORT;
        }

        $url = $url . APP_URL_PREFIX . $inPath;

        switch($inType)
        {
            case 'css':
                $url  = '<link rel="stylesheet" type="text/css" href="' . $url . '">' . PHP_EOL;
                break;

            case 'js':
                $url = '<script type="text/javascript" src="' . $url . '"></script>';
                break;
        }
        return $url;
    }


//-------------------------------------------------------------------------
//-- Generate a full CDN URL site link 
//----------------------------------------------------------------------

    function cdnURL(string $inPath = '', string $inType = 'none')
    {
        switch($inType)
        {
            case 'css':
                $url  = '<link rel="stylesheet" type="text/css" href="https://';
                $url .= CDN_SERVER_NAME . (defined('CDN_PORT') ? ':' . CDN_PORT : '') . CDN_PATH . $inPath . '">' . PHP_EOL;
                break;

            case 'js':
                $url = '<script type="text/javascript" src="https://';
                $url .= CDN_SERVER_NAME . (defined('CDN_PORT') ? ':' . CDN_PORT : '') . CDN_PATH . $inPath . '"></script>' . PHP_EOL;
                break;

            default:
                $url = 'https://' . CDN_SERVER_NAME . (defined('CDN_PORT') ? ':' . CDN_PORT : '') . CDN_PATH . $inPath;
        }

        return $url;
    }


//----------------------------------------------------------------------
//-- Redirects web browser to the site index page
//----------------------------------------------------------------------

    function redirectIndex() 
    {
        header('Location: ' . \Framework\siteURL());
    }


//----------------------------------------------------------------------
//-- Redirects web browser to specified page
//----------------------------------------------------------------------

    function redirectPage(string $inPage = '') 
    {
        if (! is_string($inPage)) {
            $inPage = '';
        }

        if ((substr($inPage,0,7) == 'http://')  || (substr($inPage,0,8) == 'https://')) {
            header('Location: ' . $inPage);
        } else {
            header('Location: ' . \Framework\siteURL($inPage));
        }
    }


//----------------------------------------------------------------------
//-- Generate GUID using mt_rand (com_create_guid not always available)
//----------------------------------------------------------------------

     function getGUID() {
         return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
             mt_rand(0, 65535),
             mt_rand(0, 65535),
             mt_rand(0, 65535),
             mt_rand(16384, 20479),
             mt_rand(32768, 49151),
             mt_rand(0, 65535),
             mt_rand(0, 65535),
             mt_rand(0, 65535));
     }


//----------------------------------------------------------------------
//-- Generate a select options list
//----------------------------------------------------------------------

    function makeSelectOptionList($inList, $inTagField, $inTextField)
    {
        $options = array();
        $inList->rewind();
        while ($inList->next()) {
            $options[$inList->$inTagField] = $inList->$inTextField;
        }
        return $options;
    }



//----------------------------------------------------------------------
//-- Get the client's IP address
//----------------------------------------------------------------------

    function clientIP()
    {
        if (! array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return false;
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'];

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $remoteAddr = trim($_SERVER['HTTP_X_FORWARDED_FOR']);
            if (strpos($remoteAddr, ':') !== false) {
                list($remoteAddr, $extra) = explode(':', $remoteAddr, 2);
            }
        }

        $remoteAddr = trim($remoteAddr);

        return $remoteAddr;
    }



//----------------------------------------------------------------------
//-- Format values to Binary System readable value
//----------------------------------------------------------------------

    function binaryByteFormat($inValue)
    {
        if ($inValue < KIBIBYTE) {
            return $inValue . ' B';
        }

        if ($inValue < MEBIBYTE) {
            return round($inValue / KIBIBYTE) . ' KB';
        }

        if ($inValue < GIBIBYTE) {
            return round($inValue / MEBIBYTE) . ' MB';
        }

        if ($inValue < TEBIBYTE) {
            return round($inValue / GIBIBYTE) . ' GB';
        }

        if ($inValue < PEBIBYTE) {
            return round($inValue / TEBIBYTE) . ' TB';
        }

        return round($inValue / PEBIBYTE) . ' PB';
    }


//----------------------------------------------------------------------
//-- Format values to Decimal System readable value
//----------------------------------------------------------------------

    function decimalByteFormat($inValue)
    {
        if ($inValue < KILOBYTE) {
            return $inValue . ' B';
        }

        if ($inValue < MEGABYTE) {
            return round($inValue / KILOBYTE) . ' KB';
        }

        if ($inValue < GIGABYTE) {
            return round($inValue / MEGABYTE) . ' MB';
        }

        if ($inValue < TERABYTE) {
            return round($inValue / GIGABYTE) . ' GB';
        }

        if ($inValue < PETABYTE) {
            return round($inValue / TERABYTE) . ' TB';
        }

        return round($inValue / PETABYTE) . ' PB';
    }



//------------------------------------------------------------------------------
//-- Implements a classes to represent a JWT
//------------------------------------------------------------------------------

    class JWT
    {
        protected $headerB64;
        protected $payloadB64;
        protected $signagureB64;

        protected $header;
        protected $payload;
        protected $signature;

        protected $debug;

        //----------------------------------------------------------------------
        //-- Contructor

            public function __construct()
            {
                $this->reset();
            }


        //----------------------------------------------------------------------
        //-- Reset the JWT

            public function reset()
            {
                $this->headerB64    = false;
                $this->payloadB64   = false;
                $this->signagureB64 = '';

                $this->header    = false;
                $this->payload   = false;
                $this->signature = '';

                $this->debug = false;
            }


        //----------------------------------------------------------------------
        //-- Turn debugging on

            public function debug()
            {
                $this->debug = true;
            }




        //----------------------------------------------------------------------
        //-- Returns the JWT payload

            public function payload() {
                return $this->payload;
            }


        //----------------------------------------------------------------------
        //-- Decode a b64 encoded string

            public function urlsafeB64Decode(string $inInput)
            {
                $remainder = \strlen($inInput) % 4;
                if ($remainder) {
                    $padlen = 4 - $remainder;
                    $inInput .= \str_repeat('=', $padlen);
                }
                return \base64_decode(\strtr($inInput, '-_', '+/'));
            }


        //----------------------------------------------------------------------
        //-- Return an encoded string length

            public function encodeLength(int $inLength)
            {
                if ($inLength <= 0x7F) {
                    return chr($inLength);
                }
                $temp = ltrim(pack('N', $inLength), chr(0));
                return pack('Ca*', 0x80 | strlen($temp), $temp);
            }


        //----------------------------------------------------------------------
        //-- Return the base64 encoded JWT

            public function export()
            {
                return $this->headerB64 . "." . $this->payloadB64 . "." . $this->signagureB64;
            }


        //----------------------------------------------------------------------
        //-- Create a JWT

            public function create(array $inData, string $inSecret)
            {
                $this->reset();

                // Create token header as a JSON string
                $this->header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'expires' => time() + 300]);

                // Create token payload as a JSON string
                $this->payload = json_encode($inData);

                // Encode Header to Base64Url String
                $this->headerB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->header));

                // Encode Payload to Base64Url String
                $this->payloadB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->payload));

                // Create Signature Hash
                $this->signature = hash_hmac('sha256', $this->headerB64 . "." . $this->payloadB64, $inSecret, true);

                // Encode Signature to Base64Url String
                $this->signagureB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->signature));

            }


        //----------------------------------------------------------------------
        //-- Decode a JWT

            public function decode(string $inJWT, string $inSecret)
            {
                $this->reset();

                $tks = explode('.', $inJWT);

                if (count($tks) != 3) {
                    if ($this->debug) {
                        print '[decode: incorrect number of parts]';
                    }
                    return false;
                }

                list($this->headerB64, $this->payloadB64, $this->signagureB64) = $tks;

                $this->header = json_decode($this->urlsafeB64Decode($this->headerB64),true);

                if ($this->header === false ) {
                    if ($this->debug) {
                        print '[decode: header decode failed]';
                    }
                    return false;
                }

                $this->payload = json_decode($this->urlsafeB64Decode($this->payloadB64), true);

                if ($this->payload === false) {
                    if ($this->debug) {
                        print '[decode: payload decode failed]';
                    }
                    $this->reset();
                    return false;
                }

                $this->signature = $this->urlsafeB64Decode($this->signagureB64);

                if ($this->signature === false) {
                    if ($this->debug) {
                        print '[decode: signature decode failed]';
                    }
                    $this->reset();
                    return false;
                }

                if (! array_key_exists('typ', $this->header)) {
                    if ($this->debug) {
                        print '[decode: not typ]';
                    }
                    $this->reset();
                    return false;
                }

                if ($this->header['typ'] != 'JWT') {
                    if ($this->debug) {
                        print '[decode: typ not JWT]';
                    }
                    $this->reset();
                    return false;
                }

                if (! array_key_exists('alg', $this->header)) {
                    if ($this->debug) {
                        print '[decode: no alg]';
                    }
                    $this->reset();
                    return false;
                }

                if ($this->header['alg'] != 'HS256') {
                    if ($this->debug) {
                        print '[decode: alg no HS256]';
                    }
                    $this->reset();
                    return false;
                }

                if (! array_key_exists('expires', $this->header)) {
                    if ($this->debug) {
                        print '[decode: no expires]';
                    }
                    $this->reset();
                    return false;
                }

                if (! is_numeric($this->header['expires'])) {
                    if ($this->debug) {
                        print '[decode: expires no numeric]';
                    }
                    $this->reset();
                    return false;
                }

                $expires = intval($this->header['expires']);

                if ($expires < time()) {
                    if ($this->debug) {
                        print '[decode: jwt expired]';
                    }
                    $this->reset();
                    return false;
                }

                $signature = hash_hmac('sha256', $this->headerB64 . "." . $this->payloadB64, $inSecret, true);

                if ($signature != $this->signature) {
                    if ($this->debug) {
                        print '[decode: signature mismatch]';
                    }
                    $this->reset();
                    return false;
                }

                return true;
            }


        //----------------------------------------------------------------------
        //-- Generate a signature using the private key

            public function generateSignature(string $inPrivateKey)
            {
                $privateKey = file_get_contents($inPrivateKey);

                if ($privateKey === false) {
                    $privateKey = NULL;
                }

                $data = $this->headerB64 . "." . $this->payloadB64 . "." . $this->signagureB64;

                if (! openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                    return NULL;
                }

                return $signature;
            }


        //----------------------------------------------------------------------
        //-- Validate a signature using the public key

            public function validateSignature(string $inSignature, string $inPublicKeyFile)
            {
                $publicKey = file_get_contents($inPublicKeyFile);

                $data = $this->headerB64 . "." . $this->payloadB64 . "." . $this->signagureB64;

                if (openssl_verify($data, $inSignature, $publicKey, "sha256WithRSAEncryption") === 1) {
                    return true;
                }

                if ($this->debug) {
                    print '[validateSignature: did not verify]';
                }

                return false;
            }

    }



//-------------------------------------------------------------------------------------------
//-- Contains general functions and classes related to tracking "breadcrumbs" in a session
//--
//-- NOTE: APP_NAVSTACK_TAG needs to be defined so that navstack is unique to the application
//-------------------------------------------------------------------------------------------

    class NavStack
    {
        protected static $debugLog = array();

        //----------------------------------------------------------------------
        //-- Get the stack

            public static function getStack()
            {
                if (! array_key_exists(APP_SESSION_KEY, $_SESSION)) {
                    self::$debugLog[] = 'NavStack::getStack: no APP_SESSION_KEY';
                    return array();
                }

                if (! is_array($_SESSION[APP_SESSION_KEY])) {
                    self::$debugLog[] = 'NavStack::getStack: APP_SESSION_KEY not array';
                    return array();
                }

                if (! array_key_exists(APP_NAVSTACK_TAG . '-navstack', $_SESSION[APP_SESSION_KEY])) {
                    self::$debugLog[] = 'NavStack::getStack: no application navstack';
                    return array();
                }

                if (! is_array($_SESSION[APP_SESSION_KEY][APP_NAVSTACK_TAG . '-navstack'])) {
                    self::$debugLog[] = 'NavStack::getStack: application navstack not array';
                    return array();
                }

                return $_SESSION[APP_SESSION_KEY][APP_NAVSTACK_TAG . '-navstack'];
            }



        //----------------------------------------------------------------------
        //-- Store the stack

            public static function storeStack(array $inStack)
            {
                self::$debugLog[] = 'NavStack::storeStack: [' . print_r($inStack,true) . ']';
                $_SESSION[APP_SESSION_KEY][APP_NAVSTACK_TAG . '-navstack'] = $inStack;
            }


        //----------------------------------------------------------------------
        //-- Push navigation url onto stack

            public static function push(bool $inClear = false, bool $inIgnoreQueryParameters = false)
            {
                self::$debugLog[] = 'NavStack::push: start';

                //-- set if NVS is set

                    $nvs = \App\Request::getQP('nvs');

                //-- grab the url and query parameters

                    $url = \App\Request::scriptPath();

                //-- strip off query parameters to ignore

                    $parameters = \App\Request::getQueryParameters();

                    self::$debugLog[] = 'NavStack::push: orig query parameters [' . print_r($parameters,true) . ']';

                    if (is_array($inIgnoreQueryParameters)) {
                        $inIgnoreQueryParameters[] = 'nvs';
                        foreach($inIgnoreQueryParameters as $ip) {
                            $t = $ip . '=';
                            $tLen = strlen($t);
                            foreach($parameters as $ndx => $p) {
                                if (substr($p,0,$tLen) == $t) {
                                    self::$debugLog[] = 'NavStack::push: removing nvs';
                                    unset($parameters[$ndx]);
                                }
                            }
                        }
                    } 

                    $queryParameters = http_build_query($parameters);

                    self::$debugLog[] = 'NavStack::push: url [' . $url . ']';
                    self::$debugLog[] = 'NavStack::push: query parameters [' . print_r($queryParameters,true) . ']';

                //-- get the nav stack from the session

                    $navStack = self::getStack();
                    self::$debugLog[] = 'NavStack::push: current stack [' . print_r($navStack,true) . ']';

                //-- does this request include clearing the nav stack
                //-- remove the nvs parameter as well

                    if (($nvs !== false) || ($inClear === true)) {
                        self::$debugLog[] = 'NavStack::push: clearing navstack';
                        $navStack = array();
                        array_push($navStack, array('url' => $url, 'query' => $queryParameters));
                        self::storeStack($navStack);
                        return;
                    }

                //-- if the navstack exists, but is empty, just add

                    $stackNDX = count($navStack);

                    if ($stackNDX < 1) {
                        self::$debugLog[] = 'NavStack::push: empty stack';
                        $navStack = array();
                        array_push($navStack, array('url' => $url, 'query' => $queryParameters));
                        self::storeStack($navStack);
                        return;
                    }

                //-- check the top of the stack to see if the url matches the current url (and query parameters)

                    $stackNDX--;

                    self::$debugLog[] = 'NavStack::push: stackNDX [' . $stackNDX . ']';
                    self::$debugLog[] = 'NavStack::push: stackNDX url [' . $navStack[$stackNDX]['url'] . ']';

                    if ($navStack[$stackNDX]['url'] == $url) {
                        self::$debugLog[] = 'NavStack::push: same url [' . print_r($navStack[$stackNDX],true) . ']';
                        if ($navStack[$stackNDX]['query'] == $queryParameters) {
                            self::$debugLog[] = 'NavStack::push: same url/query [' . print_r($navStack[$stackNDX],true) . ']';
                            // we assume we are returning to same page -- leave on top of stack
                            return;
                        }
                    }

                //-- check to see if the 2nd from the top matches current url
                //-- if so, we are backing up

                    $stackNDX--;

                    if ($stackNDX < 0) {
                        self::$debugLog[] = 'NavStack::push: no prev URL';
                        array_push($navStack, array('url' => $url, 'query' => $queryParameters));
                        self::storeStack($navStack);
                        return;
                    }

                    if (array_key_exists(APP_NAVSTACK_TAG . '-navstack', $_SESSION[APP_SESSION_KEY])) {
                        if ($_SESSION[APP_SESSION_KEY][APP_NAVSTACK_TAG . '-navstack'][$stackNDX]['url'] == $url) {
                            if ($_SESSION[APP_SESSION_KEY][APP_NAVSTACK_TAG . '-navstack'][$stackNDX]['query'] == $queryParameters) {
                                self::$debugLog[] = 'NavStack::push: match prev url [' . print_r($navStack[$stackNDX],true) . ']';
                                array_pop($navStack);
                                self::storeStack($navStack);
                                return;
                            }
                        }
                    }

                //-- if we got this far, we did not find the url, add

                    self::$debugLog[] = 'NavStack::push: new URL';
                    array_push($navStack, array('url' => $url, 'query' => $queryParameters));
                    self::storeStack($navStack);
            }


        //----------------------------------------------------------------------
        //-- Returns the top url from the stack

            public static function getBackURL(bool $inGetTop=false, bool $inNoPrefix=false)
            {
                self::$debugLog[] = 'NavStack::getBackURL: start';

                //-- get the nav stack from the session

                    $navStack = self::getStack();
                    self::$debugLog[] = 'NavStack::getBackURL: current stack [' . print_r($navStack,true) . ']';

                //-- There must be something on the stack to return to

                    if ($inGetTop === true) {
                        $stackNDX = count($navStack) - 1;
                    } else {
                        $stackNDX = count($navStack) - 2;
                    }

                    self::$debugLog[] = 'NavStack::getBackURL: stackNDX [' . $stackNDX . ']';

                    if ($stackNDX < 0) {
                        self::$debugLog[] = 'NavStack::getBackURL: empty stack return to index';
                        if ($inNoPrefix == true) {
                            return '';
                        }
                        return \Framework\siteURL('');
                    }

                //-- find the stack top, and then return the next to top

                    if ($navStack[$stackNDX]['query'] == '') {
                        self::$debugLog[] = 'NavStack::getBackURL: return url [' . $navStack[$stackNDX]['url'] . ']';
                        if ($inNoPrefix == true) {
                            return $navStack[$stackNDX]['url'];
                        }
                        return \Framework\siteURL( $navStack[$stackNDX]['url']);
                    }

                    self::$debugLog[] = 'NavStack::getBackURL: return url [' . $navStack[$stackNDX]['url'] . '?' . $navStack[$stackNDX]['query'] . ']';
                    if ($inNoPrefix == true) {
                        return $navStack[$stackNDX]['url'] . '?' . $navStack[$stackNDX]['query'];
                    }
                    return \Framework\siteURL( $navStack[$stackNDX]['url'] . '?' . $navStack[$stackNDX]['query']);
            }


        //----------------------------------------------------------------------
        //-- Clear the Nav Stack

            public static function clear()
            {
                self::$debugLog[] = 'NavStack::clear: clearing stack';
                self::storeStack(array());
            }


        //----------------------------------------------------------------------
        //-- Dump the stack for debugging

            public static function dumpStack()
            {
                $navStack = self::getStack();
                print '<pre>';
                print_r( $navStack );
                print '</pre>';
            }

            public static function dumpDebug()
            {
                print '<pre>';
                print_r( self::$debugLog );
                print '</pre>';
            }

    }



//---------------------------------------------------------------------------------
//-- Common Normalization Definitions
//---------------------------------------------------------------------------------

    class Normalize
    {
        public static function dateY($inValue, bool $inUseUnset=false)
        {
            $inValue = intval($inValue);
            if ($inValue == DEFAULT_UNDEFINED_DATE) {
                return '';
            }
            return date('Y',intval($inValue));
        }
    
        public static function dateYMD($inValue, bool $inUseUnset=false)
        {
            $inValue = intval($inValue);
            if ($inValue == DEFAULT_UNDEFINED_DATE) {
                return '';
            }
            return date('Y-m-d',intval($inValue));
        }
    
        public static function dateMD($inValue)
        {
            $inValue = intval($inValue);
            if ($inValue == DEFAULT_UNDEFINED_DATE) {
                return '';
            }
            return date('m-d',intval($inValue));
        }
    
        public static function date_time($inValue)
        {
            $inValue = intval($inValue);
            if ($inValue == DEFAULT_UNDEFINED_DATE) {
                return '';
            }
            return date('Y-m-d H:i:s',intval($inValue));
        }
    
        public static function yes_no($inValue)
        {
            return \Framework\YesNoState::getText($inValue);
        }

    }



//------------------------------------------------------------------------------
//-- Application PDO class for MySQL/MarioDB connections
//------------------------------------------------------------------------------

    class LibPDO
    {
        protected $pdo = false;
        protected $debugMsgs = array();
        protected $wasError = false;

        //----------------------------------------------------------------------
        //-- Attempt to connect to the database

            public function connect()
            {
                if (! defined('APP_MYSQL_DSN')) {
                    die('APP_MYSQL_DSN not defined');
                }

                if (! defined('APP_MYSQL_USER')) {
                    die('APP_MYSQL_USER not defined');
                }

                if (! defined('APP_MYSQL_PASSWD')) {
                    die('APP_MYSQL_PASSWD not defined');
                }

                try {
                    $this->pdo = new \PDO(APP_MYSQL_DSN, APP_MYSQL_USER, APP_MYSQL_PASSWD);
                } catch (\PDOException $e) {
                    return false;
                }

                return true;
            }


        //----------------------------------------------------------------------
        //-- Outputs the $debugMsgs array with <pre>...</pre> block

            public function dumpDebugMsgs()
            {
                print '<pre>';
                print_r( $this->debugMsgs );
                print '</pre>';
            }


        //----------------------------------------------------------------------
        //-- Returns the $debugMsgs array

            public function getDebugMsgs()
            {
                return $this->debugMsgs;
            }


        //----------------------------------------------------------------------
        //-- Starts transaction sequence

            public function beginTransaction()
            {
                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'beginTransaction: pdo false:';
                    return false;
                }

                return $this->pdo->beginTransaction();
            }


        //----------------------------------------------------------------------
        //-- Rollback a transaction sequence

            public function rollbackTransaction()
            {
                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'rollbackTransaction: pdo false:';
                    return false;
                }

                return $this->pdo->rollback();
            }


        //----------------------------------------------------------------------
        //-- Commit a transaction sequence

            public function commitTransaction()
            {
                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'commitTransaction: pdo false:';
                    return false;
                }

                return $this->pdo->commit();
            }


        //----------------------------------------------------------------------
        //-- Executes a query using parameters

            public function execute(string $inQuery, array $inParams = array())
            {
                $this->wasError = false;

                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'execute: pdo false: query=' . $inQuery;
                    $this->debugMsgs[] = 'execute: pdo false: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                try {
                    $stmt = $this->pdo->prepare($inQuery);
                } catch (PDOException $e) {
                    $stmt = false;
                } catch (\Throwable $e) {
                    $stmt = false;
                } catch (Exception $e) {
                    $stmt = false;
                }

                if ($stmt === false) {
                    $this->debugMsgs[] = 'execute: prepare failed: error=' . print_r($this->pdo->errorInfo(),true);
                    $this->debugMsgs[] = 'execute: prepare failed: query=' . $inQuery;
                    $this->debugMsgs[] = 'execute: prepare failed: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                try {
                    $res = $stmt->execute($inParams);
                } catch (PDOException $e) {
                    $res = false;
                } catch (\Throwable $e) {
                    $res = false;
                } catch (Exception $e) {
                    $res = false;
                }

                if ($res === false) {
                    $this->debugMsgs[] = 'execute: execute failed: error=' . print_r($stmt->errorInfo(),true);
                    $this->debugMsgs[] = 'execute: execute failed: query=' . $inQuery;
                    $this->debugMsgs[] = 'execute: execute failed: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                return true;
            }


        //----------------------------------------------------------------------
        //-- Executes a query using parameters, and fetches all records
        //--
        //-- Be careful using this function since a large query can exceed memory

            public function fetchAll(string $inQuery, array $inParams = array())
            {
                $this->wasError = false;

                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'fetchAll: pdo false: query=' . $inQuery;
                    $this->debugMsgs[] = 'fetchAll: pdo false: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                try {
                    $stmt = $this->pdo->prepare($inQuery);
                } catch (PDOException $e) {
                    $stmt = false;
                } catch (\Throwable $e) {
                    $stmt = false;
                } catch (Exception $e) {
                    $stmt = false;
                }

                if ($stmt === false) {
                    $this->debugMsgs[] = 'fetchAll: stmt false: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return array();
                }

                try {
                    $res = $stmt->execute($inParams);
                } catch (PDOException $e) {
                    $res = false;
                } catch (\Throwable $e) {
                    $res = false;
                } catch (Exception $e) {
                    $res = false;
                }

                if ($res === false) {
                    $this->debugMsgs[] = 'fetchAll: execute failed: query=' . $inQuery;
                    $this->debugMsgs[] = 'fetchAll: execute failed: params=' . print_r($inParams,true);
                    $this->debugMsgs[] = 'fetchAll: execute failed: error=' . print_r($stmt->errorInfo(),true);
                    $this->wasError = false;
                    return array();
                }

                $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($records === false) {
                    $this->debugMsgs[] = 'fetchAll: no records: query=' . $inQuery;
                    $this->debugMsgs[] = 'fetchAll: no records: params=' . print_r($inParams,true);
                    return array();
                }

                return $records;
            }


        //----------------------------------------------------------------------
        //-- Executes a query using parameters, and fetches a single record
        //--
        //-- NOTE: this should probably be used when querying against a PRIMARY or UNIQUE keys

            public function fetchRecord(string $inQuery, array $inParams = array())
            {
                $this->wasError = false;

                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'fetchRecord: pdo false: query=' . $inQuery;
                    $this->debugMsgs[] = 'fetchRecord: pdo false: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                try {
                    $stmt = $this->pdo->prepare($inQuery);
                } catch (PDOException $e) {
                    $stmt = false;
                } catch (\Throwable $e) {
                    $stmt = false;
                } catch (Exception $e) {
                    $stmt = false;
                }

                if ($stmt === false) {
                    $this->debugMsgs[] = 'fetchRecord: stmt false: params=' . print_r($inParams,true);
                    $this->wasError = true;
                    return false;
                }

                try {
                    $res = $stmt->execute($inParams);
                } catch (PDOException $e) {
                    $res = false;
                } catch (\Throwable $e) {
                    $res = false;
                } catch (Exception $e) {
                    $res = false;
                }

                if ($res === false) {
                    $res = false;
                    $this->debugMsgs[] = 'fetchRecord: execute failed: query=' . $inQuery;
                    $this->debugMsgs[] = 'fetchRecord: execute failed: params=' . print_r($inParams,true);
                    $this->debugMsgs[] = 'fetchRecord: execute failed: error=' . print_r($stmt->errorInfo(),true);
                    $this->wasError = true;
                    return false;
                }

                return $stmt->fetch(\PDO::FETCH_ASSOC);
            }


        //----------------------------------------------------------------------
        //-- Retrieves the lastInsertID based on the PDO
        
            public function lastInsertID()
            {
                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'lastInsertID: pdo false';
                    return false;
                }

                $recordID = $this->pdo->lastInsertID();

                if ($recordID === false) {
                    return false;
                }

                return intval($recordID);
            }


        //----------------------------------------------------------------------
        //-- Prepares a query

            public function prepare(string $inQuery)
            {
                if ($this->pdo === false) {
                    $this->debugMsgs[] = 'prepare: pdo false: query=' . $inQuery;
                    return false;
                }

                try {
                    $stmt = $this->pdo->prepare($inQuery);
                } catch (PDOException $e) {
                    $stmt = false;
                } catch (\Throwable $e) {
                    $stmt = false;
                } catch (Exception $e) {
                    $stmt = false;
                }

                return $stmt;
            }


        //--------------------------------------------------------------------------------
        //-- Returns value of $wasError which indicates if the last operation had an error

            public function error()
            {
                return $this->wasError;
            }


        //----------------------------------------------------------------------
        //-- Returns value of a PDO attribute

            public function getAttr(string $inAttribute)
            {
                if ($this->pdo === false) {
                    return null;
                }

                return $this->pdo->getAttribute($inAttribute);
            }


        //----------------------------------------------------------------------
        //-- Sets a value of a PDO attribute

            public function setAttr($inAttribute, $inValue)
            {
                if ($this->pdo === false) {
                    return null;
                }

                return $this->pdo->setAttribute($inAttribute,$inValue);
            }


        //----------------------------------------------------------------------
        //-- Attempts to get a row lock on a table 

            public function getRowLock(string $inTable, string $inField, $inValue) {
                $sql = 'SELECT * FROM ' . $inTable . ' WHERE ' . $inField . ' = :' . $inField. ' FOR UPDATE';
                return $this->fetchRecord($sql, array($inField => $inValue));
            }

    }



//---------------------------------------------------------------------------------
//-- Page class used to render content
//---------------------------------------------------------------------------------

    class Page
    {
        protected $pageLang;
        protected $pageCharSet;
        protected $pageTitle;
        protected $formTitle;
        protected $pageViewPort;
        protected $bodyContentFileName;
        protected $pageDirectory;
        protected $headIncludes;
        protected $footIncludes;
        protected $debug;
        protected $htmlTagExtra;
        protected $accessOverride;
        protected $useBodyContentFunction;
    
        public function __construct()
        {
            $this->pageLang = 'en';
            $this->pageCharSet = 'utf-8';
            $this->pageTitle = 'Page Title';
            $this->formTitle = 'Page Title';
            $this->pageViewPort = 'width=device-width, initial-scale=1.0';
            $this->bodyContentFileName = 'display.php';
            $this->pageDirectory = '';
            $this->headIncludes = array();
            $this->footIncludes = array();
            $this->debug = false;
            $this->htmlTagExtra = '';
            $this->accessOverride = false;
            $this->useBodyContentFunction = false;
        } 
    
        public function setBodyContentFileName(string $inFileName)
        {
            $this->bodyContentFileName = $inFileName;
        }
    
        public function setAccessOverride()
        {
            $this->accessOverride = true;
        }
    
        public function debugOn()
        {
            $this->debug = true;
        }
    
        //-- should be overwritten by child class
        protected function userHasAccess()
        {
            return false;
        }
    
        public function buildHeadIncludes()
        {
            //-- should be extended by child class
        }
    
        public function headIncludeSS(string $inURL)
        {
            $this->headIncludes[] = '<link rel="stylesheet" href="' . $inURL . '">';
        }
    
        public function headIncludeJS(string $inURL)
        {
            $this->headIncludes[] = '<script type="text/javascript" src="' . $inURL . '"></script>';
        }
    
        protected function renderHeadIncludes()
        {
            foreach($this->headIncludes as $include) {
                print '    ' . $include . PHP_EOL;
            }
        }
    
        //-- should be extended by child class
        protected function buildFootIncludes()
        {
        }
    
        public function footIncludeJS(string $inURL)
        {
            $this->footIncludes[] = '<script type="text/javascript" src="' . $inURL . '"></script>';
        }
    
        protected function renderFootIncludes()
        {
            foreach($this->footIncludes as $include) {
                print '    ' . $include . PHP_EOL;
            }
        }
    
        protected function renderHeadContent()
        {
            $this->renderHeadIncludes();
        }
    
        protected function renderHead()
        {
            $this->buildHeadIncludes();
            print'  <head>' . PHP_EOL;
            print'    <meta charset="' . $this->pageCharSet . '" />' . PHP_EOL;
            print'    <title>' . $this->pageTitle . '</title>' . PHP_EOL;
            print'    <meta name="viewport" content="' . $this->pageViewPort . '" />' . PHP_EOL;
            $this->renderHeadContent();
            print'  </head>' . PHP_EOL;
        }
  
        protected function bodyContent()
        {
            // if used, should be overridden by child class
        }
 
        protected function renderBodyContent()
        {
            if ($this->useBodyContentFunction) {
                $this->bodyContent();
            } else {
                include($this->pageDirectory . $this->bodyContentFileName);
            }
        }

        protected function renderPostBody()
        {
            // intended to be overridden by child class
        }
 
        protected function renderBody()
        {
            print '  <body>' . PHP_EOL;
            $this->renderBodyContent();
            $this->renderPostBody();
            $this->renderFootIncludes();
            print '  </body>' . PHP_EOL;
        }
    
        public function renderPage()
        {
            if (! $this->buildContent()) {
                \Framework\redirectIndex();
                return;
            }
    
            $this->buildFootIncludes();
            print '<!DOCTYPE html>' . PHP_EOL;
            print '<html lang="en"';
            if ($this->htmlTagExtra != '') {
                print ' ' . $this->htmlTagExtra;
            }
            print '>' . PHP_EOL;
            $this->renderHead();
            $this->renderBody();
            print '</html>' . PHP_EOL;
        }
    
        public function render()
        {
            if (! $this->accessOverride) {
                if (! $this->userHasAccess()) {
                    \Framework\redirectIndex();
                    return;
                }
            }
    
            $this->renderPage();
        }
    
        protected function buildContent()
        {
            //-- should be extended by child class
            return true;
        }
    
    }



//------------------------------------------------------------------------------
//-- Application Request Process
//--
//-- Provides a function that standardizes the process of interpreting an 
//-- application request for applications.  Based on the URL Path, 
//-- the appropriate script is selected to run.
//------------------------------------------------------------------------------
    
    define('ERROR_404', 404);
    define('ERROR_500', 500);
    
    class Request
    {
        protected static $debugLog = array();
    
        protected static $isPOST;
        protected static $isHTTPS;
        protected static $uri;
        protected static $urlPath;
        protected static $scriptPath;
        protected static $origScriptPath;
        protected static $queryString;
        protected static $queryParameters;
    
        public static $user;
        public static $db;
    
    
        //----------------------------------------------------------------------------
        // Function for clearing, setting and retrieving maintenance message

            public static function clearMaintenanceMessage()
            {
                unset($_SESSION['MAINTENANCE_MESSAGE']);
            }

            public static function setMaintenanceMessage(string $inMessage)
            {
                $_SESSION['MAINTENANCE_MESSAGE'] = $inMessage;
            }

            public static function getMaintenanceMessage()
            {
                if (array_key_exists('MAINTENANCE_MESSAGE', $_SESSION)) {
                    return $_SESSION['MAINTENANCE_MESSAGE'];
                }

                return '';
            }

            public static function goMaintenance(string $inMessage = '')
            {
                self::setMaintenanceMessage($inMessage);
                header('Location: ' . APP_URL_PREFIX . 'maintenance');
            }


        //----------------------------------------------------------------------------
        // Initialize the Request
        //
        // The following defines area required
        // - APP_ROOT
   
            public static function init()
            {
                //-- Clear the maintenance session setting

                    self::clearMaintenanceMessage();

                //-- Initialize the variables
    
                    self::$isPOST = false;
                    self::$isHTTPS = false;
                    self::$uri = false;
                    self::$urlPath = false;
                    self::$scriptPath = false;
                    self::$origScriptPath = false;
                    self::$queryString = false;
                    self::$queryParameters = array();
    
                    self::$user = new \Framework\User;
                    self::$db = new \Framework\LibPDO;
    
    
                //-- Establish the APP_SERVER_NAME (if not set)
    
                    if (! defined('APP_SERVER_NAME')) {
                        if (array_key_exists('SERVER_NAME', $_SERVER)) {
                            define('APP_SERVER_NAME', $_SERVER['SERVER_NAME']);
                        } else {
                            define('APP_SERVER_NAME', 'localhost');
                        }
                    }
    
                //-- Load common application iniitialization
    
                    if (file_exists(APP_ROOT . 'app/config/app.php')) {
                        require_once(APP_ROOT . 'app/config/app.php');
                    } else {
                        self::goMaintenance('app/config/app.php missing');
                        return false;
                    }
    
                //-- Define cookie domain for application
    
                    define('APP_COOKIE_DOMAIN', APP_SERVER_NAME);
    
                //-- Establish the application protocol
    
                    if (! defined('APP_PROTOCOL')) {
                        define('APP_PROTOCOL', 'https://');
                    }
    
                //-- Define paths to Framework
    
                    if (! defined('CDN_SERVER_NAME')) {
                        define('CDN_SERVER_NAME', APP_SERVER_NAME);
                    }
    
                //-- Enable display of errors
    
                    if (defined('APP_ERRORS_ON')) {
                        ini_set('display_errors', '1');
                        ini_set('display_startup_errors', '1');
                        error_reporting(E_ALL);
                    }
    
                //-- Are we in maintenance mode
    
                    if (MAINTENANCE_MODE) {
                        self::goMaintenance();
                        return false;
                    }
    
                //-- Connect to the database
    
                    if (defined('AUTO_CONNECT_DB') && AUTO_CONNECT_DB) {
                        if (! self::$db->connect()) {
                            self::goMaintenance('failed to connect to database');
                            return false;
                        }
                        self::$db->setAttr(\PDO::ATTR_EMULATE_PREPARES, 0);
                    }
  
                return true; 
            }
   

        //----------------------------------------------------------------------------
        // Initialize the Request for cronRun
        //
        // The following defines area required
        // - APP_ROOT

            public static function cronInit()
            {
                //-- Initialize the variables
    
                    self::$isPOST = false;
                    self::$isHTTPS = false;
                    self::$uri = false;
                    self::$urlPath = false;
                    self::$scriptPath = false;
                    self::$origScriptPath = false;
                    self::$queryString = false;
                    self::$queryParameters = array();
    
                    self::$user = new \Framework\User;
                    self::$db = new \Framework\LibPDO;
    
    
                //-- Load common application iniitialization
    
                    if (file_exists(APP_ROOT . 'app/config/app.php')) {
                        require_once(APP_ROOT . 'app/config/app.php');
                    } else {
                        return false;
                    }
   
 
                //-- Connect to the database
    
                    if (defined('AUTO_CONNECT_DB') && AUTO_CONNECT_DB) {
                        if (! self::$db->connect()) {
                            return false;
                        }
                        self::$db->setAttr(\PDO::ATTR_EMULATE_PREPARES, 0);
                    }
   
                    return true; 
            }


 
    
        //----------------------------------------------------------------------
        //-- getter
    
            public static function __callStatic(string $inMethod, array $inArgs)
            {
                switch($inMethod) {
                    case 'isHTTPS':
                        return self::$isHTTPS;
    
                    case 'isPOST':
                        return self::$isPOST;
    
                    case 'pageURL':
                        $pageURL = \Framework\siteURL(self::$origScriptPath);
    
                        if (count($inArgs) > 0) {
                            if ($inArgs[0] === true) {
                                if (is_string(self::$queryString)) {
                                    $pageURL .= '?' . self::$queryString;
                                }
                            }
                        }
    
                        return $pageURL;
    
                    case 'urlPath':
                        return self::$urlPath;
    
                    case 'scriptPath':
                        return self::$origScriptPath;
    
                    case 'uri':
                        return self::$uri;
                }
    
                return null;
            }
    
    
        //----------------------------------------------------------------------------
        //-- Determines if scriptPath is exempt from requiring login
    
            protected static function isLoginExemptPath(string $inPath)
            {
                if ($inPath == APP_LOGIN_PATH) {
                    return true;
                }
    
                if (! defined('APP_LOGIN_EXEMPT')) {
                    return false;
                }
    
                if (! is_array(APP_LOGIN_EXEMPT)) {
                    return false;
                }
    
                if (array_key_exists(self::$scriptPath, APP_LOGIN_EXEMPT)) {
                    return true;
                }
    
                return false;
            }
    
    
        //----------------------------------------------------------------------------
        //-- Return a query parameter
    
            public static function getQP(string $inName)
            {
                if (! is_string($inName)) {
                    return false;
                }
    
                if (! array_key_exists($inName, self::$queryParameters)) {
                    return false;
                }

                return self::$queryParameters[$inName];
            }
    
    
        //---------------------------------------------------------------------------
        //-- Process a boolean query parameter, and stores collected value in SESSION
    
            public static function getBoolQP(string $inName)
            {
                $inName = trim($inName);
    
                if ($inName == '') {
                    return false;
                }
    
                $boolValue = self::getQP($inName);
    
                if ($boolValue === false) {
                    if (array_key_exists($inName, $_SESSION)) {
                        $boolValue = $_SESSION[$inName];
                    } else {
                        $boolValue = false;
                    }
                } else {
                    if ($boolValue == 'Y') {
                        $boolValues = true;
                    } else {
                        $boolValue = false;
                    }
                }
    
                $_SESSION[$inName] = $boolValue;
    
                return $boolValue;
            }
    
    
        //---------------------------------------------------------------------------
        //-- Process an option query parameter, and stores collected value in SESSION
    
            public static function getOptionQP(string $inName, array $inOptions, string $inDefaultOption)
            {
                $inName = trim($inName);
    
                if ($inName == '') {
                    return $inDefaultOption;
                }
    
                $optionValue = self::getQP($inName);
    
                if ($optionValue === false) {
                    if (array_key_exists($inName, $_SESSION)) {
                        $optionValue = $_SESSION[$inName];
                    } else {
                        $optionValue = $inDefaultOption;
                    }
                }
    
                if (! in_array($optionValue, $inOptions)) {
                    $optionValue = $inDefaultOption;
                }
    
                $_SESSION[$inName] = $optionValue;
    
                return $optionValue;
            }
    
    
        //----------------------------------------------------------------------
        //-- Return a POST parameter
    
            public static function getPP(string $inParameterName, bool $inSanitize = true)
            {
                if (! self::$isPOST) {
                    return false;
                }
    
                $inParameterName = trim($inParameterName);
    
                if ($inParameterName == '') {
                    return false;
                }
    
                if (! array_key_exists($inParameterName, $_POST)) {
                    return false;
                }
    
                if ($inSanitize !== true) {
                    return $_POST[$inParameterName];        
                }
    
                return htmlspecialchars($_POST[$inParameterName]);
            }
    
    
        //----------------------------------------------------------------------------
        //-- Based on error page type, pull in the user defined error page (if defined)
    
            public static function errorPage(int $inErrorPage)
            {
                switch($inErrorPage)
                {
                    case ERROR_404:
                        if (! defined('ERROR_404_PAGE')) {
                            print '404 Page Not Found';
                        } else {
                            header('Location: ' . \Framework\siteURL(ERROR_404_PAGE));
                        }
                        break;
    
                    case ERROR_500:
                        if (! defined('ERROR_500_PAGE')) {
                            print '500 Server Error';
                        } else {
                            header('Location: ' . \Framework\siteURL(ERROR_500_PAGE));
                        }
                        break;
    
                    default:
                        print '500 Server Error';
                }
    
            }
    
    
        //----------------------------------------------------------------------------
        //-- Return the query parameters
    
            public static function getQueryParameters()
            {
                return self::$queryParameters;
            }
    
    
        //----------------------------------------------------------------------------
        // Process a Request
    
            public static function process()
            {
                //-- set request type
    
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        self::$isPOST = true;
                    }
    
                //-- set if https
    
                    if (strtolower($_SERVER['REQUEST_SCHEME']) === 'https') {
                        self::$isHTTPS = true;
                    }
    
                //-- Make sure we are using HTTPS (if required)
    
                    if (! self::$isHTTPS) {
                        if ((! defined('REQUIRE_HTTPS')) || (REQUIRE_HTTPS !== false)) {
                            self::errorPage(ERROR_500);
                            return false;
                        }
                    }
    
                //-- Parse the url request into its components
                //-- This framework only supports redirect processing
                //-- THEREFORE, we must have a REQUEST_URI to function
    
                    if (! array_key_exists('REQUEST_URI', $_SERVER)) {
                        self::errorPage(ERROR_500);
                        return false;
                    }
    
                    self::$uri = $_SERVER['REQUEST_URI'];
                    self::$urlPath = parse_url(self::$uri, PHP_URL_PATH);
    
                    if ((self::$urlPath === null) || (self::$urlPath === false)) {
                        self::errorPage(ERROR_500);
                        return false;
                    }
    
                //-- Remove the application url prefix to normalize the page request
    
                    if (substr(self::$urlPath,0,strlen(APP_URL_PREFIX)) != APP_URL_PREFIX) {
                        self::errorPage(ERROR_500);
                        return false;
                    }
    
                    self::$scriptPath = substr(self::$urlPath,strlen(APP_URL_PREFIX));
                    self::$origScriptPath = self::$scriptPath;
    
                //-- If it is empty or index.php then assume asking for index page
    
                    if ((self::$scriptPath == '') || (self::$scriptPath == 'index.php')) {
                        self::$scriptPath = 'index';
                    }
    
                //-- make sure that the requested page begins with only letters (lower/upper)
    
                    if (preg_match('/^[a-zA-Z]+/', self::$scriptPath) != 1) {
                        \Framework\redirectIndex();
                        return false;
                    }
    
                //-- Process the query parameters
    
                    self::$queryString = parse_url(self::$uri, PHP_URL_QUERY);
    
                    if (is_string(self::$queryString)) {
                        parse_str(self::$queryString, self::$queryParameters);
                    }
    
                //-- Check to see if login is required to access the pages
    
                    if (! defined('APP_REQUIRE_LOGIN')) {
                        self::errorPage(ERROR_500);
                        return false;
                    }
    
                //-- Attempt to load the user information from the session
    
                    if (! self::$user->loadFromSession()) {
                        if (APP_REQUIRE_LOGIN !== false) {
                            if (! self::isLoginExemptPath(self::$scriptPath)) {
                                if (! defined('APP_LOGIN_PATH')) {
                                    self::errorPage(ERROR_500);
                                    return false;
                                }
    
                                $fqsp = APP_PAGES_ROOT . APP_LOGIN_PATH . '/script.php';
    
                                if (! file_exists($fqsp)) {
                                    self::errorPage(ERROR_500);
                                    return false;
                                }
    
                                if (! array_key_exists('login_request_uri', $_SESSION)) {
                                    $_SESSION['login_request_uri'] = self::$uri;
                                    // NOTE: login process should clear this value
                                }
    
                                \Framework\redirectPage(APP_LOGIN_PATH);
                                return false;
                            }
                        }
                    }
    
    
                //-- See if we are going to do a rewrite
                //-- This allows for a single script to handle multiple
                //-- Variations of a URL
    
                    if (defined('APP_REWRITES')) {
                        if (is_array(APP_REWRITES)) {
                            foreach (APP_REWRITES as $key => $target) {
                                $l = strlen($key);
                                if (substr(self::$scriptPath,0,$l) === $key) {
                                    self::$scriptPath = $target;
                                    break;
                                }
                            }
                        }
                    }
    
    
                //-- Build the script path
    
                    $fqsp = APP_PAGES_ROOT . self::$scriptPath . '/script.php';
    
    
                //-- Check to make sure the script is available
    
                    if (! file_exists($fqsp)) {
                        if ($fqsp === APP_PAGES_ROOT . 'index/script.php') {
                            self::errorPage(ERROR_500);
                            return false;
                        }
    
                        if (defined('APP_PASS_NOT_FOUND_TO')) {
                            $fqsp = APP_PAGES_ROOT . APP_PASS_NOT_FOUND_TO . '/script.php';
                        } else {
                            \Framework\redirectIndex();
                            return false;
                        }
                    }
    
                    if (self::$scriptPath == 'index') {
                        self::$scriptPath = '';
                        self::$origScriptPath = '';
                    }   
    
    
                //-- pull in the target script
    
                    return $fqsp;
            }
    
    }




//---------------------------------------------------------------------------------
//-- Class used to run "script" but still exit back to index page
//-- This is typically used to run a script and then return back to its origin
//-- based on the Navigation Stack
//---------------------------------------------------------------------------------

    class Script
    {
        protected $accessOverride;
        protected $redirectPage;

        public function __construct()
        {
            $this->accessOverride = false;
            $this->redirectPage = \Framework\NavStack::getBackURL(true);
        }

        public function setAccessOverride()
        {
            $this->accessOverride = true;
        }
    
        public function debugOn()
        {
            $this->debug = true;
        }
    
        //-- should be overwritten by child class
        protected function userHasAccess()
        {
            return false;
        }
    
        //-- should be overwritten by child class
        protected function run()
        {
            return;
        }

        public function process()
        {
            if (! $this->accessOverride) {
                if (! $this->userHasAccess()) {
                    \Framework\redirectPage($this->redirectPage);
                    return;
                }
            }

            $this->run();

            \Framework\redirectPage($this->redirectPage);
        }

    }



//---------------------------------------------------------------------------------
//-- Defines a table interface to interact with MySQL tables
//---------------------------------------------------------------------------------

    class TableInterface
    {
        protected $dbName;
        protected $tableName;
        protected $asName;
        protected $withFile;
        protected $withFileField;
        protected $indexField;
        protected $relatedField;

        protected $selectExpressions;
        protected $joinClauses;
        protected $whereExpressions;
        protected $groupBy;
        protected $orderBy;
        protected $limitRows;
        protected $queryOffset;

        protected $changeLog = false;           //-- this value needs to be set in the child class
        protected $logTableTag = false;         //-- this value needs to be set in the child class

        //----------------------------------------------------------------------
        //-- Constructor

            public function __construct()
            {
                $this->dbName        = 'unknown';
                $this->tableName     = 'unknown';
                $this->asName        = 'x';
                $this->withFile      = false;
                $this->withFileField = 'file_blob';
                $this->indexField    = 'id';
                $this->relatedField  = 'unknown_related_field';

                $this->selectExpressions = array();
                $this->joinClauses       = array();
                $this->whereExpressions  = array();
                $this->groupBy           = false;
                $this->orderBy           = false;
                $this->limitRows         = 0;
                $this->queryOffset       = 0;

                $this->reset();
            }


        //----------------------------------------------------------------------
        //-- Resets the object

            public function reset()
            {
                //-- this should defined by child class
            }


        //----------------------------------------------------------------------
        //-- Returns the asName

            public function asName()
            {
                return $this->asName;
            }


        //----------------------------------------------------------------------
        //-- Returns the fields for a table
        //--
        //-- NOTE: this function needs to be overridden by child class
        //--       to return actual fields.  This function
        //--       just returns an empty array
    
            public function getFieldNames()
            {
                return array();
            }
    

        //----------------------------------------------------------------------
        //-- Returns the field label for a record field
        //--
        //-- NOTE: this function needs to be overridden by child class
        //--       to return an actual field label.  This function
        //--       just returns the field name passed in

            public function getFieldLabelText(string $inFieldName)
            {
                return $inFieldName;
            }

    
        //----------------------------------------------------------------------
        //-- Returns a field list that can be used with a SELECT

            public function selectFieldList(bool $inAsArray = false)
            {
                $fieldNames = $this->getFieldNames();

                foreach($fieldNames as $ndx => $fName) {
                    if ($fName == $this->withFileField) {
                        if ($this->withFile !== true) {
                            unset($fieldNames[$ndx]);
                            continue;
                        }
                    }
                    $fieldNames[$ndx] = $this->asName . '.' . $fName;
                }

                if (count($fieldNames) == 0) {
                    $fieldNames[0] = $this->asName . '.*';
                }

                if ($inAsArray === true) {
                    return $fieldNames;
                }

                return implode(',', $fieldNames);
            }


        //----------------------------------------------------------------------
        //-- Returns a FROM string with the table name and asName specified

            public function sqlFromClause()
            {
                return ' FROM ' . $this->dbName . '.' . $this->tableName . ' AS ' . $this->asName;
            }


        //----------------------------------------------------------------------
        //-- Function used by ListInterface 

            public function fetchSQL()
            {
                $sql  = 'SELECT ';

                if (count($this->selectExpressions) == 0) {
                    $sql .= $this->selectFieldList();
                } else {
                    $sql .= implode(',', $this->selectExpressions);
                }

                $sql .= $this->sqlFromClause();

                if (count($this->joinClauses) > 0) {
                    $sql .= ' ' . implode(' ', $this->joinClauses);
                }

                if (count($this->whereExpressions) > 0) {
                    $sql .= ' WHERE ' . implode(' ', $this->whereExpressions);
                }

                if ($this->groupBy != false) {
                    $sql .= ' GROUP BY ' . $this->groupBy;
                }

                if ($this->orderBy != false) {
                    $sql .= ' ORDER BY ' . $this->orderBy;
                }

                if ($this->limitRows > 0) {
                    $sql .= ' LIMIT ' . $this->limitRows;
                }

                if ($this->queryOffset > 0) {
                    $sql .= ' OFFSET ' . $this->queryOffset;
                }

                return $sql;
            }


        //----------------------------------------------------------------------
        //-- Used to toggle the withFile parameter so that sql can be
        //-- adjusted if record contains a file blob

            public function withFile()
            {
                $this->withFile = true;
            }


        //----------------------------------------------------------------------
        //-- Set custom select expressions

            public function selectExpressions(array $inExpressions)
            {
                $this->selectExpressions = $inExpressions;
            }


        //----------------------------------------------------------------------
        //-- Set custom join clauses

            public function joinClauses(array $inClauses)
            {
                $this->joinClauses = $inClauses;
            }


        //----------------------------------------------------------------------
        //-- Set custom where clauses

            public function whereExpressions(array $inExpressions)
            {
                $this->whereExpressions = $inExpressions;
            }


        //----------------------------------------------------------------------
        //-- Set custom group by 

            public function groupBy(string $inGroupBy)
            {
                $this->groupBy = $inGroupBy;
            }


        //----------------------------------------------------------------------
        //-- Set custom order by 

            public function orderBy(string $inOrderBy)
            {
                $this->orderBy = $inOrderBy;
            }


        //----------------------------------------------------------------------
        //-- Limit number of rows returned

            public function limitRows(int $inLimitRows)
            {
                $this->limitRows = $inLimitRows;
            }


        //----------------------------------------------------------------------
        //-- Set query offset

            public function queryOffset(int $inQueryOffset)
            {
                $this->queryOffset = $inQueryOffset;
            }


        //----------------------------------------------------------------------
        //-- Normalizes a record field value
        //--
        //-- This function is intended to be used to TableRecord and TableList
        //-- but is included here to be available to both
        //--
        //-- If method exists with the name <fieldName>Normalize,
        //-- then the method is called to retrieve the normalized
        //-- value; otherwise, the record[<fieldName>] value
        //-- is returned
    
            public function normalize(string $inFieldName, $inFieldValue=null)
            {
                $inFieldName = trim($inFieldName);

                if ($inFieldName == '') {
                    return false;
                }

                if ($inFieldValue === null) {
                    $currentRecord = $this->record();

                    if ($currentRecord === false) {
                        return false;
                    }
                    if (! array_key_exists($inFieldName, $currentRecord)) {
                        return false;
                    }
                    $inFieldValue = $currentRecord[$inFieldName];
                }

                $nMethod = $inFieldName . 'Normalize';

                if (! method_exists($this, $nMethod)) {
                    return $inFieldValue;
                }

                return $this->$nMethod($inFieldValue);
            }

 
        //----------------------------------------------------------------------
        //-- Returns a record with all of the field values normalized
        //--
        //-- This function is intended to be used by TableRecord and TableList
        //-- but is included here to be available to both
 
            public function normalizeRecord(array $inRecord=null, bool $inNormalizeSkip=false)
            {
                if ($inRecord === null) {
                    if ($this->record === false) {
                        return false;
                    }
                    $inRecord = $this->record;
                } else {
                    if (! is_array($inRecord)) {
                        return false;
                    }
                }

                if (! is_array($inNormalizeSkip)) {
                    $inNormalizeSkip = array();
                }
    
                foreach($inRecord as $key => $value) {
                    if (! in_array($key, $inNormalizeSkip)) {
                        $inRecord[$key] = $this->normalize($key,$value);
                    }
                }
    
                return $inRecord;
            }
   
 
        //----------------------------------------------------------------------
        //-- Remove fields from being logged
        //-- This function should be overridden by child class if fields need
        //-- to be removed before logging

            protected function filterLogFields(&$inFields)
            {
                return;
            }
   

    }



//---------------------------------------------------------------------------------
//-- Defines a list class to work with tables
//---------------------------------------------------------------------------------

    trait TableListInterface
    {
        protected $record;
        protected $records;
        protected $wasError;
        protected $currentNdx;

        //----------------------------------------------------------------------
        //-- Resets the object

            public function reset()
            {
                parent::reset();
                $this->record = false;
                $this->records = array();
                $this->wasError = false;
                $this->currentNdx = -1;
            }


        //----------------------------------------------------------------------
        //-- Returns true/false if there was an erorr during the fetch

            public function error()
            {
                return $this->wasError;
            }


        //----------------------------------------------------------------------
        //-- Returns the value from the record field OR NULL
    
            public function __get(string $inName)
            {
                if ($this->currentNdx < 0) {
                    return NULL;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return NULL;
                }

                if ($inName != trim($inName)) {
                    return NULL;
                }
    
                if ($inName == '') {
                    return NULL;
                }
    
                if (! array_key_exists($inName, $this->records[$this->currentNdx])) {
                    return NULL;
                }
    
                return $this->records[$this->currentNdx][$inName];
            }




        //----------------------------------------------------------------------
        //-- Query for records

            protected function fetchRecords(string $inSQL, array $inParams = array())
            {
                $this->record = false;
                $this->records = array();
                $this->wasError = false;
                $this->currentNdx = -1;
    
                if (! is_string($inSQL)) {
                    return false;
                }

                if (! is_array($inParams)) {
                    $inParams = array();
                }

                $this->records = \App\Request::$db->fetchAll($inSQL,$inParams);

                if (\App\Request::$db->error()) {
                    $this->records = array();
                    $this->wasError = true;
                    return false;
                }
    
                return true;
            }


        //----------------------------------------------------------------------
        //-- Query for records

            public function query(array $inParams = array())
            {
                return $this->fetchRecords($this->fetchSQL(), $inParams);
            }


        //----------------------------------------------------------------------
        //-- Return the number of records retrieved

            public function count()
            {
                return count($this->records);    
            }
   
 
        //----------------------------------------------------------------------
        //-- Clears the record set

            public function rewind()
            {
                $this->currentNdx = -1;
                $this->record - false;
            }
  
 
        //----------------------------------------------------------------------
        //-- Returns the current ndx into the _records array

            public function currentNdx()
            {
                return $this->currentNdx;
            }


        //----------------------------------------------------------------------
        //-- Returns boolean indicating if currentNdx is at the end of the array

            public function atEnd()
            {
                if ($this->currentNdx >= count($this->records)) {
                    return true;
                }

                return false;
            }


        //----------------------------------------------------------------------
        //-- Advances the current index into the record set by 1
        //--   NOTE: initially the current index is pointing before (-1) the
        //--         first record in the record set (0)

            public function next()
            {
                $this->currentNdx++;
                $this->record = $this->record();
                return ! $this->atEnd();
            }


        //----------------------------------------------------------------------
        //-- "Unsets" the record in the records array indicated by the current
        //-- ndx value

            public function unsetRecord()
            {
                if ($this->currentNdx < 0) {
                    return false;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return false;
                }

                array_splice($this->records, $this->currentNdx, 1);

                $this->currentNdx--;
                $this->record = $this->record();

                return true;
            }


        //----------------------------------------------------------------------
        //-- Returns the current record of the record set
 
            public function record()
            {
                if ($this->currentNdx < 0) {
                    return false;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return false;
                }

                return $this->records[$this->currentNdx];
            }
   

        //----------------------------------------------------------------------
        //-- Returns the record set
 
            public function records()
            {
                return $this->records;
            }


        //----------------------------------------------------------------------
        //-- Used to "mark" records for application use

            public function mark()
            {
                if ($this->currentNdx < 0) {
                    return;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return;
                }

                $this->records[$this->currentNdx]['__mark__'] = true;
            } 


            public function unmark()
            {
                if ($this->currentNdx < 0) {
                    return;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return;
                }

                $this->records[$this->currentNdx]['__mark__'] = true;
            }
 
            public function marked()
            {
                if ($this->currentNdx < 0) {
                    return false;
                }

                if ($this->currentNdx >= count($this->records)) {
                    return false;
                }

                if (! array_key_exists('__mark__', $this->records[$this->currentNdx])) {
                    return false;
                }

                return $this->records[$this->currentNdx]['__mark__'];
            } 

    }



//---------------------------------------------------------------------------------
//-- Defines a common record interface to work with PDO tables
//---------------------------------------------------------------------------------

    trait TableRecordInterface
    {
        protected $record = false;
        protected $wasError = false;
        protected $debug = false;
        protected $insertAllowed = false;
        protected $updateAllowed = false;
        protected $deleteAllowed = false;
        protected $lastInsertID = 0;
        protected $logIncludeFields = array();
        protected $logSkipIfOnlyFields = array();

        //----------------------------------------------------------------------
        //-- Resets the object

            public function reset()
            {
                parent::reset();
                $this->record = false;
                $this->wasError = false;
            }


        //----------------------------------------------------------------------
        //-- Returns true/false if there was an erorr during the fetch

            public function error()
            {
                return $this->wasError;
            }


        //----------------------------------------------------------------------
        //-- Turns on debuggin

            public function debug()
            {
                $this->debug = true;
            }


        //----------------------------------------------------------------------
        //-- Returns the value from the record field OR NULL
    
            public function __get(string $inName)
            {
                if ($this->record === false) {
                    return NULL;
                }
    
                if ($inName != trim($inName)) {
                    return NULL;
                }
    
                if ($inName == '') {
                    return NULL;
                }
    
                if (! array_key_exists($inName, $this->record)) {
                    return NULL;
                }
    
                return $this->record[$inName];
            }


            public function __set(string $inName, $inValue)
            {
                if ($this->record === false) {
                    return;
                }
    
                if ($inName != trim($inName)) {
                    return;
                }
    
                if ($inName == '') {
                    return;
                }
    
                if (! array_key_exists($inName, $this->record)) {
                    return;
                }
   
                $this->record[$inName] = $inValue;
            }


        //----------------------------------------------------------------------
        //-- Returns if record contains data

            public function empty()
            {
                if ($this->record === false) {
                    return true;
                }
                if (count($this->record) == 0) {
                    return true;
                }
                return false;
            }


        //----------------------------------------------------------------------
        //-- Returns the record insert ID

            public function lastInsertID()
            {
                return $this->lastInsertID;
            }


        //----------------------------------------------------------------------
        //-- Returns the current record

            public function record()
            {
                return $this->record;
            }
   
 
        //----------------------------------------------------------------------
        //-- Find a single record

            protected function findRecord(string $inSQL, array $inParams = array())
            {
                $this->record = false;
                $this->wasError = false;
    
                if (! is_string($inSQL)) {
                    return false;
                }

                if (! is_array($inParams)) {
                    $inParams = array();
                }

                $this->record = \App\Request::$db->fetchRecord($inSQL,$inParams);

                if (\App\Request::$db->error()) {
                    $this->record = false;
                    $this->wasError = true;
                    return false;
                }
    
                if ($this->record === false) {
                    return false;
                }
      
                return true;
            }

    
        //----------------------------------------------------------------------
        //-- Finds a record using the given params and the current fetchSQL

            public function findBy(array $inParams)
            {
                return $this->findRecord($this->fetchSQL(), $inParams);
            }


        //----------------------------------------------------------------------
        //-- Finds a record by ID

            public function findByID($inID)
            {
                $this->whereExpressions[] = $this->asName . '.' . $this->indexField . ' = :' . $this->indexField;
                $rc = $this->findRecord($this->fetchSQL(), array($this->indexField => $inID));
                return $this->findRecord($this->fetchSQL(), array($this->indexField => $inID));
            }


        //----------------------------------------------------------------------
        //-- Returns SQL for inserting a record into a table
    
            protected function insertSQL(array $inInsertFields)
            {
                $keys = array_keys($inInsertFields);

                $fields = implode(',',$keys);

                foreach($keys as $key => $value) {
                    $keys[$key] = ':' . $value;
                }

                $values = implode(',',$keys);

                return 'INSERT INTO ' . $this->tableName . ' (' . $fields . ') VALUES (' . $values . ')';
            }
    
    
        //----------------------------------------------------------------------
        //-- Inserts a record into the table
        //--
        //-- inRelatedKey    -> key from another table related to change log entry
        //-- inRecordFIeldID -> Field in the inInsertFields that holds the primary key value.
        //--                    This is used when establishing the primary record id by a
        //--                    known value rather than have MariaDB generate an insert id
    
            public function insert(array $inInsertFields, string $inRelatedKey = null, string $inRecordIDField = null)
            {
                //-- must be allowed

                    if ($this->insertAllowed !== true) {
                        if ($this->debug) {
                            print '[insert:insert not allowed]';
                        }
                        return false;
                    }

                //-- make sure we have everything we need
    
                    $this->lastInsertID = 0;
   
                    if (! is_array($inInsertFields)) {
                        if ($this->debug) {
                            print '[insert:inInsertFields not array]';
                        }
                        return false;
                    }

                //-- If indexField is in the field list, indicate that insertID was given

                    if ($inRecordIDField === null) {
                        if (array_key_exists($this->indexField, $inInsertFields)) {
                            $inRecordIDField = $this->indexField;
                        }
                    }
  

                //-- remove fields not part of the record

                    $keys = array_keys($inInsertFields);

                    $fieldNames = array_flip($this->getFieldNames());

                    // remove "fields" that do not exist in the record
                    foreach($keys as $ndx => $fName) {
                        if (! array_key_exists($fName, $fieldNames)) {
                            unset($inInsertFields[$fName]);
                        }
                    }

 
                //-- insert the record

                    $sql = $this->insertSQL($inInsertFields);

                    if (! \App\Request::$db->execute($sql, $inInsertFields)) {
                        if ($this->debug) {
                            print '[insert:execute failed]';
                        }
                        return false;
                    }


                //-- retrieve the last insert id
    
                    $this->lastInsertID = \App\Request::$db->lastInsertID();
 
                    //-- this should never happen unless primary key was passed as param with 0 value
                    if ($this->lastInsertID == 0) {
                        if ($inRecordIDField === null) {
                            if ($this->debug) {
                                print '[insert:lastInsertID is 0 with no inRecordIDField]';
                            }
                            return false;
                        }
    
                        if (! array_key_exists($inRecordIDField, $inInsertFields)) {
                            if ($this->debug) {
                                print '[insert:inRecordIDField does not exist inInsertFields]';
                            }
                            return false;
                        }

                        // primary key was passed inside of the inInserFields array
                        $this->lastInsertID = intval($inInsertFields[$inRecordIDField]);
                    } else {
                        if ($this->lastInsertID === false) {
                            $this->lastInsertID = 0;
                            if ($this->debug) {
                                print '[insert:lstInsertID is false]';
                            }
                            return false;
                        }

                        // valid last insert id returned    
                        $inInsertFields[$this->indexField] = $this->lastInsertID;
                    }
  
 
                //-- Insert worked, move values into record

                    $this->record = $inInsertFields;

                //-- Add a change log (unless disabled)

                    if ($this->changeLog === false) {
                        return true;
                    }
    
                    $this->filterLogFields($inInsertFields);
   
                    // find the related key for logging 
                    if ($inRelatedKey === null) {
                        if ($this->relatedField == $this->indexField) {
                            $inRelatedKey = $this->lastInsertID;
                        } else {
                            $inRelatedKey = $this->record[$this->relatedField];
                        }
                    }

                    $logFields = array();

                    $normalizedNewRecord = $this->normalizeRecord($inInsertFields);

                    foreach($normalizedNewRecord as $fieldName => $fieldValue) {
                        if ($fieldName == 'last_updated_on') {
                            continue;
                        }

                        $logFields[$fieldName] = array(
                            'label' => $this->getFieldLabelText($fieldName),
                            'old'   => '',
                            'new'   => $normalizedNewRecord[$fieldName]
                        );
                    }

                    if ($this->debug) {
                        $this->changeLog->debugOn();
                    }

                    if (! $this->changeLog->logFields(CHANGE_LOG_TYPE_ADD, 
                                                        $this->logTableTag, 
                                                        strval($inInsertFields[$this->indexField]),
                                                        strval($inRelatedKey),
                                                        $logFields)) {
                        if ($this->debug) {
                            print '[insert:changeLog failed]';
                        }
                        return false;
                    }

                    return true;
            }
    
    
        //----------------------------------------------------------------------
        //-- Returns the SQL to update a record based on the parameters passed
        //-- in inParams
    
            protected function updateSQL(array $inParams)
            {
                unset($inParams[$this->indexField]);
    
                $sql  = 'UPDATE ' . $this->tableName . ' SET ';
    
                $setFields = '';
    
                foreach($inParams as $key => $value) {
                    if (! array_key_exists($key, $this->record)) {
                        return false;
                    }
                    $setFields .= ',' . $key . '= :' . $key;
                }
    
                if ($setFields == '') {
                    return false;
                }
    
                $sql .= substr($setFields,1);
                $sql .= ' WHERE ' . $this->indexField . ' = :' . $this->indexField;
    
                return $sql;
            }
    
    
        //----------------------------------------------------------------------
        //-- Updates a record based on the updated fields in Record
    
            public function update(array $inRecord, bool $inNewFieldsOK = false)
            {
                //-- must be allowed
    
                    if ($this->updateAllowed !== true) {
                        if ($this->debug) {
                            print '[update:update not allowed]';
                        }
                        return false;
                    }
    
                //-- make sure we have everything we need
    
                    if ($this->record === false) {
                        if ($this->debug) {
                            print '[update:no record]';
                        }
                        return false;
                    }
    
                    if (! is_array($inRecord)) {
                        if ($this->debug) {
                            print '[update:inRecord not array]';
                        }
                        return false;
                    }
    
                //-- See if there are any changes actually made
    
                    $updateFields = array();
    
                    foreach($inRecord as $key => $value) {
                        if (! array_key_exists($key, $this->record)) {
                            if ($inNewFieldsOK !== true) {
                                if ($this->debug) {
                                    print '[update: field does not exist:' . $key . ']';
                                }
                                return false;
                            }
                            $this->record[$key] = NULL;
                        }
    
                        if ($this->logAllFields == true) {
                            $updateFields[$key] = $value;
                            continue;
                        } 
    
                        if (gettype($this->record[$key]) == 'string') {
                            if (strcmp(strval($inRecord[$key]),$this->record[$key]) != 0) {
                                $updateFields[$key] = $value;
                            }            
                            continue;
                        } 
    
                        if ($inRecord[$key] != $this->record[$key]) {
                            $updateFields[$key] = $value;
                        }            
                    }
    
                    if (count($updateFields) == 0) {
                        //-- no changes made, no need to run sql
                        return true;
                    }

                //-- Update the record
    
                    $sql = $this->updateSQL($updateFields);
    
                    if ($sql === false) {
                        if ($this->debug) {
                            print '[update: no sql]';
                        }
                        return false;
                    }
    
                    $updateFields[$this->indexField] = $this->record[$this->indexField];
    
                    if (! \App\Request::$db->execute($sql, $updateFields)) {
                        if ($this->debug) {
                            print '[update: update execute failed]';
                        }
                        return false;
                    }
    
                //-- Add a change log (unless disabled)
    
                    if ($this->changeLog === false) {
                        return true;
                    }

                //-- Include specific fields from record even if not included in the update
   
                    foreach($this->logIncludeFields as $key => $t) {
                        if (! array_key_exists($key, $updateFields)) {
                            if (array_key_exists($key, $this->record)) {
                                $updateFields[$key] = $this->record[$key];
                            }
                        }
                    }
   
                //-- Exclude specific fields from update
 
                    if (count($this->logSkipIfOnlyFields) > 0) {
                        $foundNonSkipField = false;
                        foreach($updateFields as $key => $value) {
                            if (! array_key_exists($key, $this->logSkipIfOnlyFields)) {
                                $foundNonSkipField = true;
                                break;
                            }
                        }
    
                        if ($foundNonSkipField === false) {
                            //-- no fields included that can't be skipped for logging
                            return true;
                        }
                    }

                //-- prepare the fields                    
    
                    $this->filterLogFields($updateFields);
                    $oldRecord = $this->record;
    
                //-- remove last_updated_on fields
    
                    unset($oldRecord['last_updated_on']);
                    unset($updateFields['last_updated_on']);
    
                //-- remove any field from oldRecord that is not in updateFields
    
                    foreach($oldRecord as $fieldName => $fieldValue) {
                        if (! array_key_exists($fieldName, $updateFields)) {
                            unset($oldRecord[$fieldName]);
                        }
                    }
        
                //-- remove any fields that are the same between
    
                    if ($this->logAllFields !== true) {
                        foreach($updateFields as $fieldName => $fieldValue) {
                            if (array_key_exists($fieldName, $oldRecord)) {
                                if ($oldRecord[$fieldName] == $fieldValue) {
                                    if (! array_key_exists($fieldName, $this->logIncludeFields)) {
                                        unset($updateFields[$fieldName]);
                                        unset($oldRecord[$fieldName]);
                                    }
                                }
                            } else {
                                $oldRecord[$fieldName] = '';
                            }
                        }
                    }
    
                //-- we must have at least one field to log; otherwise, return
    
                    if (count($updateFields) == 0) {
                        return true;
                    }
    
                //-- add the fields to logIncludeFields

                    $logFields = array();
    
                    $normalizedOldRecord = $this->normalizeRecord($oldRecord);
                    $normalizedNewRecord = $this->normalizeRecord($updateFields);
    
                    foreach($normalizedNewRecord as $fieldName => $fieldValue) {
                        $logFields[$fieldName] = array(
                            'label' => $this->getFieldLabelText($fieldName),
                            'old'   => $normalizedOldRecord[$fieldName],
                            'new'   => $normalizedNewRecord[$fieldName]
                        );
                    }
    
                //-- log the change
    
                    if ($this->debug) {
                        $this->changeLog->debugOn();
                    }

                    return $this->changeLog->logFields(CHANGE_LOG_TYPE_EDIT, 
                                                        $this->logTableTag, 
                                                        strval($this->record[$this->indexField]),
                                                        strval($this->record[$this->relatedField]),
                                                        $logFields);
            }


        //----------------------------------------------------------------------
        //-- Remove fields from being logged
        //-- This function should be overridden by child class if fields
        //-- need to be removed before logging

            protected function filterLogFields(&$inFields)
            {
                return;
            }


        //----------------------------------------------------------------------
        //-- Deletes the current record from the table
    
            public function delete()
            {
                //-- must be allowed

                    if ($this->deleteAllowed !== true) {
                        return false;
                    }

                //-- make sure we have everything we need
    
                    if ($this->record === false) {
                        return false;
                    }
    
                //-- delete the record
    
                    $sql = 'DELETE FROM ' . $this->tableName . ' WHERE ' . $this->indexField . ' = :' . $this->indexField;
    
                    if ($sql === false) {
                        return false;
                    }
    
                    if (! \App\Request::$db->execute($sql, array($this->indexField => $this->record[$this->indexField]))) {
                        return false;
                    }
    
                //-- Add a change log (unless disabled)

                    if ($this->changeLog === false) {
                        return true;
                    }

                    $logFields = array();

                    $normalizedOldRecord = $this->normalizeRecord($this->record);
  
                    foreach($normalizedOldRecord as $fieldName => $fieldValue) {
                        $logFields[$fieldName] = array(
                            'label' => $this->getFieldLabelText($fieldName),
                            'old'   => $normalizedOldRecord[$fieldName],
                            'new'   => ''
                        );
                    }

                    if ($this->debug) {
                        $this->changeLog->debugOn();
                    }

                    return $this->changeLog->logFields(CHANGE_LOG_TYPE_DELETE,
                                                    $this->logTableTag, 
                                                    strval($this->record[$this->indexField]),
                                                    strval($this->record[$this->relatedField]),
                                                    $logFields );
                    return true;
            }
    
    
    }



//------------------------------------------------------------------------------
//-- Implements a base user class to represent a logged in user
//-- Applications should extend the class to provide application
//-- specified user tracking (authorization, etc)
//-- NOTE: APP_SESSION_KEY and APP_SESSION_KEY_FIELD must be defined
//------------------------------------------------------------------------------
    
    class User
    {
        protected $userIdentity;
    
        //---------------------------------------------------------------------------------
        //-- Construct the user
    
            public function __construct()
            {
                $this->reset();
            }
    
    
        //---------------------------------------------------------------------------------
        //-- Resets the class variables
    
            public function reset()
            {
                $this->userIdentity = false;
            }
    
    
        //----------------------------------------------------------------------
        //-- Returns the value from the user array
    
            public function __get(string $inName)
            {
                if (! is_string($inName)) {
                    return NULL;
                }
    
                switch($inName) {
                    case '':
                        return NULL;

                    case 'identity':
                        return $this->userIdentity;

                    case 'loggedIn':
                        return ($this->userIdentity === false ? false : true);
                }
    
                return NULL;
            }
    
    
        //----------------------------------------------------------------------
        //-- Sets value in the object -- provided if child class wants to set
        //-- custom parameters
    
            public function __set(string $inName, $inValue)
            {
            }
    
    
        //----------------------------------------------------------------------
        //-- Stores the value in the application session key
    
            public function rememberUser(string $inIdentity, string $inRealIdentity = null)
            {
                if (! defined('APP_SESSION_KEY')) {
                    return;
                }
    
                if (! defined('APP_SESSION_KEY_FIELD')) {
                    return;
                }
    
                $_SESSION[APP_SESSION_KEY][APP_SESSION_KEY_FIELD] = $inIdentity;
    
                if ($inRealIdentity === null) {
                    $inRealIdentity = $inIdentity;
                }
    
                $_SESSION[APP_SESSION_KEY]['realEmail'] = $inRealIdentity;
                return;
            }
    
    
        //---------------------------------------------------------------------------------
        //-- Resets the $user instantiated variable and removes the application session key
    
            public function forgetUser()
            {
                $this->reset();
                if (! defined('APP_SESSION_KEY')) {
                    return;
                } 
                unset($_SESSION[APP_SESSION_KEY]);
            }
    
    
        //-----------------------------------------------------------------------------------------
        //-- Attempts to log user in using the APP_SESSION_KEY_FIELD in the application sesison key
    
            public function loadFromSession()
            {
                $this->reset();
    
                if (! isset($_SESSION)) {
                    return false;
                }
    
                if (! defined('APP_SESSION_KEY')) {
                    return false;
                }
    
                if (! defined('APP_SESSION_KEY_FIELD')) {
                    return false;
                }
    
                if (! array_key_exists(APP_SESSION_KEY, $_SESSION)) {
                    return false;
                }
    
                if (! is_array($_SESSION[APP_SESSION_KEY])) {
                    $this->forgetUser();
                    return false;
                }
    
                if (! array_key_exists(APP_SESSION_KEY_FIELD, $_SESSION[APP_SESSION_KEY])) {
                    $this->forgetUser();
                    return false;
                }
    
                $this->userIdentity = $_SESSION[APP_SESSION_KEY][APP_SESSION_KEY_FIELD];
    
                return true;
            }
    
    
        //----------------------------------------------------------------------
        //-- If impersonation is in use, determines if user is real user
    
            public function isRealUser()
            {
                if (! $this->loggedIn) {
                    return false;
                }
    
                if (! array_key_exists('realEmail', $_SESSION[APP_SESSION_KEY])) {
                    return false;
                }
    
                if ($_SESSION[APP_SESSION_KEY][APP_SESSION_KEY_FIELD] == $_SESSION[APP_SESSION_KEY]['realEmail']) {
                    return true;
                }
    
                return false;
            }
    
    
        //----------------------------------------------------------------------
        //-- If impersonation is in use, returns real user email
    
            public function realUserEmail()
            {
                if (! $this->loggedIn) {
                    return false;
                }
    
                if (array_key_exists('realEmail', $_SESSION[APP_SESSION_KEY])) {
                    return $_SESSION[APP_SESSION_KEY]['realEmail'];
                }
    
                return false;
            }
    
    }



//---------------------------------------------------------------------------------
//-- Define variable state traits
//--
//-- Variable State "classes" allow for the handling of associated values
//--
//-- Traits allow for:
//--    validation
//--    selection list
//--    tag to text look ups
//--    selectability
//--    assignability (allow value to be deprecated)
//---------------------------------------------------------------------------------

    //----------------------------------------------------------------------
    //-- Trait used to define string based "variable states"

        trait VariableState
        {
            protected static $constructed = false;

            protected static function construct()
            {
                self::$constructed = true;
            }

            public static function get()
            {
                return self::$values;
            }

            public static function getValue(string $inValue) {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_string($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        return self::$values[$inValue];
                    }
                }
                return array();
            }

            public static function getText(string $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_string($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        return self::$values[$inValue]['text'];
                    }
                }
                return 'Unknown';
            }

            public static function valid(string $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_string($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        return true;
                    }
                }
                return false;
            }

            public static function selectable(string $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_string($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        if (self::$values[$inValue]['selectable']) {
                            return true;
                        }
                    }
                }
                return false;
            }

            public static function selectList(bool $inIncludeAll = false, bool $inSort = false)
            {
                if (! self::$constructed) {
                    static::construct();
                }

                $selectArray = array();
                foreach(self::$values as $ndx => $ndxArray) {
                    if (($ndxArray['selectable'] === true) || ($inIncludeAll == true)) {
                        $selectArray[$ndx] = $ndxArray['text'];
                    }
                }

                if ($inSort === true) {
                    asort($selectArray);
                }

                return $selectArray;
            }

            public static function assignList(bool $inIncludeAll = false, bool $inSort = false)
            {
                if (! self::$constructed) {
                    static::construct();
                }

                $selectArray = array();
                foreach(self::$values as $ndx => $ndxArray) {
                    if (($ndxArray['assignable'] === true) || ($inIncludeAll == true)) {
                        $selectArray[$ndx] = $ndxArray['text'];
                    }
                }

                if ($inSort === true) {
                    asort($selectArray);
                }

                return $selectArray;
            }

            public static function assignable(string $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_string($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        if (self::$values[$inValue]['assignable']) {
                            return true;
                        }
                    }
                }
                return false;
            }

            public static function keyList()
            {
                if (! self::$constructed) {
                    static::construct();
                }
                return array_keys(self::$values);
            }

        }


    //------------------------------------------------------------------------------------------
    //-- Trait used to define interger based "variable states"

        trait VariableStateInt
        {
            protected static $constructed = false;

            protected static function construct()
            {
                self::$constructed = true;
            }

            public static function get()
            {
                return self::$values;
            }

            public static function getValue(string $inValue) {
                if (! self::$constructed) {
                    static::construct();
                }
                if (array_key_exists($inValue, self::$values)) {
                    return self::$values[$inValue];
                }
                return array();
            }

            public static function getText(int $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (array_key_exists($inValue, self::$values)) {
                    return self::$values[$inValue]['text'];
                }
                return 'Unknown';
            }

            public static function selectable(int $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (array_key_exists($inValue, self::$values)) {
                    if (self::$values[$inValue]['selectable']) {
                        return true;
                    }
                }
                return false;
            }

            public static function selectList(bool $inIncludeAll = false)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                $selectArray = array();
                foreach(self::$values as $ndx => $ndxArray) {
                    if (($ndxArray['selectable'] === true) || ($inIncludeAll == true)) {
                        $selectArray[$ndx] = $ndxArray['text'];
                    }
                }
                return $selectArray;
            }

            public static function assignList(bool $inIncludeAll = false, bool $inSort = false)
            {
                if (! self::$constructed) {
                    static::construct();
                }

                $selectArray = array();
                foreach(self::$values as $ndx => $ndxArray) {
                    if (($ndxArray['assignable'] === true) || ($inIncludeAll == true)) {
                        $selectArray[$ndx] = $ndxArray['text'];
                    }
                }

                if ($inSort === true) {
                    asort($selectArray);
                }

                return $selectArray;
            }

            public static function assignable(int $inValue)
            {
                if (! self::$constructed) {
                    static::construct();
                }
                if (is_int($inValue)) {
                    if (array_key_exists($inValue, self::$values)) {
                        if (self::$values[$inValue]['assignable']) {
                            return true;
                        }
                    }
                }
                return false;
            }

            public static function keyList()
            {
                if (! self::$constructed) {
                    static::construct();
                }
                return array_keys(self::$values);
            }

        }


//---------------------------------------------------------------------------------
//-- Definitions and functions for mime types related to file
//--
//-- Variable State for file type representation within the application
//---------------------------------------------------------------------------------

    define('FILE_MIME_TYPE_NONE',        'none');

    define('FILE_MIME_TYPE_CSS',         'text/css');
    define('FILE_MIME_TYPE_DOC',         'application/msword');
    define('FILE_MIME_TYPE_DOCX',        'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    define('FILE_MIME_TYPE_DOTX',        'application/vnd.openxmlformats-officedocument.wordprocessingml.template');
    define('FILE_MIME_TYPE_HTML',        'text/html');
    define('FILE_MIME_TYPE_JS',          'text/javascript');
    define('FILE_MIME_TYPE_JPEG',        'image/jpeg');
    define('FILE_MIME_TYPE_PDF',         'application/pdf');
    define('FILE_MIME_TYPE_PNG',         'image/png');
    define('FILE_MIME_TYPE_TEXT_CSV',    'text/csv');
    define('FILE_MIME_TYPE_TEXT_PLAIN',  'text/plain');
    define('FILE_MIME_TYPE_TIFF',        'image/tiff');
    define('FILE_MIME_TYPE_XLS',         'application/vnd.ms-excel');
    define('FILE_MIME_TYPE_XLSX',        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    define('FILE_MIME_TYPE_XLTM',        'application/vnd.ms-excel.template.macroenabled.12');
    define('FILE_MIME_TYPE_ZIP',         'applicaion/zip');

    define('FILE_MIME_TYPE_AAC',         'audio/aac');
    define('FILE_MIME_TYPE_CDA',         'application/x-cdf');
    define('FILE_MIME_TYPE_MID',         'audio/midi');
    define('FILE_MIME_TYPE_MIDI',        'audio/x-midi');
    define('FILE_MIME_TYPE_MP3',         'audio/mpeg');
    define('FILE_MIME_TYPE_OGA',         'audio/ogg');
    define('FILE_MIME_TYPE_OPUS',        'audio/opus');
    define('FILE_MIME_TYPE_WAV',         'audio/wav');
    define('FILE_MIME_TYPE_WEBA',        'audio/webm');
    define('FILE_MIME_TYPE_3GP',         'audio/3gpp');
    define('FILE_MIME_TYPE_3G2',         'audio/3gpp2');

    define('FILE_MIME_TYPE_MP4',         'video/mp4');

    define('FILE_MIME_TYPE_VTT',         'text/vtt');

    class FileMimeTypes
    {
        use \Framework\VariableState;
        protected static $values = array(
            FILE_MIME_TYPE_CSS         => array('text' => 'Cascading Style Sheet (CSS)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'css'),
            FILE_MIME_TYPE_DOC         => array('text' => 'MS-Word (doc)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'doc'),
            FILE_MIME_TYPE_DOCX        => array('text' => 'MS-Word (docx)',
                                                'selectable' => true, 
                                                'assignable' => true,
                                                'ext' => 'docx'),
            FILE_MIME_TYPE_DOTX        => array('text' => 'MS-Template (dotx)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'docx'),
            FILE_MIME_TYPE_HTML        => array('text' => 'HyperText Markup Language (HTML)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'html'),
            FILE_MIME_TYPE_JS          => array('text' => 'JavaScript',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'js'),
            FILE_MIME_TYPE_JPEG        => array('text' => 'JPEG Image',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'jpeg'),
            FILE_MIME_TYPE_PDF         => array('text' => 'PDF',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'pdf'),
            FILE_MIME_TYPE_PNG         => array('text' => 'Portable Network Graphics (png)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'png'),
            FILE_MIME_TYPE_TEXT_CSV    => array('text' => 'CSV file',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'csv'),
            FILE_MIME_TYPE_TEXT_PLAIN  => array('text' => 'Text file',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'txt'),
            FILE_MIME_TYPE_TIFF        => array('text' => 'TIFF Image',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'tiff'),
            FILE_MIME_TYPE_XLS         => array('text' => 'Microsoft Excel (xls)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'xls'),
            FILE_MIME_TYPE_XLSX        => array('text' => 'Microsoft Excel (xlsx)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'xlsx'),
            FILE_MIME_TYPE_XLTM        => array('text' => 'Microsoft Excel Template',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'xltm'),
            FILE_MIME_TYPE_ZIP         => array('text' => 'ZIP archive',
                                                'selectable' => true, 
                                                'assignable' => true,
                                                'ext' => 'png'),

            FILE_MIME_TYPE_AAC         => array('text' => 'AAC Audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'aac'),
            FILE_MIME_TYPE_CDA         => array('text' => 'CD Audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'cda'),
            FILE_MIME_TYPE_MID         => array('text' => 'Musical Instrument Digital Interface (MIDI)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'mid'),
            FILE_MIME_TYPE_MIDI        => array('text' => 'Musical Instrument Digital Interface (MIDI)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'midi'),
            FILE_MIME_TYPE_MP3         => array('text' => 'MP3 audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'mp3'),
            FILE_MIME_TYPE_OGA         => array('text' => 'OGG audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'oga'),
            FILE_MIME_TYPE_OPUS        => array('text' => 'Opus audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'opus'),
            FILE_MIME_TYPE_WAV         => array('text' => 'Waveform Audio Format',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'wav'),
            FILE_MIME_TYPE_WEBA        => array('text' => 'WEBM audio',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'weba'),
            FILE_MIME_TYPE_3GP         => array('text' => '3GPP audio container',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => '3gp'),
            FILE_MIME_TYPE_3G2         => array('text' => '3GPP2 audio container',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => '3g2'),
            FILE_MIME_TYPE_MP4         => array('text' => 'MP4 Video',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'mp4'),
            FILE_MIME_TYPE_VTT         => array('text' => 'Web Video Text Tracks Format (WebVTT)',
                                                'selectable' => true,
                                                'assignable' => true,
                                                'ext' => 'vtt'),
        );

        public static function getExtension(string $inFileMimeType)
        {
            if (is_string($inFileMimeType)) {
                if (array_key_exists($inFileMimeType, self::$values)) {
                    return self::$values[$inFileMimeType]['ext'];
                }
            }
            return 'UNKNOWN';
        }

        public static function imageKeyList()
        {
            return array(
                FILE_MIME_TYPE_JPEG,
                FILE_MIME_TYPE_PNG
            );
        }

        public static function docKeyList()
        {
            return array(
                FILE_MIME_TYPE_PDF,
                FILE_MIME_TYPE_DOC,
                FILE_MIME_TYPE_DOCX,
                FILE_MIME_TYPE_XLS,
                FILE_MIME_TYPE_XLSX
            );
        }

        public static function docImageKeyList()
        {
            return array(
                FILE_MIME_TYPE_JPEG,
                FILE_MIME_TYPE_PNG,
                FILE_MIME_TYPE_PDF,
                FILE_MIME_TYPE_DOC,
                FILE_MIME_TYPE_DOCX,
                FILE_MIME_TYPE_XLS,
                FILE_MIME_TYPE_XLSX
            );
        }

        public static function xlsKeyList()
        {
            return array(
                FILE_MIME_TYPE_TEXT_CSV,
                FILE_MIME_TYPE_TEXT_PLAIN,
                FILE_MIME_TYPE_XLS,
                FILE_MIME_TYPE_XLSX
            );
        }

        public static function audioKeyList()
        {
            return array(
                FILE_MIME_TYPE_AAC,
                FILE_MIME_TYPE_CDA,
                FILE_MIME_TYPE_MID,
                FILE_MIME_TYPE_MIDI,
                FILE_MIME_TYPE_MP3,
                FILE_MIME_TYPE_OGA,
                FILE_MIME_TYPE_OPUS,
                FILE_MIME_TYPE_WAV,
                FILE_MIME_TYPE_WEBA,
                FILE_MIME_TYPE_3GP,
                FILE_MIME_TYPE_3G2,
            );
        }

    }



//---------------------------------------------------------------------------------
//-- Defines and Functions for States
//---------------------------------------------------------------------------------

    define('STATE_AL', 'AL');
    define('STATE_AK', 'AK');
    define('STATE_AZ', 'AZ');
    define('STATE_AR', 'AR');
    define('STATE_CA', 'CA');
    define('STATE_CO', 'CO');
    define('STATE_CT', 'CT');
    define('STATE_DE', 'DE');
    define('STATE_DC', 'DC');
    define('STATE_FL', 'FL');
    define('STATE_GA', 'GA');
    define('STATE_GU', 'GU');
    define('STATE_HI', 'HI');
    define('STATE_ID', 'ID');
    define('STATE_IL', 'IL');
    define('STATE_IN', 'IN');
    define('STATE_IA', 'IA');
    define('STATE_KS', 'KS');
    define('STATE_KY', 'KY');
    define('STATE_LA', 'LA');
    define('STATE_ME', 'ME');
    define('STATE_MD', 'MD');
    define('STATE_MA', 'MA');
    define('STATE_MI', 'MI');
    define('STATE_MN', 'MN');
    define('STATE_MS', 'MS');
    define('STATE_MO', 'MO');
    define('STATE_MT', 'MT');
    define('STATE_NE', 'NE');
    define('STATE_NV', 'NV');
    define('STATE_NH', 'NH');
    define('STATE_NJ', 'NJ');
    define('STATE_NM', 'NM');
    define('STATE_NY', 'NY');
    define('STATE_NC', 'NC');
    define('STATE_ND', 'ND');
    define('STATE_OH', 'OH');
    define('STATE_OK', 'OK');
    define('STATE_OR', 'OR');
    define('STATE_PA', 'PA');
    define('STATE_PR', 'PR');
    define('STATE_RI', 'RI');
    define('STATE_SC', 'SC');
    define('STATE_SD', 'SD');
    define('STATE_TN', 'TN');
    define('STATE_TX', 'TX');
    define('STATE_UT', 'UT');
    define('STATE_VT', 'VT');
    define('STATE_VI', 'VI');
    define('STATE_VA', 'VA');
    define('STATE_WA', 'WA');
    define('STATE_WV', 'WV');
    define('STATE_WI', 'WI');
    define('STATE_WY', 'WY');

    class States
    {
        use \Framework\VariableState;

        protected static $values = array(
            STATE_AL => array('text' => 'Alabama',        'selectable' => true, 'assignable' => true),
            STATE_AK => array('text' => 'Alaska',         'selectable' => true, 'assignable' => true),
            STATE_AZ => array('text' => 'Arizona',        'selectable' => true, 'assignable' => true),
            STATE_AR => array('text' => 'Arkansas',       'selectable' => true, 'assignable' => true),
            STATE_CA => array('text' => 'California',     'selectable' => true, 'assignable' => true),
            STATE_CO => array('text' => 'Colorado',       'selectable' => true, 'assignable' => true),
            STATE_CT => array('text' => 'Connecticut',    'selectable' => true, 'assignable' => true),
            STATE_DE => array('text' => 'Delaware',       'selectable' => true, 'assignable' => true),
            STATE_DC => array('text' => 'District',       'selectable' => true, 'assignable' => true),
            STATE_FL => array('text' => 'Florida',        'selectable' => true, 'assignable' => true),
            STATE_GA => array('text' => 'Georgia',        'selectable' => true, 'assignable' => true),
            STATE_GU => array('text' => 'Guam',           'selectable' => true, 'assignable' => true),
            STATE_HI => array('text' => 'Hawaii',         'selectable' => true, 'assignable' => true),
            STATE_ID => array('text' => 'Idaho',          'selectable' => true, 'assignable' => true),
            STATE_IL => array('text' => 'Illinois',       'selectable' => true, 'assignable' => true),
            STATE_IN => array('text' => 'Indiana',        'selectable' => true, 'assignable' => true),
            STATE_IA => array('text' => 'Iowa',           'selectable' => true, 'assignable' => true),
            STATE_KS => array('text' => 'Kansas',         'selectable' => true, 'assignable' => true),
            STATE_KY => array('text' => 'Kentucky',       'selectable' => true, 'assignable' => true),
            STATE_LA => array('text' => 'Louisiana',      'selectable' => true, 'assignable' => true),
            STATE_ME => array('text' => 'Maine',          'selectable' => true, 'assignable' => true),
            STATE_MD => array('text' => 'Maryland',       'selectable' => true, 'assignable' => true),
            STATE_MA => array('text' => 'Massachusetts',  'selectable' => true, 'assignable' => true),
            STATE_MI => array('text' => 'Michigan',       'selectable' => true, 'assignable' => true),
            STATE_MN => array('text' => 'Minnesota',      'selectable' => true, 'assignable' => true),
            STATE_MS => array('text' => 'Mississippi',    'selectable' => true, 'assignable' => true),
            STATE_MO => array('text' => 'Missouri',       'selectable' => true, 'assignable' => true),
            STATE_MT => array('text' => 'Montana',        'selectable' => true, 'assignable' => true),
            STATE_NE => array('text' => 'Nebraska',       'selectable' => true, 'assignable' => true),
            STATE_NV => array('text' => 'Nevada',         'selectable' => true, 'assignable' => true),
            STATE_NH => array('text' => 'New Hampshire',  'selectable' => true, 'assignable' => true),
            STATE_NJ => array('text' => 'New Jersey',     'selectable' => true, 'assignable' => true),
            STATE_NM => array('text' => 'New Mexico',     'selectable' => true, 'assignable' => true),
            STATE_NY => array('text' => 'New York',       'selectable' => true, 'assignable' => true),
            STATE_NC => array('text' => 'North Carolina', 'selectable' => true, 'assignable' => true),
            STATE_ND => array('text' => 'North Dakota',   'selectable' => true, 'assignable' => true),
            STATE_OH => array('text' => 'Ohio',           'selectable' => true, 'assignable' => true),
            STATE_OK => array('text' => 'Oklahoma',       'selectable' => true, 'assignable' => true),
            STATE_OR => array('text' => 'Oregon',         'selectable' => true, 'assignable' => true),
            STATE_PA => array('text' => 'Pennsylvania',   'selectable' => true, 'assignable' => true),
            STATE_PR => array('text' => 'Puerto',         'selectable' => true, 'assignable' => true),
            STATE_RI => array('text' => 'Rhode',          'selectable' => true, 'assignable' => true),
            STATE_SC => array('text' => 'South Carolina', 'selectable' => true, 'assignable' => true),
            STATE_SD => array('text' => 'South Dakota',   'selectable' => true, 'assignable' => true),
            STATE_TN => array('text' => 'Tennessee',      'selectable' => true, 'assignable' => true),
            STATE_TX => array('text' => 'Texas',          'selectable' => true, 'assignable' => true),
            STATE_UT => array('text' => 'Utah',           'selectable' => true, 'assignable' => true),
            STATE_VT => array('text' => 'Vermont',        'selectable' => true, 'assignable' => true),
            STATE_VI => array('text' => 'Virgin',         'selectable' => true, 'assignable' => true),
            STATE_VA => array('text' => 'Virginia',       'selectable' => true, 'assignable' => true),
            STATE_WA => array('text' => 'Washington',     'selectable' => true, 'assignable' => true),
            STATE_WV => array('text' => 'West Virginia',  'selectable' => true, 'assignable' => true),
            STATE_WI => array('text' => 'Wisconsin',      'selectable' => true, 'assignable' => true),
            STATE_WY => array('text' => 'Wyoming',        'selectable' => true, 'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Defines Yes / No values
//---------------------------------------------------------------------------------

    define('VALUE_UNKNOWN', 'U');
    define('VALUE_YES',     'Y');
    define('VALUE_NO',      'N');

    class YesNoState
    {
        use \Framework\VariableState;
        protected static $values = array(
            VALUE_YES => array('text' => 'Yes', 'selectable' => true),
            VALUE_NO  => array('text' => 'No',  'selectable' => true)
        );
    }


