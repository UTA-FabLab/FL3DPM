<?php
/**
 * app/lib/Common.php
 *
 * Contains the procedures and classes specific to the application
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


//---------------------------------------------------------------------------------
//-- Define application ACLs
//---------------------------------------------------------------------------------

    define('APP_ACL_MANAGE_ACL',      'fl3dpm-manage-acl');
    define('APP_ACL_MANAGE_SETTINGS', 'fl3dpm-manage-settings');
    define('APP_ACL_ACCESS',          'fl3dpm-access');
    define('APP_ACL_FULL_ACCESS',     'fl3dpm-full-access');
    define('APP_ACL_STUDENT_LEAD',    'fl3dpm-student-lead');
    define('APP_ACL_STUDENT',         'fl3dpm-student');


//---------------------------------------------------------------------------------
//-- Define application ACL label and descriptions
//---------------------------------------------------------------------------------

    define('APP_ACL_LIST', array(

        APP_ACL_MANAGE_ACL => array(
            'label' => 'Manage Access to application',
            'desc'  => 'Manage Access to applciation'
        ),

        APP_ACL_MANAGE_SETTINGS => array(
            'label' => 'Manage application settings',
            'desc'  => 'Manage application settings'
        ),

        APP_ACL_FULL_ACCESS => array(
            'label' => 'Full Access',
            'desc'  => 'User has full access to application'
        ),

        APP_ACL_STUDENT_LEAD => array(
            'label' => 'Lead Student',
            'desc'  => 'User is a Student Lead'
        ),

        APP_ACL_STUDENT => array(
            'label' => 'Student',
            'desc'  => 'User is a Student'
        ),

    ));

    define('APP_ACL_TREE', array(
        APP_ACL_MANAGE_ACL      => null,
        APP_ACL_MANAGE_SETTINGS => null,
        APP_ACL_FULL_ACCESS     => array(
            APP_ACL_STUDENT_LEAD => array(
                APP_ACL_STUDENT => null,
            ),
        ),
    ));


//---------------------------------------------------------------------------------
//-- Defines ChangeLog Table Log Types
//---------------------------------------------------------------------------------

    define('LOG_TABLE_FL3DPM_COLLEGES',               'FL3DPM-COL');
    define('LOG_TABLE_FL3DPM_FILAMENTS',              'FL3DPM-F');
    define('LOG_TABLE_FL3DPM_PRINTERS',               'FL3DPM-PRT');
    define('LOG_TABLE_FL3DPM_PROJECTS',               'FL3DPM-PR');
    define('LOG_TABLE_FL3DPM_PROJECT_FILAMENTS',      'FL3DPM-PS');
    define('LOG_TABLE_FL3DPM_PROJECT_FILES',          'FL3DPM-PF');
    define('LOG_TABLE_FL3DPM_PROJECT_NOTES',          'FL3DPM-PN');
    define('LOG_TABLE_FL3DPM_PROJECT_PRINTS',         'FL3DPM-PP');
    define('LOG_TABLE_FL3DPM_PROJECT_PRINT_PROBLEMS', 'FL3DPM-PPP');


//---------------------------------------------------------------------------------
//-- App ChangeLog class
//---------------------------------------------------------------------------------

    class ChangeLogTypes extends \MiddleWare\ChangeLogTypes
    {
    }

    class ChangeLog extends \MiddleWare\ChangeLog
    {
    }


//---------------------------------------------------------------------------------
//-- Common Normalization Definitions
//---------------------------------------------------------------------------------

    class Normalize extends \MiddleWare\Normalize
    {
        public static function projectID($inType,$inYear,$inID) {
            $year = intval($inYear) - 2000;
            return $inType . '-' . $year . '-' . $inID;
        }
    }


//---------------------------------------------------------------------------------
//-- Define application pages
//---------------------------------------------------------------------------------

    class Page extends \MiddleWare\Page
    {
    }

    class Form extends \MiddleWare\Form
    {
    }

    class LoginPage extends \MiddleWare\LoginPage
    {
    }

    class LoginErrorPage extends \MiddleWare\LoginErrorPage
    {
    }

    class LoginForm extends \MiddleWare\LoginForm
    {
    }


//---------------------------------------------------------------------------------
//-- Define Application Render class
//---------------------------------------------------------------------------------

    class Render extends \MiddleWare\Render
    {
        public static function stickyButton(string $inID, string $inHREF, string $inText, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: $inID,
                inHREF: $inHREF,
                inButtonClass: 'btn btn-primary',
                inIconClass: 'bi bi-sticky',
                inIconExtra: array('style="font-size: 1.25rem;"'),
                inText: $inText,
                inPrint: false);

            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }

            return $output;
        }
    }



//---------------------------------------------------------------------------------
//-- Define application request class
//---------------------------------------------------------------------------------

    class Request extends \MiddleWare\Request
    {
    }



//---------------------------------------------------------------------------------
//-- Define user application class
//---------------------------------------------------------------------------------

    class User extends \MiddleWare\User
    {
        public function __get(string $inName)
        {
            $outValue = parent::__get($inName);

            if ($outValue !== NULL) {
                return $outValue;
            }

            if (! is_string($inName)) {
                return NULL;
            }

            switch($inName) {

                case '':
                    return NULL;

                case 'isEmployed':

                    if (! array_key_exists(APP_MYSQL_IDENTITY_ENABLED, $this->userDetails)) {
                        return false;
                    }

                    if ($this->userDetails[APP_MYSQL_IDENTITY_ENABLED] === VALUE_YES) {
                        return true;
                    }

                    return false;
            }

            return NULL;
        }
    }



//---------------------------------------------------------------------------------
//-- Function used to send email
//---------------------------------------------------------------------------------
   
    define('MAIL_TYPE_MANUAL',          'M');
    define('MAIL_TYPE_PRINT_PICKEDUP',  'PP');
    define('MAIL_TYPE_PRINT_STORED',    'PS');
    define('MAIL_TYPE_PROBLEM_COLOR',   'PC');
    define('MAIL_TYPE_PROBLEM_RESLICE', 'PR');
    define('MAIL_TYPE_PROBLEM_UNPAID',  'PU');

    define('MAIL_SCRIPTS', array(
        MAIL_TYPE_PRINT_PICKEDUP  => 'PrintPickedUp.php',
        MAIL_TYPE_PRINT_STORED    => 'PrintStored.php',
        MAIL_TYPE_PROBLEM_COLOR   => 'ProblemColor.php',
        MAIL_TYPE_PROBLEM_RESLICE => 'ProblemReslice.php',
        MAIL_TYPE_PROBLEM_UNPAID  => 'ProblemUnpaid.php',
    ));
 
    function mail(string $inMailType, string $inEmailTo, string $inSubject = '', string $inMessage = '', array $inBCC = array())
    {
        //-- Find the address to send email as

            $applicationEmailFrom = \Middleware\getConfigValue('APPLICATION_EMAIL_FROM');
    
            if ($applicationEmailFrom === false) {
                return false;
            }
   
        //-- Build the beginning header array
 
            $headers = array();
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type:text/html;charset=UTF-8;';
            $headers[] = 'From: ' . $applicationEmailFrom;


        //-- If not a manual email, then pull in the script

            $emailSubject = $inSubject;
            $emailMessage = $inMessage;
            $emailBCC     = $inBCC;

            if ($inMailType != MAIL_TYPE_MANUAL) {

                if (! array_key_exists($inMailType, MAIL_SCRIPTS)) {
                    return false;
                }

                $sfn = APP_ROOT . 'app/instance/Mail/' . MAIL_SCRIPTS[$inMailType];

                if (! file_exists($sfn)) {
                    $sfn = APP_ROOT . 'app/lib/Mail/' . MAIL_SCRIPTS[$inMailType];
                    if (! file_exists($sfn)) {
                        return false;
                    }
                }

                if (! @include $sfn) {
                    return false;
                }

            }


        //-- Handle redirects, prod/dev, etc customization
   
            return \Middleware\mail($inEmailTo, $emailSubject, $emailMessage);
    }



//---------------------------------------------------------------------------------
//-- Project Types
//---------------------------------------------------------------------------------

    define('PROJECT_TYPE_3D_PRINT', 'P');

    class ProjectTypes
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_TYPE_3D_PRINT => array(
                'text' => '3D Print',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project Statuses
//---------------------------------------------------------------------------------

    define('PROJECT_STATUS_UNDEFINED', 'U');
    define('PROJECT_STATUS_ACTIVE',    'A');
    define('PROJECT_STATUS_CLOSED',    'C');

    class ProjectStatuses
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_STATUS_UNDEFINED => array(
                'text' => 'Undefined',
                'selectable' => false,
                'assignable' => false),

            PROJECT_STATUS_ACTIVE => array(
                'text' => 'Active',
                'selectable' => true,
                'assignable' => true),

            PROJECT_STATUS_CLOSED => array(
                'text' => 'Closed',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Cancel Reasons Statuses
//---------------------------------------------------------------------------------

    define('CANCEL_REASONS_UNDEFINED',           'U');
    define('CANCEL_REASONS_ACCIDENTAL_CREATION', 'AC');
    define('CANCEL_REASONS_CANCEL_DATE',         'CD');
    define('CANCEL_REASONS_LEARNER_NO_RESPONSE', 'LNR');
    define('CANCEL_REASONS_LEARNER_CANCEL',      'LC');

    class CancelReasons
    {
        use \Framework\VariableState;

        protected static $values = array(
            CANCEL_REASONS_UNDEFINED => array(
                'text' => 'Undefined',
                'selectable' => false,
                'assignable' => false),

            CANCEL_REASONS_ACCIDENTAL_CREATION => array(
                'text' => 'Accidental Project Creation',
                'selectable' => true,
                'assignable' => true),

            CANCEL_REASONS_CANCEL_DATE => array(
                'text' => 'Cancel Date Reached',
                'selectable' => true,
                'assignable' => true),

            CANCEL_REASONS_LEARNER_NO_RESPONSE => array(
                'text' => 'No Response From Learner',
                'selectable' => true,
                'assignable' => true),

            CANCEL_REASONS_LEARNER_CANCEL => array(
                'text' => 'Cancelled By Learner',
                'selectable' => true,
                'assignable' => true),

        );
    }



//---------------------------------------------------------------------------------
//-- Project Categories
//---------------------------------------------------------------------------------

    define('PROJECT_CATEGORY_CURRICULAR',       'C');
    define('PROJECT_CATEGORY_EXTRA_CURRICULAR', 'EC');
    define('PROJECT_CATEGORY_NON_ACADEMIC',     'NA');
    define('PROJECT_CATEGORY_ENTREPRENURIAL',   'E');


    class ProjectCategories
    {
        use \Framework\VariableState;

        protected static $values = array(
            PROJECT_CATEGORY_CURRICULAR => array(
                'text' => 'Curricular',
                'selectable' => true,
                'assignable' => true),

            PROJECT_CATEGORY_EXTRA_CURRICULAR => array(
                'text' => 'Extra-Curricular',
                'selectable' => true,
                'assignable' => true),

            PROJECT_CATEGORY_NON_ACADEMIC => array(
                'text' => 'Non-Academic',
                'selectable' => true,
                'assignable' => true),

            PROJECT_CATEGORY_ENTREPRENURIAL => array(
                'text' => 'Entreprenurial',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project Print Statuses
//---------------------------------------------------------------------------------


    define('PROJECT_PRINT_STATUS_UNDEFINED', 'U');
    define('PROJECT_PRINT_STATUS_WAITING',   'W');
    define('PROJECT_PRINT_STATUS_PRINTING',  'P');
    define('PROJECT_PRINT_STATUS_PROBLEM',   'B');
    define('PROJECT_PRINT_STATUS_RESOLVED',  'R');
    define('PROJECT_PRINT_STATUS_CANCELLED', 'X');
    define('PROJECT_PRINT_STATUS_COMPLETE',  'C');

    class ProjectPrintStatuses
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_PRINT_STATUS_UNDEFINED => array(
                'text' => 'Unknown',
                'selectable' => false,
                'assignable' => false),

            PROJECT_PRINT_STATUS_WAITING   => array(
                'text' => 'Waiting',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_STATUS_PRINTING  => array(
                'text' => 'Printing',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_STATUS_PROBLEM   => array(
                'text' => 'Problem',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_STATUS_RESOLVED  => array(
                'text' => 'Problem Resolved',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_STATUS_CANCELLED => array(
                'text' => 'Cancelled',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_STATUS_COMPLETE  => array(
                'text' => 'Complete',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project File Types
//---------------------------------------------------------------------------------

    define('PROJECT_FILE_TYPE_GCODE', 'G');
    define('PROJECT_FILE_TYPE_STL',   'S');
    define('PROJECT_FILE_TYPE_ZIP',   'Z');

    define('PROJECT_FILE_EXT_GCODE', 'gcode');
    define('PROJECT_FILE_EXT_STL',   'stl');
    define('PROJECT_FILE_EXT_ZIP',   'zip');

    class ProjectFileTypes
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_FILE_TYPE_GCODE => array(
                'text' => 'G-code',
                'selectable' => true,
                'assignable' => true),

            PROJECT_FILE_TYPE_STL   => array(
                'text' => 'STL',
                'selectable' => true,
                'assignable' => true),

            PROJECT_FILE_TYPE_ZIP   => array(
                'text' => 'ZIP',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project File Statuses
//---------------------------------------------------------------------------------

    define('PROJECT_FILE_STATUS_ENABLED',  'E');
    define('PROJECT_FILE_STATUS_DISABLED', 'X');

    class ProjectFileStatuses
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_FILE_STATUS_ENABLED  => array(
                'text' => 'Enabled',
                'selectable' => true,
                'assignable' => true),

            PROJECT_FILE_STATUS_DISABLED => array(
                'text' => 'Disabled',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project Print Actions
//---------------------------------------------------------------------------------

    define('PROJECT_PRINT_PROBLEM_NONE',               'N');

    define('PROJECT_PRINT_PROBLEM_ADD_SUPPORT',        'AS');
    define('PROJECT_PRINT_PROBLEM_COLOR_SWAP_FAIL',    'CWF');
    define('PROJECT_PRINT_PROBLEM_CORRUPTED_FILE',     'CF');
    define('PROJECT_PRINT_PROBLEM_INCORRECT_FILE',     'IF');
    define('PROJECT_PRINT_PROBLEM_LOOSE_ON_BED',       'LOB');
    define('PROJECT_PRINT_PROBLEM_LOST_FILE',          'LF');
    define('PROJECT_PRINT_PROBLEM_LOST_POWER',         'LP');
    define('PROJECT_PRINT_PROBLEM_NOZZLE_CLOGGED',     'NC');
    define('PROJECT_PRINT_PROBLEM_OBJECT_FELL',        'OF');
    define('PROJECT_PRINT_PROBLEM_OUT_OF_COLOR',       'OOC');
    define('PROJECT_PRINT_PROBLEM_OUT_OF_FILAMENT',    'OOF');
    define('PROJECT_PRINT_PROBLEM_PRINTER_BROKEN',     'PB');
    define('PROJECT_PRINT_PROBLEM_PRINTER_CONNECTION', 'PC');
    define('PROJECT_PRINT_PROBLEM_OTHER',              'O');
    define('PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT',     'PP');

    define('PROJECT_PRINT_ACTION_NEEDED_NONE',                  'N');
    define('PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE', 'ELR');
    define('PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR',   'ELC');
    define('PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_PICKUP',  'ELP');
    define('PROJECT_PRINT_ACTION_NEEDED_CHECK_NOZZLE',          'CN');
    define('PROJECT_PRINT_ACTION_NEEDED_OPEN_SUPPORT_TICKET',   'OST');
    define('PROJECT_PRINT_ACTION_NEEDED_REPRINT',               'RP');
    define('PROJECT_PRINT_ACTION_NEEDED_UPLOAD_RESLICE',        'UR');
    define('PROJECT_PRINT_ACTION_NEEDED_LEARNER_PICKUP',        'LPU');

    define('PROJECT_PRINT_ACTION_TAKEN_NONE',                    'N');
    define('PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE', 'MLR');
    define('PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_COLOR',   'MLC');
    define('PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_PICKUP',  'MLP');
    define('PROJECT_PRINT_ACTION_TAKEN_REPRINT',                 'RP');
    define('PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE',          'UR');
    define('PROJECT_PRINT_ACTION_TAKEN_CANCEL_PROJECT',          'XP');
    define('PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLEANED',          'NC');
    define('PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLEANED_REPRINT',  'NCR');
    define('PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLOGGED_SUPPORT',  'NXS');
    define('PROJECT_PRINT_ACTION_TAKEN_LEARNER_PICKUP',          'NPU');

    class ProjectPrintProblems
    {
        use \Framework\VariableState;
        protected static $values = array(

            PROJECT_PRINT_PROBLEM_NONE => array(
                'text'           => 'None',
                'selectable'     => false,
                'assignable'     => false,
                'actionNeeded'   => PROJECT_PRINT_ACTION_NEEDED_NONE,
                'actionTaken'    => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => false),

            //-- Problems that need to email learner

            PROJECT_PRINT_PROBLEM_ADD_SUPPORT => array(
                'text' => 'Need to add support / brim / raft (email)',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_CORRUPTED_FILE => array(
                'text' => 'Corrupted/Unreadable File (email)',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_INCORRECT_FILE => array(
                'text' => 'Incorrect File (email)',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_PREVIOUS_PRINT => array(
                'text' => 'Learner Needs to Pickup Previous Print (email)',
                'selectable' => false,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_PICKUP,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_PICKUP,
                'lockCountable'  => false),

            //-- no longer used
            PROJECT_PRINT_PROBLEM_LOST_FILE => array(
                'text' => 'Lost File (email)',
                'selectable' => false,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_OUT_OF_COLOR => array(
                'text' => 'Out of Requested Color (email)',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_COLOR,
                'lockCountable'  => false),


            //-- that do not require emailing learner

            PROJECT_PRINT_PROBLEM_COLOR_SWAP_FAIL => array(
                'text' => 'Color Swap Failed',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => true),

            //-- no longer used
            PROJECT_PRINT_PROBLEM_LOOSE_ON_BED => array(
                'text' => 'Did Not Stick to Bed',
                'selectable' => false,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_LOST_POWER => array(
                'text' => 'Lost Power',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => false),

            PROJECT_PRINT_PROBLEM_NOZZLE_CLOGGED => array(
                'text' => 'Nozzle Clogged',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_OPEN_SUPPORT_TICKET,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_OBJECT_FELL => array(
                'text' => 'Print Fell Down / Fell Off Bed',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => true),

            PROJECT_PRINT_PROBLEM_OUT_OF_FILAMENT => array(
                'text' => 'Print Ran Out Of Filament',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => false),

            PROJECT_PRINT_PROBLEM_PRINTER_BROKEN => array(
                'text' => 'Printer Mechanical Issues',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_OPEN_SUPPORT_TICKET,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => false),

            PROJECT_PRINT_PROBLEM_PRINTER_CONNECTION => array(
                'text' => 'Printer Connection Issue',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => false),

            PROJECT_PRINT_PROBLEM_OTHER => array(
                'text' => 'Other',
                'selectable' => true,
                'assignable' => false,
                'actionNeeded' => PROJECT_PRINT_ACTION_NEEDED_REPRINT,
                'actionTaken'  => PROJECT_PRINT_ACTION_TAKEN_NONE,
                'lockCountable'  => true),
        );

        public static function getActionNeeded($inProblem)
        {
            if (! self::$constructed) {
                static::construct();
            }
            if (is_string($inProblem)) {
                if (array_key_exists($inProblem, self::$values)) {
                    return self::$values[$inProblem]['actionNeeded'];
                }
            }
            return false;
        }

        public static function getActionTaken($inProblem)
        {
            if (! self::$constructed) {
                static::construct();
            }
            if (is_string($inProblem)) {
                if (array_key_exists($inProblem, self::$values)) {
                    return self::$values[$inProblem]['actionTaken'];
                }
            }
            return false;
        }

        public static function lockCountable($inProblem, $inActionNeeded)
        {
            if (! self::$constructed) {
                static::construct();
            }

            // do we ignore problem?
            switch($inActionNeeded) {
                case PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE:
                case PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR:
                    return false;
            }

            if (is_string($inProblem)) {
                if (array_key_exists($inProblem, self::$values)) {
                    return self::$values[$inProblem]['lockCountable'];
                }
            }
            return true;
        }
    }


    class ProjectPrintActionNeeded
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_PRINT_ACTION_NEEDED_NONE => array(
                'text' => 'None',
                'selectable' => false,
                'assignable' => false),

            PROJECT_PRINT_ACTION_NEEDED_CHECK_NOZZLE => array(
                'text' => 'Check Nozzle',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_RESLICE => array(
                'text' => 'Email Learner - Reslice',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_PICKUP => array(
                'text' => 'Email Learner - Pickup Previous Print',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_EMAIL_LEARNER_COLOR => array(
                'text' => 'Email Learner - Request Color',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_OPEN_SUPPORT_TICKET => array(
                'text' => 'Open Support Ticket',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_REPRINT => array(
                'text' => 'Reprint',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_UPLOAD_RESLICE => array(
                'text' => 'Upload Learner Reslice',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_NEEDED_LEARNER_PICKUP => array(
                'text' => 'Learner Pickup Print',
                'selectable' => true,
                'assignable' => true),
        );

    }


    class ProjectPrintActionTaken
    {
        use \Framework\VariableState;
        protected static $values = array(
            PROJECT_PRINT_ACTION_TAKEN_NONE => array(
                'text' => 'None',
                'selectable' => false,
                'assignable' => false),

            PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_RESLICE => array(
                'text' => 'Emailed Learner - Reslice',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_COLOR => array(
                'text' => 'Emailed Learner - Request Color',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_EMAILED_LEARNER_PICKUP => array(
                'text' => 'Emailed Learner - Pickup Previous Print',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_REPRINT => array(
                'text' => 'Reprint',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE => array(
                'text' => 'Uploaded Learner Reslice',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_CANCEL_PROJECT => array(
                'text' => 'Project Cancelled',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLEANED => array(
                'text' => 'Nozzle Cleaned',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLEANED_REPRINT => array(
                'text' => 'Nozzle Cleaned - Reprint',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_NOZZLE_CLOGGED_SUPPORT => array(
                'text' => 'Nozzle Clogged - Open Support Ticket',
                'selectable' => true,
                'assignable' => true),

            PROJECT_PRINT_ACTION_TAKEN_LEARNER_PICKUP => array(
                'text' => 'Learner Picked Up Print',
                'selectable' => true,
                'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Project Locations
//---------------------------------------------------------------------------------

    define('LOCATION_PICKUP', 'C');
    define('LOCATION_STORED', 'S');

    class ProjectLocations
    {
        use \Framework\VariableState;
        protected static $values = array(
           LOCATION_PICKUP => array(
                'text' => 'Customer Picked Up Print Job',
                'selectable' => true,
                'assignable' => true),

           LOCATION_STORED => array(
                'text' => 'Print Job Stored',
                'selectable' => true,
                'assignable' => true),
        );
    }

