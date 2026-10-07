<?php
/**
 * Framework/lib/FormVariables.php
 *
 * Contains the form variable classes
 *
 * @package Framework
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace Framework\Form\Variable;


//---------------------------------------------------------------------------------
//-- Define the class to handle form variables
//---------------------------------------------------------------------------------

    define('FORM_VARIABLE_STATE_INIT',    0);
    define('FORM_VARIABLE_STATE_VALID',   1);
    define('FORM_VARIABLE_STATE_INVALID', 2);

    define('FILE_UPLOAD_ERR_OK',         0);
    define('FILE_UPLOAD_ERR_INI_SIZE',   1);
    define('FILE_UPLOAD_ERR_FORM_SIZE',  2);
    define('FILE_UPLOAD_ERR_PARTIAL',    3);
    define('FILE_UPLOAD_ERR_NO_FILE',    4);
    define('FILE_UPLOAD_ERR_NO_TMP_DIR', 6);
    define('FILE_UPLOAD_ERR_CANT_WRITE', 7);
    define('FILE_UPLOAD_ERR_EXTENSION',  8);

    define('FILE_UPLOAD_MAX',          20000000);
    define('FILE_MAX_SIZE_MEDIUMBLOB', 16777000);
    define('FILE_MAX_SIZE_LONGBLOB',   4294967000);


//----------------------------------------------------------------------
//-- Base Form Variable class

    class Base
    {
        protected $valueID;
        protected $valueName;
        protected $labelText;
        protected $valueType;

        protected $value;
        protected $valueState;
        protected $autoFocus;
        protected $errMsg;
        protected $collected;

        protected $collect;
        protected $disabled;
        protected $readOnly;
        protected $required;
        protected $optionalCallValidate;

        protected $autoComplete;
        protected $autoTrim;
        protected $boldFieldLabel;
        protected $display;
        protected $hidden;
        protected $inputClasses;
        protected $inputSize;
        protected $inputWidth;
        protected $labelClasses;
        protected $maxLength;
        protected $minLength;
        protected $normalizeSpaces;
        protected $options;
        protected $runHtmlSpecialChars;


        public function __construct()
        {
            $this->valueID = false;
            $this->valueName = 'unknown';
            $this->labelText = 'unknown';
            $this->valueType = 'text';

            $this->reset();

            // by default fields are configured as optional
            $this->collect  = true;
            $this->disabled = false;
            $this->readOnly = false;
            $this->required = false;
            $this->optionalCallValidate = false;

            $this->autoComplete = false;
            $this->autoTrim = true;
            $this->boldFieldLabel= false;
            $this->display = true;
            $this->hidden = false;
            $this->inputClasses = array();
            $this->inputSize = false;
            $this->inputWidth = false;
            $this->labelClasses = array();
            $this->maxLength = false;
            $this->minLength = false;
            $this->normalizeSpaces = false;
            $this->options = array();
            $this->runHtmlSpecialChars = true;
        }

        public function reset()
        {
            $this->value      = '';
            $this->valueState = FORM_VARIABLE_STATE_INIT;
            $this->autoFocus  = false;
            $this->errMsg     = '';
            $this->collected  = false;
        }

        public function resetState()
        {
            $this->valueState = FORM_VARIABLE_STATE_INIT;
        }

        public function __get(string $inName)
        {
            switch($inName) {
                case 'autoFocus':
                    return $this->autoFocus;

                case 'collect':
                    return $this->collect;

                case 'collected':
                    return $this->collected;

                case 'errMsg':
                    return $this->errMsg;

                case 'hidden':
                    return $this->hidden;

                case 'required':
                    return $this->required;

                case 'type':
                    return $this->valueType;

                case 'valid':
                    if ($this->valueState === FORM_VARIABLE_STATE_VALID) {
                        return true;
                    }
                    return false;

                case 'value':
                    return $this->value;

                case 'valueInt':
                    return intval($this->value);
            }

            return null;
        }

        public function __set(string $inName, $inValue)
        {
            switch($inName) {

                case 'autoFocus':
                    if ($inValue === true) {
                        $this->autoFocus = true;
                    } else {
                        $this->autoFocus = false;
                    }
                    break;

                case 'boldLabel':
                    if ($inValue === true) {
                        $this->boldFieldLabel = true;
                    } else {
                        $this->boldFieldLabel = false;
                    }
                    break;

                case 'collect':
                    if ($inValue === true) {
                        $this->collect = true;
                    } else {
                        $this->collect = false;
                    }
                    break;

                case 'disabled':
                    if ($inValue === true) {
                        $this->disabled = true;
                    } else {
                        $this->disabled = false;
                    }
                    break;

                case 'errMsg':
                    $this->errMsg = $inValue;
                    break;

                case 'hidden':
                    if ($inValue === true) {
                        $this->hidden = true;
                    } else {
                        $this->hidden = false;
                    }
                    break;

                case 'id':
                    $this->valueID = $inValue;
                    break;

                case 'label':
                    $this->labelText = $inValue;
                    break;

                case 'name':
                    $this->valueName = $inValue;
                    break;

                case 'optional':
                    $this->readOnly = false;
                    $this->disabled = false;
                    $this->collect  = true;
                    $this->required = false;
                    break;

                case 'optionalCallValidate':
                    $this->optionalCallValidate = true;
                    break;

                case 'readonly':
                case 'readOnly':
                    $this->required  = false;
                    $this->collect   = false;
                    $this->readOnly  = true;
                    $this->disabled  = true;
                    break;

                case 'required':
                    if ($inValue === true) {
                        $this->required  = true;
                        $this->collect   = true;
                        $this->readOnly  = false;
                        $this->disabled  = false;
                    } else {
                        $this->required  = false;
                        $this->collect   = true;
                        $this->readOnly  = false;
                        $this->disabled  = false;
                    }
                    break;

                case 'reqReadOnly':
                        $this->required  = true;
                        $this->collect   = true;
                        $this->readOnly  = true;
                        $this->disabled  = false;
                    break;

                case 'runHtmlSpecialChars':
                    if ($inValue === true) {
                        $this->runHtmlSpecialChars = true;
                    } else {
                        $this->runHtmlSpecialChars = false;
                    }
                    break;

                case 'selectMultiple':
                    if ($inValue === true) {
                        $this->selectMultiple = true;
                    } else {
                        $this->selectMultiple = false;
                    }
                    break;

                case 'size':
                    $this->inputSize = $inValue;
                    break;

                case 'width':
                    $this->inputWidth = $inValue;
                    break;

                case 'value':
                    $this->setValue($inValue);
                    break;

            }
        }

        public function setOption(string $inOption, $inValue, bool $inOverride=true)
        {
            if ($inOverride === false) {
                if (array_key_exists($inOption, $this->options)) {
                    return;
                }
            }

            $this->options[$inOption] = $inValue;
        }

        public function setValue($inValue, bool $inSetState=true)
        {
            if ($inSetState === true) {
                $this->valueState = FORM_VARIABLE_STATE_INIT;
            }

            if ($this->runHtmlSpecialChars) {
                if (is_string($inValue)) {
                    $inValue = htmlspecialchars($inValue);
                }
            }

            if ($this->autoTrim) {
                $inValue = trim(strval($inValue));
            } 

            if ($this->normalizeSpaces) {
                $pieces = explode(' ', $inValue);

                foreach($pieces as $ndx => $pieceValue) {
                    $pieceValue = trim($pieceValue);
                    if ($pieceValue == '') {
                        unset($pieces[$ndx]);
                    }
                }

                $inValue = implode(' ', $pieces);
            }

            $this->value = $inValue;
        }

        public function setInvalid(string $inErrMsg='')
        {
            if ($inErrMsg != '') {
                $this->errMsg = trim($inErrMsg);
            }
            $this->valueState = FORM_VARIABLE_STATE_INVALID;
        }

        public function validate()
        {
            $this->valueState = FORM_VARIABLE_STATE_VALID;

            //-- we are trim'ing again because if autoTrimmed is set to false
            //-- we assume that even a non-autoTrim'ed value
            //-- must contain something other than just spaces
            if (trim($this->value) === '') {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
            }

            return $this->valueState;
        }

        public function collect()
        {
            //-- initialize value / state

                $this->valueState = FORM_VARIABLE_STATE_INIT;
                $this->collected  = false;
                $this->value = '';

                if ($this->collect == false) {
                    return;
                }

            //-- no variables in POST

                if (empty($_POST)) {
                    if ($this->required) {
                        $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    }
                    return;
                }

            //-- target variable not in POST

                if (! array_key_exists($this->valueName, $_POST)) {
                    if ($this->required) {
                        $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    }
                    return;
                }

            //-- store value

                $this->setValue($_POST[$this->valueName]);
                $this->collected = true;

                if ($this->required || $this->optionalCallValidate)  {
                    $this->validate();
                }
        }

        protected function renderValue()
        {
            if ($this->value != '') {
                print 'value="' . $this->value . '" ';
            }
        }

        public function renderLabel(array $inClasses=array(), bool $inLabelText=false, bool $inForLabel = false)
        {
            if ($inForLabel === false) {
                print '<label for="' . $this->valueName . '"';
            } else {
                print '<label for="b-' . $inForLabel . '"';
            }

            if (count($inClasses) > 0) {
                $classes = array_merge($this->labelClasses, $inClasses);
            } else {
                $classes = $this->labelClasses;
            }

            if (count($classes) > 0) {
                print ' class="' . implode(' ', $classes) . '"';
            }

            print '>';

            if ($inLabelText === false) {
                $inLabelText = $this->labelText;
            } 

            if ($this->boldFieldLabel) {
                print '<b>' . $inLabelText . '</b>';;
            } else {
                print $inLabelText;
            }

            print '</label>';
        }

        protected function renderName()
        {
            print 'name="' . $this->valueName . '" ';
        }

        protected function renderAttributes(array $inClasses = array(), array $inStyles = array())
        {
            if ($this->valueID === false) {
                print 'id="' . $this->valueName . '" ';
            } else {
                print 'id="' . $this->valueID . '" ';
            }

            $this->renderName();

            if (! $this->readOnly) {
                switch($this->valueState) {
                    case FORM_VARIABLE_STATE_INVALID:
                        $inClasses[] = 'is-invalid';
                        break;

                    case FORM_VARIABLE_STATE_VALID:
                        $inClasses[] = 'is-valid';
                        break;
                }
            }

            if ($this->autoFocus) {
                print 'autofocus ';
            }

            if ($this->required) {
                print 'required ';
            }

            if ($this->minLength !== false) {
                print 'minlength="' . $this->minLength . '" ';
            }

            if ($this->maxLength !== false) {
                print 'maxlength="' . $this->maxLength . '" ';
            }

            if ($this->inputSize !== false) {
                print 'size="' . $this->inputSize . '" ';
            }

            if ($this->inputWidth !== false) {
                print 'width="' . $this->inputWidth . '" ';
            }

            if ($this->readOnly) {
                print 'readonly ';
            }

            if ($this->disabled) {
                print 'disabled ';
            }

            if ($this->autoComplete !== false) {
                print 'autocomplete="' . $this->autoComplete . '" ';
            }

            //-- generate the class attribute

                if (array_key_exists('class', $this->options)) {
                    $inClasses[] = trim($this->options['class']);
                    unset($this->options['class']);
                }

                if (count($inClasses) > 0) {
                    print 'class="' . implode(' ', $inClasses) . '" ';
                }

            //-- generate the style attribute

                if ($this->display === false) {
                    $inStyles[] = 'display: none;';
                }

                if (array_key_exists('style', $this->options)) {
                    $inStyles[] = trim($this->options['style']) . ';';
                    unset($this->options['style']);
                }

                if (count($inStyles) > 0) {
                    print 'style="' . implode(' ', $inStyles) . '" ';
                }

            //-- render remaining attributes

                foreach($this->options as $attrName => $attrValue) {
                    print $attrName;
                    if ($attrValue !== NULL) {
                        print '="' . $attrValue . '"';
                    }
                    print ' ';
                }
        }

        public function renderElement(array $inClasses=array(), array $inStyles=array())
        {
            print '<input ';

            if ($this->hidden) {
                print 'type="hidden" ';
            } else {
                print 'type="' . $this->valueType . '" ';
            }

            $this->renderAttributes($inClasses, $inStyles);

            $this->renderValue();

            print '>';
        }

        public function renderErr(string $inClass='')
        {
            if ($this->valueState === FORM_VARIABLE_STATE_INVALID) {
                print '<div';

                if ($inClass != '') {
                    print ' class="' . $inClass . '"';
                }

                print '>' . $this->errMsg . '</div>';
            }
        }

        public function render(array $inClasses=array())
        {
            $this->renderElement($inClasses);
        }

    }



//----------------------------------------------------------------------
//-- Base class with Render function
//-- This is provided so that custom classes don't have to include
//-- render function which calls Custom Render 

    class BaseRender extends \Framework\Form\Variable\Base
    {
        public function render(array $inClasses=array(), bool $inInsideForm=true)
        {
            \App\Render::formField($this, inInsideForm: $inInsideForm);
        }
    }



//----------------------------------------------------------------------
//-- Class to handle form variables with checkbox input type
//-- NOTE: uses 'checked' as the value for indicating that the checkbox
//-- has been selected

    class Checkbox extends \Framework\Form\Variable\BaseRender
    {
        protected $isSwitch; // this value is used to indicate "switch" for renderer

        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'checkbox';
            $this->value = 0;
            $this->isSwitch = false;
        }

        public function __set($inName, $inValue)
        {
            switch($inName) {
                case 'checked':
                    if ($inValue === true) {
                        $this->value = 1;
                    } else {
                        $this->value = 0;
                    }
                    break;
                case 'switch':
                    if ($inValue === true) {
                        $this->isSwitch = true;
                    } else {
                        $this->isSwitch = false;
                    }
                    break;
                default:
                    parent::__set($inName, $inValue);
            }
        }

        public function __get($inName)
        {
            switch($inName) {
                case 'checked':
                    return ($this->value == 1 ? true : false);
                case 'switch':
                    return ($this->isSwitch ? true : false);
                default:
                return parent::__get($inName);
            }
        }

        public function setValue($inValue, $inSetState=true)
        {
            if ($inSetState === true) {
                $this->valueState = FORM_VARIABLE_STATE_VALID;
            }
            $this->value = 0;
            if ($inValue == 'on') {
                $this->value = 1;
            }
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            if ($this->value == 1) {
                $this->options['checked'] = NULL;
            } else {
                unset($this->options['checked']);
            }

            parent::renderElement($inClasses, $inStyles);
        }

        public function renderValue()
        {
            // don't render value
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with color input type

    class Color extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'color';
        }

        public function validate()
        {
            parent::validate();

            if ($this->valueState != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^#[0-9a-f]{6}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function setValue($inValue, $inSetState=true)
        {
            parent::setValue($inValue, $inSetState);
            $this->value = strtolower($this->value);
        }

    }



//----------------------------------------------------------------------
//-- CSRF Class

    class CSRF extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'csrf';
            $this->valueID = 'csrf';
            $this->labelText = 'CSRF';
            $this->hidden = true;
        }
    }



//----------------------------------------------------------------------
//-- Class to handle form variables with date input type

    class Date extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'date';
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'ts':
                    parent::setValue(date('Y-m-d', intval($inValue)));
                    break;
            }
        }

        public function __get($inName)
        {
            switch($inName) {
                case 'ts':
                    if ($this->value == '') {
                        return DEFAULT_UNDEFINED_DATE;
                    }
                    if ($this->valueState !== FORM_VARIABLE_STATE_VALID) {
                        return DEFAULT_UNDEFINED_DATE;
                    }
                    return strtotime($this->value);
            }

            return parent::__get($inName);
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (! checkdate(intval(substr($this->value,5,2)), intval(substr($this->value,8,2)), intval(substr($this->value,0,4)))) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            $this->options['pattern']='\d{4}-\d{2}-\d{2}';
            parent::renderElement($inClasses, $inStyles);
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with datetime-local input type

    class DateTimeLocal extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'datetime-local';
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'ts':
                    $st = date('c', intval($inValue));
                    $pieces = explode('+', $st);
                    if (count($pieces) > 2) {
                        break;
                    }
                    $st = $pieces[0];
                    $pieces = explode(':', $st);
                    if (count($pieces) > 2) {
                        $st = $pieces[0] . ':' . $pieces[1];
                    }
                    parent::setValue($st);
                    break;
            }
        }

        public function __get($inName)
        {
            switch($inName) {
                case 'ts':
                    if ($this->value == '') {
                        return DEFAULT_UNDEFINED_DATE;
                    }
                    if ($this->valueState !== FORM_VARIABLE_STATE_VALID) {
                        return DEFAULT_UNDEFINED_DATE;
                    }
                    return strtotime($this->value);
            }

            return parent::__get($inName);
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (! checkdate(intval(substr($this->value,5,2)), intval(substr($this->value,8,2)), intval(substr($this->value,0,4)))) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', substr($this->value,11)) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            $this->options['pattern']='[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}';
            parent::renderElement($inClasses, $inStyles);
        }

        public function setNow()
        {
            $this->value = date('Y-m-d\TH:i');
        }

    }



//----------------------------------------------------------------------
//-- Dollar Input Type

    class Dollar extends \Framework\Form\Variable\BaseRender
    {
        protected $maxValue;

        public function __construct()
        {
            parent::__construct();
            $this->options['pattern'] = '^\$?\d{1,3}(,\d{3})*(\.\d{2})?$';
            $this->options['data-type'] = 'currency';
            $this->maxValue = 0;
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'maxValue':
                    $this->maxValue = floatval($inValue);
                    break;
            }
        }

        protected function renderValue()
        {
            if ($this->value == '') {
                parent::renderValue();
            } else {
                print ' value="$' . number_format(floatval($this->value),2) . '"';
            }
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (! is_numeric($this->value)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            $currentVal = floatval($this->value);

            if ($this->maxValue > 0) {
                if ($currentVal > $this->maxValue) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = 'Exceeds max dollar amount $' . $this->maxValue;
                    return $this->valueState;
                }
            }

            return $this->valueState;
        }

        public function setValue($inValue, $inSetState=true)
        {
            parent::setValue($inValue, $inSetState);

            $this->value = str_replace('$','', $this->value);
            $this->value = str_replace(',','', $this->value);
            $this->value = trim($this->value);

            if (is_numeric($this->value)) {
                $this->value = number_format(floatval($this->value),2,'.','');
            }
        }
    } 



//----------------------------------------------------------------------
//-- Class to handle form variables with email input

    class Email extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueName = 'email';
            $this->valueID = 'email';
            $this->labelText = '<b>Email</b>';
            $this->valueType = 'email';
            $this->inputSize = 50;
            $this->autoComplete = 'email';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with file input type

    class File extends \Framework\Form\Variable\BaseRender
    {
        protected $fileType;
        protected $fileTmpName;
        protected $fileExt;
        protected $fileError;
        protected $fileSize;
        protected $allowedFileTypes;
        protected $allowedFileExts;
        protected $allowedMaxSize;
        protected $allowZeroSize;

        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'file';
            $this->allowedFileTypes = array();
            $this->allowedFileExts = array();
            $this->allowedMaxSize = 0;
            $this->allowZeroSize = true;
        }

        public function reset()
        {
            parent::reset();
            $this->fileType = '';
            $this->fileTmpName = '';
            $this->fileError = 0;
            $this->fileSize = 0;
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'allowedFileExts':
                    if (! is_array($inValue)) {
                        break;
                    }
                    foreach($inValue as $ndx => $value) {
                        $inValue[$ndx] = strtolower($value);
                    }
                    $this->allowedFileExts = $inValue;
                    break;

                case 'allowedFileTypes':
                    if (! is_array($inValue)) {
                        return;
                    }
                    foreach($inValue as $ndx => $value) {
                        $inValue[$ndx] = strtolower($value);
                    }
                    $this->allowedFileTypes = $inValue;
                    break;

                case 'allowedMaxSize':
                    if (! is_int($inValue)) {
                        break;
                    }
                    if ($inValue < 0) {
                        break;
                    }
                    $this->allowedMaxSize = $inValue;
                    break;

                case 'allowZeroSize':
                    if ($inValue === true) {
                        $this->allowZeroSize = true;
                    } else {
                        $this->allowZeroSize = false;
                    }
                    break;
            }
        }

        public function __get($inName)
        {
            switch($inName) {
                case 'fileType':
                    return $this->fileType;
                case 'fileTmpName':
                    return $this->fileTmpName;
                case 'fileError':
                    return $this->fileError;
                case 'fileSize':
                    return $this->fileSize;
            }

            return parent::__get($inName);
        }

        public function collect()
        {
            $this->valueState = FORM_VARIABLE_STATE_VALID;
            $this->value = '';
            $this->fileType = '';
            $this->fileTmpName = '';
            $this->fileExt = '';
            $this->fileError = 0;
            $this->fileSize = 0;
            $this->collected = false;

            if (empty($_FILES)) {
                if ($this->required) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                }
                return;
            }

            if (! array_key_exists($this->valueName, $_FILES)) {
                if ($this->required) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                }
                return;
            }

            if ($_FILES[$this->valueName]['error'] == UPLOAD_ERR_NO_FILE) {
                if (! $this->required) {
                    return;
                }
                $this->collected = true;
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return;
            }

            $this->collected   = true;
            $this->value       = trim($_FILES[$this->valueName]['name']);
            $this->fileType    = strtolower(trim($_FILES[$this->valueName]['type']));
            $this->fileTmpName = trim($_FILES[$this->valueName]['tmp_name']);
            $this->fileExt     = strtolower(pathinfo($_FILES[$this->valueName]['full_path'], PATHINFO_EXTENSION));
            $this->fileError   = $_FILES[$this->valueName]['error'];
            $this->fileSize    = $_FILES[$this->valueName]['size'];

            if ($this->fileError != FILE_UPLOAD_ERR_OK) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                $this->errMsg = 'There was an error while uploading the file';
                return;
            }

            if (count($this->allowedFileTypes) > 0) {
                $typeFound = false;
                foreach($this->allowedFileTypes as $fileType) {
                    if ($fileType == $this->fileType) {
                        $typeFound = true;
                        break;
                    }
                }
                if (! $typeFound) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = 'Not an allowed file type';
                    return;
                }
            }

            if (count($this->allowedFileExts) > 0) {
                $extFound = false;
                foreach($this->allowedFileExts as $fileExt) {
                    if ($fileExt == $this->fileExt) {
                        $extFound = true;
                        break;
                    }
                }
                if (! $extFound) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = 'Not an allowed file extension';
                    return;
                }
            }

            if (! $this->allowZeroSize) {
                if ($this->fileSize < 1) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = 'Uploaded file is empty';
                    return;
                }
            }

            if ($this->allowedMaxSize > 0) {
                if ($this->allowedMaxSize < $this->fileSize) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = 'Uploaded file is too large';
                    return;
                }
            }
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with month input type

    class Month extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'month';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]{4}-[0-9]{2}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            $this->options['pattern']='[0-9]{4}-[0-9]{2}';
            parent::renderElement($inClasses, $inStyles);
        }

    }



//----------------------------------------------------------------------
//-- Number Input Type

    class Number extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'number';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9\+\-\.]+$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (! is_numeric($this->value)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with password input

    class Password extends \Framework\Form\Variable\BaseRender
    {
        protected $runComplexityCheck;

        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'password';
            $this->runComplexityCheck = true;
        }


        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'runComplexityCheck':
                    if ($inValue === true) {
                        $this->runComplexityCheck = true;
                    } else {
                        $this->runComplexityCheck = false;
                    }
                    break;
            }
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if ($this->runComplexityCheck === true) {
                if (! \App\passwordComplexityCheck($this->value)) {
                    $this->valueState  = FORM_VARIABLE_STATE_INVALID;
                    $this->errMsg = PASSWORD_COMPLEXITY_ERR_MSG;
                    return $this->valueState;
                }
            }

            return $this->valueState;
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with radio input type
//-- NOTE: renderRadio would be called multiple times with $inButton
//--       specifying multiple different index values 0, 1, 2, ....

    class Radio extends \Framework\Form\Variable\BaseRender
    {
        protected $numButtons;
        protected $buttonLabelText;
        protected $buttonLabelClasses;

        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'radio';
            $this->numButtons = 0;
            $this->buttonLabelText = array();
            $this->butonLabelClasses = array();
        }

        public function reset()
        {
            parent::reset();
            $this->value = 0;
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'buttons':
                    if (! is_array($inValue)) {
                        break;
                    }
                    $buttonNdx = 0;
                    $this->buttonLabelText = array();
                    foreach($inValue as $buttonText) {
                        $this->buttonLabelText[$buttonNdx] = $buttonText;
                        $buttonNdx++;
                    }
                    $this->numButtons = count($this->buttonLabelText);
                    break;

                case 'buttonLabelClasses':
                    if (! is_array($inValue)) {
                        break;
                    }
                    $this->buttonLabelClasses = $inValue;
                    break;
            }
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if ($this->value < 0) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if ($this->value >= $this->numButtons) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function setValue($inValue, $inSetState=true)
        {
            parent::setValue($inValue, $inSetState);

            if ($this->valueState !== FORM_VARIABLE_STATE_INIT) {
                return;
            }

            $nameLength = strlen($this->valueName);

            if (substr($this->value,0,$nameLength) != $this->valueName) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return;
            }

            $this->value = substr($this->value,$nameLength);

            if (! is_numeric($this->value)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return;
            }

            $this->value = intval($this->value);
        }

        public function renderRadio(int $inButton, array $inClasses=array(), array $inStyles = array())
        {
            if ($inButton < 0) {
                return;
            }

            if ($inButton >= $this->numButtons) {
                return;
            }

            $currentValue = $this->value;

            if ($this->value == $inButton) {
                $this->options['checked'] = NULL;
            }

            $this->value = $this->valueName . $inButton;
            $this->valueID = $this->valueName . $inButton;

            parent::renderElement($inClasses, $inStyles);

            //-- undo temporary values
            $this->value = $currentValue;
            unset($this->options['checked']);

            parent::renderLabel($this->buttonLabelClasses, $this->buttonLabelText[$inButton], $this->valueID);
        }

        public function renderElement(array $inClasses=array(), array $inStyles=array())
        {
            for($i = 0 ; $i < $this->numButtons ; $i++ ) {
                $this->renderRadio($i, $inClasses, $inStyles);
            }
        }

    }



//----------------------------------------------------------------------
//-- Select Class
//--
//-- NOTE: for searchable functionality, you will need to provide
//--       javascript code to implement search

    class Select extends \Framework\Form\Variable\BaseRender
    {
        protected $selectOptions;
        protected $initialOptionKey;
        protected $initialOptionText;
        protected $validateInList;
        protected $searchable;
        protected $selectMultiple;

        public function __construct()
        {
            parent::__construct();
            $this->selectOptions = array();
            $this->initialOptionKey = false;
            $this->initialOptionText = '';
            $this->validateInList = true;
            $this->searchable = false;
            $this->selectMultiple = false;
            $this->valueType = 'select';
        }

        public function __set($inName, $inValue)
        {
            switch($inName) {
                case 'required':
                    if ($inValue === true) {
                        $this->validateInList = true;
                    } else {
                        $this->validateInList = false;
                    }
                    break;

                case 'searchable':
                    if ($inValue === true) {
                        $this->searchable = true;
                    } else {
                        $this->searchable = false;
                    }
                    break;

                case 'selectMultiple':
                    if ($inValue === true) {
                        $this->selectMultiple = true;
                    } else {
                        $this->selectMultiple = false;
                    }
                    break;

                case 'validateInList':
                    if ($inValue === true) {
                        $this->validateInList = true;
                    } else {
                        $this->validateInList = false;
                    }
                    break;
            }

            parent::__set($inName, $inValue);
        }

        public function setValue($inValue, $inSetState=true)
        {
            if ($this->selectMultiple === false) {
                parent::setValue($inValue, $inSetState);
                return;
            }

            if ($inSetState === true) {
                $this->valueState = FORM_VARIABLE_STATE_INIT;
            }

            $this->value = array();

            if (! is_array($inValue)) {
                return;
            }

            foreach($inValue as $value) {
                if ($this->runHtmlSpecialChars) {
                    if (is_string($value)) {
                        $inValue = htmlspecialchars($value);
                    }
                }

                $value = trim(strval($value));

                if ($value != '') {
                    $this->value[] = $value;
                }
            }
        }

        public function validate()
        {
            if ($this->selectMultiple === false) {
                if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                    return $this->valueState;
                }

                if (! $this->validateInList) {
                    return $this->valueState;
                }

                if (! array_key_exists($this->value, $this->selectOptions)) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    return $this->valueState;
                }

                return $this->valueState;
            }

            $this->valueState = FORM_VARIABLE_STATE_VALID;

            if (! is_array($this->value)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (count($this->value) == 0) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (! $this->validateInList) {
                return $this->valueState;
            }

            foreach($this->value as $v) {
                if (! array_key_exists($v, $this->selectOptions)) {
                    $this->valueState = FORM_VARIABLE_STATE_INVALID;
                    return $this->valueState;
                }
            }

            return $this->valueState;
        }

        protected function renderName()
        {
            print 'name="' . $this->valueName;
            if ($this->selectMultiple) {
                print '[]';
            }
            print '" ';
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            if ($this->searchable) {
                print '<searchable-select>' . PHP_EOL;
                if (! array_key_exists('class', $this->options)) {
                    $this->options['class'] = '';
                }
                $this->options['class'] = $this->options['class'] . ' custom-select';
            }

            print '<select ';

            if ($this->hidden) {
                print ' hidden ';
            } 

            if ($this->selectMultiple) {
                print ' multiple ';
            } 

            if (! is_array($inStyles)) {
                $inStyles = array();
            }

            $inStyles[] = 'field-sizing: fixed';

            $this->renderAttributes($inClasses, $inStyles);

            print '>' . PHP_EOL;

            if ($this->initialOptionKey !== false) {
                print '    <option value="' . $this->initialOptionKey . '">' . $this->initialOptionText . '</option>' . PHP_EOL;
            }

            if ($this->selectMultiple === true) {
                if (! is_array($this->value)) {
                    $this->value = array();
                }
            }

            foreach($this->selectOptions as $optionValue => $optionText) {
                print '    <option value="' . $optionValue . '"';
                if ($this->selectMultiple === true) {
                    if (in_array($optionValue, $this->value)) {
                        print ' selected';
                    }                    
                } else {
                    if ($optionValue == $this->value) {
                        print ' selected';
                    }
                }
                print '>' . $optionText . '</option>' . PHP_EOL;
            }

            print '</select>' . PHP_EOL;

            if ($this->searchable) {
                print '</searchable-select>' . PHP_EOL;
            }
        }

        public function setSelectOptions($inSelectOptions,$inSort=true)
        {
            if (is_array($inSelectOptions)) {
                if ($inSort === true) {
                    asort($inSelectOptions);
                }
                $this->selectOptions = $inSelectOptions;
            }
        }

        public function setInitialOption($inOptionKey, $inOptionText)
        {
            $this->initialOptionKey  = $inOptionKey;
            $this->initialOptionText = $inOptionText;
        }

        public function removeInitialOption()
        {
            $this->initialOptionKey = false;
            $this->initialOptionText = '';
        }

        public function getSelectOptions()
        {
            return $this->selectOptions;
        }

        public function selectOptionsCount()
        {
            return count($this->selectOptions);
        }

        public function selectFirst()
        {
            $keys = array_keys($this->selectOptions);
            if (count($keys) != 1) {
                return;
            }

            $this->value = $keys[0];
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with tel input type

    class Telephone extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'tel';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            $this->options['pattern'] = '[0-9]{3}-[0-9]{3}-[0-9]{4}';
            parent::renderElement($inClasses, $inStyles);
        }

    }



//----------------------------------------------------------------------
//-- Text Input Type

    class Text extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->normalizeSpaces = true;
        }
    }



//----------------------------------------------------------------------
//-- Class to handle form variables with textarea input type

    class TextArea extends \Framework\Form\Variable\BaseRender
    {
        protected $rows;
        protected $cols;

        public function __construct()
        {
            parent::__construct();
            $this->rows = 0;
            $this->cols = 0;
        }

        public function __set($inName, $inValue)
        {
            parent::__set($inName, $inValue);

            switch($inName) {
                case 'rows':
                    $this->rows = inttval($inValue);
                    break;

                case 'cols':
                    $this->cols = inttval($inValue);
                    break;
            }
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            if ($this->rows > 0) {
                $this->options['rows'] = $this->rows;
            }

            if ($this->cols > 0) {
                $this->options['cols'] = $this->cols;
            }

            print '<textarea ' . "\n";

            if ($this->hidden) {
                print ' hidden ';
            }

            $this->renderAttributes($inClasses, $inStyles);

            print '>' . $this->value . '</textarea>' . PHP_EOL;
        }

    }



//----------------------------------------------------------------------
//-- Text "Number" Input Type
//-- a "number" that does not use the Number HTML input type so that
//-- the Up/Down selectors are not part of the form field

    class TextNumber extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->normalizeSpaces = true;
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]+$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            if (! is_numeric($this->value)) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function setValue($inValue, $inSetState=true)
        {
            $this->autoTrim = true; //-- Force Auto Trim
            parent::setValue($inValue, $inSetState);
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with time input type

    class Time extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'time';
        }

        public function validate()
        {
            if (parent::validate() != FORM_VARIABLE_STATE_VALID) {
                return $this->valueState;
            }

            if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $this->value) !== 1) {
                $this->valueState = FORM_VARIABLE_STATE_INVALID;
                return $this->valueState;
            }

            return $this->valueState;
        }

        public function renderElement(array $inClasses=array(), array $inStyles = array())
        {
            $this->options['pattern']='[0-9]{2}:[0-9]{2}';
            parent::renderElement($inClasses, $inStyles);
        }

    }



//----------------------------------------------------------------------
//-- Class to handle form variables with search input type

    class Search extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'search';
        }
    }



//----------------------------------------------------------------------
//-- Class to handle form variables with url input type

    class URL extends \Framework\Form\Variable\BaseRender
    {
        public function __construct()
        {
            parent::__construct();
            $this->valueType = 'url';
        }
    }


