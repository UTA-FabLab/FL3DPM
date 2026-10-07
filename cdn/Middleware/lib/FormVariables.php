<?php
/**
 * Middleware/lib/FormVariables.php
 *
 * Contains the form variable classes specific to applications
 *
 * @package MiddleWare
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare\Form\Variable;


//---------------------------------------------------------------------------------
//-- Email Form Variable -- Defined for future customization
//---------------------------------------------------------------------------------

    class Email extends \Framework\Form\Variable\Email
    {
        public function __construct()
        {
            parent::__construct();
            $this->boldFieldLabel = true;
        }
    }


//---------------------------------------------------------------------------------
//-- Password Form Variable
//---------------------------------------------------------------------------------

    class Password extends \Framework\Form\Variable\Password
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'password';
            $this->valueID = 'password';
            $this->labelText = 'Password';
            $this->boldFieldLabel = true;
            $this->maxLength = 100;
            $this->autoComplete = 'current-password';
        }
    }



//---------------------------------------------------------------------------------
//-- First Name Form Variable
//---------------------------------------------------------------------------------

    class FirstName extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'first_name';
            $this->valueID = 'first_name';
            $this->labelText = 'First Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- Last Name Form Variable
//---------------------------------------------------------------------------------

    class LastName extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'last_name';
            $this->valueID = 'last_name';
            $this->labelText = 'Last Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- Name Form Variable
//---------------------------------------------------------------------------------

    class Name extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'name';
            $this->valueID = 'name';
            $this->labelText = 'Name';
            $this->boldFieldLabel = true;
            $this->maxLength = 50;
            $this->inputSize = 50;
            $this->autoComplete = 'name';
        }
    }



//---------------------------------------------------------------------------------
//-- Enabled Form Variable
//---------------------------------------------------------------------------------

    class Enabled extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'enabled';
            $this->valueID = 'enabled';
            $this->labelText = 'Enabled';
            $this->boldFieldLabel = true;
            $this->setSelectOptions(array('Y' => 'Yes', 'N' => 'No'));
            $this->setInitialOption('-', 'Please select yes or no');
        }
    }



//---------------------------------------------------------------------------------
//-- CSS Style Form Variable
//---------------------------------------------------------------------------------

    class CSSStyle extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'css_style';
            $this->valueID = 'css_style';
            $this->labelText = 'CSS Style';
            $this->boldFieldLabel = true;
        }

        public function loadSelectOptions()
        {
            $this->setSelectOptions(\MiddleWare\CSSStyles::selectList());
            $this->setInitialOption('-', 'Please select a style');
        }

    }



//---------------------------------------------------------------------------------
//-- Record ID Form Variable
//---------------------------------------------------------------------------------

    class RecordID extends \Framework\Form\Variable\Number
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'id';
            $this->valueID = 'id';
            $this->labelText = 'ID';
            $this->hidden = true;
        }
    }



//---------------------------------------------------------------------------------
//-- University ID Form Variable
//---------------------------------------------------------------------------------

    class UniversityID extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'university_id';
            $this->valueID = 'university_id';
            $this->labelText = APP_UNIVERSITY_SHORT_NAME . ' ID';
            $this->boldFieldLabel = true;
            $this->maxLength = 12;
            $this->inputSize = 12;
            $this->errMsg = 'Please provide a valid ' . APP_UNIVERSITY_SHORT_NAME . ' ID';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (! \App\ValidUniversityID($this->value)) {
                $this->errMsg = 'Please enter a valid ' . APP_UNIVERSITY_SHORT_NAME . ' ID';
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
            }

            return $this->valueState;
        }

    }



//---------------------------------------------------------------------------------
//-- University Email Form Variable
//---------------------------------------------------------------------------------

    class UniversityEmail extends \Framework\Form\Variable\Email
    {
        public function __construct()
        {
            parent::__construct();
            $this->boldFieldLabel = true;
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (! \App\ValidUniversityEmail($this->value)) {
                $this->errMsg = 'Must be a valid ' . APP_UNIVERSITY_SHORT_NAME . ' EMail Address';
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
            }

            return $this->valueState;
        }

    }


//---------------------------------------------------------------------------------
//-- Created By Name Form Variable
//---------------------------------------------------------------------------------

    class CreatedBy extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'created_by';
            $this->valueID = 'created_by';
            $this->labelText = 'Creatd By';
            $this->boldFieldLabel = true;
        }
    }



//---------------------------------------------------------------------------------
//-- Created On Name Form Variable
//---------------------------------------------------------------------------------

    class CreatedOn extends \Framework\Form\Variable\Text
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'created_on';
            $this->valueID = 'created_on';
            $this->labelText = 'Created On';
            $this->boldFieldLabel = true;
        }
    }



//---------------------------------------------------------------------------------
//-- Note Form Variable
//---------------------------------------------------------------------------------

    class Note extends \Framework\Form\Variable\TextArea
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'note';
            $this->valueID = 'note';
            $this->labelText = 'Note';
            $this->boldFieldLabel = true;
            $this->maxLength = 9999;
            $this->rows = 10;
            $this->cols = 0;
        }
    }


//---------------------------------------------------------------------------------
//-- Staff Select List Form Variable
//---------------------------------------------------------------------------------

    class StaffMemberSelect extends \Framework\Form\Variable\Select
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'staff_id';
            $this->valueID = 'staff_id';
            $this->labelText = 'Staff Member';
            $this->boldFieldLabel = true;
            $this->inputWidth = 50;
        }

        public function loadSelectOptions(array $inExclude = array(), bool $inIncludeInitial = true)
        {
            if ($inIncludeInitial === true) {
                $this->setInitialOption('-', 'Please select a staff member');
            }

            $this->setSelectOptions(\MiddleWare\getActiveStaff(INDEX_BY_USER_ID,$inExclude));
        }

    }


