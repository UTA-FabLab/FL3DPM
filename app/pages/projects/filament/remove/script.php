<?php
/**
 * app/pages/projects/filament/remove/script.php
 *
 * Handles the process of removing a filament from a project
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

include(APP_ROOT . 'app/lib/ProjectFilamentForm.php');

class ProjectFilamentRemovePage extends \App\ProjectFilamentForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'projects_filament_remove_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Project - Remove Filament';
        $this->formTitle = 'Project - Remove Filament';
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();

        $this->formVariables['id']->required = true;
    }

    protected function loadReadOnlyValues()
    {
        $this->formVariables['project_id']->setID($this->project->type,$this->project->year,$this->project->id);
        $this->formVariables['filament_id']->value = $this->projectFilament->filament_id;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->projectFilament->id;
    }

    protected function getRecordID()
    {
        return $this->projectFilament->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- remove the current filament

            if (! $this->projectFilament->delete()) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Collect a list of the filaments defined for the project

            $filamentList = new \App\Table\ProjectFilamentsList;

            $filamentList->whereExpressions(array($filamentList->asName() . '.project_id = :project_id'));

            $filamentList->orderBy('filament_order ASC');

            $params = array(
                'project_id' => $this->project->id
            );

            if (! $filamentList->query($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Make a list of the records;

            $newList = array();
            $ndx = 1;

            while($filamentList->next()) {
                $newList[$ndx] = array(
                    'id'                  => $filamentList->id,
                    'filament_order'      => $ndx
                );
                $ndx++;
            }

        //-- Update the records

            foreach($newList as $ndx => $entry) {

                $projectFilament = new \App\Table\ProjectFilamentsRecord;

                if (! $projectFilament->findByID($newList[$ndx]['id'])) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                }

                $params = array(
                    'filament_order' => $newList[$ndx]['filament_order']
                );

                if (! $projectFilament->update($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_011;
                    return false;
                }

            }


        //-- Go back to the filament list

            $this->redirectPage = 'projects/view?id=' . $this->project->id . '&tab=filaments';
            return true;
    }

}

$page = new ProjectFilamentRemovePage;
$page->process();

