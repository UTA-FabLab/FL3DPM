<?php
/**
 * Middleware/settings/edit/location/script.php
 *
 * Handles the updating of a "location" config value
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

class SettingsLocationForm extends \MiddleWare\SettingsForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'settings_edit_location_csrf';
        parent::__construct();
        $this->valueType = SETTING_TYPE_LOCATION;
    }

    protected function buildFormVariables()
    {
        $this->formVariables['value'] = new \Framework\Form\Variable\Select();

        parent::buildFormVariables();

        $this->formVariables['value']->label = '<b>Building</b>';
        $this->formVariables['value']->setInitialOption('-','Select a building');
        $this->formVariables['value']->setSelectOptions( \MiddleWare\SiteLocations::selectList() );

        $this->otherFormVariables[] = 'room';

        $this->formVariables['room'] = new \Framework\Form\Variable\Text;
        $this->formVariables['room']->name     = 'room';
        $this->formVariables['room']->label    = '<b>Room</b>';
        $this->formVariables['room']->required = true;
    }

    protected function loadWriteValues()
    {
        parent::loadWriteValues();
        $this->formVariables['value']->value = \MiddleWare\SiteLocations::parseBuilding( $this->setting->value);
        $this->formVariables['room']->value = \MiddleWare\SiteLocations::parseRoom( $this->setting->value);
    }

    protected function renderExtraFields()
    {
        $this->formVariables['room']->render();
    }

    protected function valueString()
    {
        return $this->formVariables['value']->value . '-' . $this->formVariables['room']->value;
    }

}


