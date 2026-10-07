<?php
/**
 * Middleware/settings/edit/string/script.php
 *
 * Handles the updating of a "string" config value
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

class SettingsStringForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_string_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_STRING;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Text;
        $this->formVariables['value']->size = 50;
        parent::buildFormVariables();
    }

}


