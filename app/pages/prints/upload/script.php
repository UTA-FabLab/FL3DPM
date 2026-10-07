<?php
/**
 * app/pages/prints/upload/script.php
 *
 * Handles the process of uploading gcode and stl files while a project is in a wait status
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

require(APP_ROOT . 'app/lib/ProjectPrintForm.php');

class PrintUploadForm extends \App\ProjectPrintForm
{
    public function __construct()
    {
        $this->csrfTokenName = 'prints_upload_csrf';
        parent::__construct();
        $this->pageDirectory = dirname(__FILE__) . '/';
        $this->pageTitle = 'Print - Upload Files';
        $this->formTitle = 'Print - Upload Files';
    }

    protected function canEdit()
    {
        if ($this->project->status == PROJECT_STATUS_ACTIVE) {
            switch($this->project->activePrintStatus()) {
                case PROJECT_PRINT_STATUS_WAITING:
                    return true;
                    break;
            }
        }
        return false;
    }

    protected function buildFormVariables()
    {
        parent::buildFormVariables();
        $this->formVariables['id']->required = true;

        $this->formVariables['project_id']->readOnly = true;

        $this->formVariables['gcode_file'] = new \App\Form\Variable\GCodeFile;
        $this->formVariables['gcode_file']->required = true;

        $this->formVariables['stl_file'] = new \App\Form\Variable\STLFile;
        $this->formVariables['stl_file']->optional = true;
        $this->formVariables['stl_file']->label = 'STL File (optional)';
    }

    protected function loadReadOnlyValues()
    {
        parent::loadReadOnlyValues();
        $this->formVariables['project_id']->value = $this->project->normalize('id');
        $this->formVariables['gcode_file']->autoFocus = true;
    }

    protected function loadWriteValues()
    {
        $this->formVariables['id']->value = $this->projectPrint->id;
    }

    protected function getRecordID()
    {
        return $this->projectPrint->id;
    }

    protected function postValidate()
    {
        return true;
    }

    protected function scriptUpdate()
    {
        //-- pull in the gCode file

            if ($this->formVariables['gcode_file']->collected) {
                $gcodeFileContents = file_get_contents($this->formVariables['gcode_file']->fileTmpName);
                if ($gcodeFileContents === false) {
                    $this->formErrorMessage = ERROR_MESSAGE_017;
                    return false;
                }
            } else {
                $this->formErrorMessage = ERROR_MESSAGE_018;
                return false;
            }

        //-- pull in the STL file

            $stlFileContents = '';

            if ($this->formVariables['stl_file']->collected) {
                $stlFileContents = file_get_contents($this->formVariables['stl_file']->fileTmpName);
                if ($stlFileContents === false) {
                    $this->formErrorMessage = ERROR_MESSAGE_019;
                    return false;
                }
            }

        //-- Attempt to get a lock

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] != PROJECT_PRINT_STATUS_WAITING) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Set all exising project files to disabled

            $projectFiles = new \App\Table\ProjectFilesList();

            if (! $projectFiles->findByProjectID($this->project->id)) {
                $this->formErrorMessage = ERROR_MESSAGE_021;
                return false;
            }

            while ($projectFiles->next()) {
                $projectFile = new \App\Table\ProjectFilesRecord();
                if (! $projectFile->findbyID($projectFiles->id)) {
                    $this->formErrorMessage = ERROR_MESSAGE_021;
                    return false;
                }

                $params = array(
                    'status' => PROJECT_FILE_STATUS_DISABLED
                );

                if (! $projectFile->update($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_021;
                    return false;
                }
            }

        //-- Store new project STL file (optional)

            $updateTime = \time();

            if ($stlFileContents != '') {
                $projectFile = new \App\Table\ProjectFilesRecord();

                $params = array(
                    'project_id' => $this->project->id,
                    'status'     => PROJECT_FILE_STATUS_ENABLED, 
                    'file_name'  => $this->formVariables['stl_file']->value,
                    'file_type'  => PROJECT_FILE_TYPE_STL,
                    'file_size'  => $this->formVariables['stl_file']->fileSize,
                    'file_blob'  => base64_encode($stlFileContents),
                    'uploaded'   => $updateTime,
                );

                if (! $projectFile->insert($params)) {
                    $this->formErrorMessage = ERROR_MESSAGE_019;
                    return false;
                }
            }

        //-- Store the gCode file

            $updateTime++;

            $params = array(
                'project_id' => $this->project->id,
                'status'     => PROJECT_FILE_STATUS_ENABLED, 
                'file_name'  => $this->formVariables['gcode_file']->value,
                'file_type'  => PROJECT_FILE_TYPE_GCODE,
                'file_size'  => $this->formVariables['gcode_file']->fileSize,
                'file_blob'  => base64_encode($gcodeFileContents),
                'uploaded'   => $updateTime,
            );

            if (! $projectFile->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_017;
                return false;
            }

            $gCodeFileID = $projectFile->lastInsertID();

            if ($gCodeFileID === false) {
                $this->formErrorMessage = ERROR_MESSAGE_001;
                return false;
            }

        //-- Update the project print record

            $params = array(
                'file_id' => $gCodeFileID,
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_001;
                return false;
            }

        //-- Go back to the print view

            $this->redirectPage = 'prints/view?id=' . $this->projectPrint->id . '&tab=file';
            return true;
    }
}

$form = new PrintUploadForm();
$form->process();

