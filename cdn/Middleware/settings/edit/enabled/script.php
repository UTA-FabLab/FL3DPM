<?php
/**
 * Middleware/settings/edit/enabled/script.php
 *
 * Handles the updating of an "enabled" config value
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

class SettingsEnabledForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_enabled_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_ENABLED;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Select();
        parent::buildFormVariables();
        $this->formVariables['value']->label = '<b>Enabled</b>';
        $this->formVariables['value']->setInitialOption('-','Select yes or no');
        $this->formVariables['value']->setSelectOptions( \Framework\YesNoState::selectList() );
    }

}

