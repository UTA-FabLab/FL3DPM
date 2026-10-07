<?php
/**
 * Middleware/settings/edit/number/script.php
 *
 * Handles the updating of a "number" config value
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

class SettingsNumberForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_number_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_NUMBER;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Number;
        parent::buildFormVariables();
    }

}


