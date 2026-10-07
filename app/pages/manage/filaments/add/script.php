<?php
/**
 * app/pages/manage/filaments/add/script.php
 *
 * Handles the process of adding a new filament
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

include(APP_ROOT . 'app/lib/FilamentForm.php');

class FilamentAddPage extends \App\FilamentForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_filament_add_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Filament - Add';
        $this->formTitle = 'Filament - Add';
        $this->postCollectID = false;
        $this->useTokenRecordID = false; 
    }

    protected function findRecord($inRecordID=false)
    {
        return true;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();
        $this->formVariables['id']->readOnly = true;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we don't already have a filament with that name

            $filament = new \App\Table\FilamentsRecord;

            if ($filament->findByName($this->formVariables['filamentName']->value)) {
                $this->formErrorMessage = ERROR_MESSAGE_004;
                return false;
            }

            if ($filament->error()) {
                $this->formErrorMessage = FORM_ERROR_MESSAGE_005;
                return false;
            }

        //-- Add the new filament

            $params = array(
                'name'    => $this->formVariables['filamentName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $filament->insert($params)) {
                $this->formErrorMessage = FORM_ERROR_MESSAGE_005;
                return false;
            }

        $this->redirectPage = 'manage/filaments/list';
        return true;
    }
}

$page = new FilamentAddPage;
$page->process();

