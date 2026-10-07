<?php
/**
 * Middleware/settings/edit/date/script.php
 *
 * Handles the updating of a "date" config value
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

require(MIDDLEWARE_ROOT . 'lib/SettingsForm.php');

class SettingsDateForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_date_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_DATE;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Date;
        parent::buildFormVariables();
    }

    protected function loadWriteValues()
    {
        parent::loadWriteValues();
        $this->formVariables['value']->value = date('Y-m-d', intval($this->setting->value));
    }

    protected function valueString()
    {
        return strval($this->formVariables['value']->ts);
    }

}


