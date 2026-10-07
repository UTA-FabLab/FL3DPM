<?php
/**
 * app/lib/FormVariables.php
 *
 * Contains the form variable classes specific to the application
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace App\Form\Variable;


//---------------------------------------------------------------------------------
//-- College Name Form Variable
//---------------------------------------------------------------------------------

    class CollegeName extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'collegeName';
            $this->valueID = 'collegeName';
            $this->labelText = 'Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- Filament Name Form Variable
//---------------------------------------------------------------------------------

    class FilamentName extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'filamentName';
            $this->valueID = 'filamentName';
            $this->labelText = 'Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- Printer Name Form Variable
//---------------------------------------------------------------------------------

    class PrinterName extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'printerName';
            $this->valueID = 'printerName';
            $this->labelText = 'Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- College ID Form Variable
//---------------------------------------------------------------------------------

    class CollegeID extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'college_id';
            $this->valueID = 'college_id';
            $this->labelText = 'College';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a college');
            }

            $collegeList = new \App\Table\CollegesList;
            $collegeList->whereExpressions(array('enabled = :enabled'));

            $params = array(
                'enabled' => 'Y',
            );

            $collegeList->query($params);

            $options = array();
            while ($collegeList->next()) {
                $options[$collegeList->id] = $collegeList->college;
            }

            $this->setSelectOptions($options);
        }

    }



//---------------------------------------------------------------------------------
//-- Printer ID Form Variable
//---------------------------------------------------------------------------------

    class PrinterID extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'printer_id';
            $this->valueID = 'printer_id';
            $this->labelText = 'Printer';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a printer');
            }

            $printerList = new \App\Table\PrintersList;
            $printerList->whereExpressions(array('enabled = :enabled'));

            $params = array(
                'enabled' => 'Y',
            );

            $printerList->query($params);

            $options = array();
            while ($printerList->next()) {
                $options[$printerList->id] = $printerList->name;
            }

            $this->setSelectOptions($options);
        }

    }



//---------------------------------------------------------------------------------
//-- Filament ID Form Variable
//---------------------------------------------------------------------------------

    class FilamentID extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'filament_id';
            $this->valueID = 'filament_id';
            $this->labelText = 'Filament';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true, bool $inIncludeColorSwap = false)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a filament');
            }

            $filamentList = new \App\Table\FilamentsList;
            $filamentList->whereExpressions(array('enabled = :enabled'));

            $params = array(
                'enabled' => 'Y',
            );

            $filamentList->query($params);

            $options = array();

            if ($inIncludeColorSwap === true) {
                $options['c'] = 'Color Swap';
            }

            while ($filamentList->next()) {
                $options[$filamentList->id] = $filamentList->name;
            }

            $this->setSelectOptions($options, false);
        }

    }



//---------------------------------------------------------------------------------
//-- Cancel Date Form Variable
//---------------------------------------------------------------------------------

    class CancelDate extends \Framework\Form\Variable\Date
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'cancel_date';
            $this->valueID = 'cancel_date';
            $this->labelText = 'Cancel Date (optional)';
            $this->boldFieldLabel = true;
        }
    }



//---------------------------------------------------------------------------------
//-- Consult Date Time Form Variable
//---------------------------------------------------------------------------------

    class ConsultDateTime extends \Framework\Form\Variable\DateTimeLocal
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'consult_date_time';
            $this->valueID = 'consult_date_time';
            $this->labelText = 'Consult Date/Time';
            $this->boldFieldLabel = true;
        }
    }



//---------------------------------------------------------------------------------
//-- Contacted Date Form Variable
//---------------------------------------------------------------------------------

    class Contacted extends \Framework\Form\Variable\Date
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'contacted';
            $this->valueID = 'contacted';
            $this->labelText = 'Contacted';
            $this->boldFieldLabel = true;
        }
    }



//---------------------------------------------------------------------------------
//-- Project Category Form Variable
//---------------------------------------------------------------------------------

    class ProjectCategory extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'category';
            $this->valueID = 'category';
            $this->labelText = 'Category';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a category');
            }

            $this->setSelectOptions(\App\ProjectCategories::selectList());
        }

    }


//---------------------------------------------------------------------------------
//-- Is Affilated Form Variable
//---------------------------------------------------------------------------------

    class IsAffiliated extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'is_affiliated';
            $this->valueID = 'is_affiliated';
            $this->labelText = 'Is ' . APP_UNIVERSITY_SHORT_NAME . ' Student / Faculty / Staff?';
            $this->boldFieldLabel = true;
            $this->setSelectOptions(array('Y' => 'Yes', 'N' => 'No'));
            $this->setInitialOption('-', 'Please select yes or no');
        }
    }



//---------------------------------------------------------------------------------
//-- Project Ticket Type Form Variable
//---------------------------------------------------------------------------------

    class TicketType extends \Framework\Form\Variable\Radio
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'ticket_type';
            $this->valueID = 'ticket_type';
            $this->labelText = 'Ticket #';
            $this->boldFieldLabel = true;
            $this->buttons = array(0 => 'Wait List #', 1 => 'Job Ticket #');
        }
    }


//---------------------------------------------------------------------------------
//-- Project Ticket Number Form Variable
//---------------------------------------------------------------------------------

    class TicketNumber extends \Framework\Form\Variable\TextNumber
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'ticket_number';
            $this->valueID = 'ticket_number';
            $this->labelText = 'Ticket #';
            $this->boldFieldLabel = true;
        }
    }


//---------------------------------------------------------------------------------
//-- STL File Form Variable
//---------------------------------------------------------------------------------

    class STLFile extends \Framework\Form\Variable\File
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'stl_file';
            $this->valueID = 'stl_file';
            $this->labelText = 'STL File';
            $this->boldFieldLabel = true;
            $this->allowedMaxSize = FILE_MAX_SIZE_LONGBLOB;
            $this->allowZeroSize = false;
            $this->allowedFileExts = array(PROJECT_FILE_EXT_STL, PROJECT_FILE_EXT_ZIP);
        }
    }


//---------------------------------------------------------------------------------
//-- GCode File Form Variable
//---------------------------------------------------------------------------------

    class GCodeFile extends \Framework\Form\Variable\File
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'gcode_file';
            $this->valueID = 'gcode_file';
            $this->labelText = 'G-code File';
            $this->boldFieldLabel = true;
            $this->allowedMaxSize = FILE_MAX_SIZE_LONGBLOB;
            $this->allowZeroSize = false;
            $this->allowedFileExts = array(PROJECT_FILE_EXT_GCODE, PROJECT_FILE_EXT_ZIP);
        }
    }


//---------------------------------------------------------------------------------
//-- Cancel Reason Form Variable
//---------------------------------------------------------------------------------

    class CancelReason extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'cancel_reason';
            $this->valueID = 'cancel_reason';
            $this->labelText = 'Reason';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a cancel reason');
            }

            $this->setSelectOptions(\App\CancelReasons::selectList());
        }

    }


//---------------------------------------------------------------------------------
//-- Project ID Form Variable
//-- NOTE: this is used to display a normalized version of the project id
//---------------------------------------------------------------------------------

    class ProjectID extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'project_id';
            $this->valueID = 'project_id';
            $this->labelText = 'Project ID';
            $this->readOnly = true;
            $this->boldFieldLabel = true;
        }

        public function setID($inProjectType, $inProjectYear, $inProjectID) {
            $this->value = \App\Normalize::projectID($inProjectType,$inProjectYear,$inProjectID);
        }
    }



//---------------------------------------------------------------------------------
//-- Print Problem Form Variable
//---------------------------------------------------------------------------------

    class PrintProblem extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'print_problem';
            $this->valueID = 'print_problem';
            $this->labelText = 'Problem';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true, bool $inIncludeAll = false)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a problem');
            }

            $this->setSelectOptions(\App\ProjectPrintProblems::selectList($inIncludeAll));
        }

    }



//---------------------------------------------------------------------------------
//-- Print Location Form Variable
//---------------------------------------------------------------------------------

    class PrintLocation extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'print_location';
            $this->valueID = 'print_location';
            $this->labelText = 'Location';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a location');
            }

            $this->setSelectOptions(\App\ProjectLocations::selectList());
        }

    }



//---------------------------------------------------------------------------------
//-- Problem Resolution Form Variable
//---------------------------------------------------------------------------------

    class ProblemResolution extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'resolution';
            $this->valueID = 'resolution';
            $this->labelText = 'Resolution';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions(array $inOptions = array(), bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a resolution');
            }

            if (count($inOptions) == 0) {
                $this->setSelectOptions(\App\ProjectPrintActionTaken::selectList());
            } else {
                $this->setSelectOptions($inOptions);
            }
        }
    }

