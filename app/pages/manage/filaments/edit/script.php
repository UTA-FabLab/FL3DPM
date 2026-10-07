<?php
/**
 * app/pages/manage/filaments/edit/script.php
 *
 * Handles the process of editing a filament
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

class FilamentEditPage extends \App\FilamentForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'manage_filament_edit_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Filament - Edit';
        $this->formTitle = 'Filament - Edit';
    }

    protected function getRecordID()
    {
        return $this->filament->id;
    }

    protected function scriptUpdate()
    {
        //-- Make sure we don't already have a filament with that name

            $filamentCheck = new \App\Table\FilamentsRecord;

            if ($filamentCheck->findByName($this->formVariables['filamentName']->value)) {
                if ($filamentCheck->id != $this->filament->id) {
                    $this->formErrorMessage = ERROR_MESSAGE_004;
                    return false;
                }
            }

            if ($filamentCheck->error()) {
                $this->formErrorMessage = ERROR_MESSAGE_006;
                return false;
            }

        //-- Add the new filament

            $params = array(
                'name' => $this->formVariables['filamentName']->value,
                'enabled' => $this->formVariables['enabled']->value,
            );

            if (! $this->filament->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_006;
                return false;
            }

        $this->redirectPage = 'manage/filaments/list';
        return true;
    }
}

$page = new FilamentEditPage;
$page->process();


