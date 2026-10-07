<?php
/**
 * app/lib/ProblemResolutions
 *
 * Functions to handle problem resolutions
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

trait ProblemResolutions {

    protected function problemResolutionReprint()
    {
        //-- Attempt to get a lock

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] != PROJECT_PRINT_STATUS_PROBLEM) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Update the current Project Print job to resolved

            $params = array(
                'status' => PROJECT_PRINT_STATUS_RESOLVED
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Update project print problem record

            $updateTime = \time();

            $params = array(
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_REPRINT,
                'action_taken_date'   => $updateTime,
            );

            if (! $this->projectPrintProblem->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Create a new project print record

            $updateTime++; 

            $newProjectPrint = new \App\Table\ProjectPrintsRecord();

            $params = array(
                'project_id'        => $this->project->id,
                'printer_id'        => $this->projectPrint->printer_id,
                'file_id'           => $this->projectPrint->file_id,
                'status'            => PROJECT_PRINT_STATUS_WAITING,
                'created'           => $updateTime,
                'wait_list_number'  => '',
                'job_ticket_number' => '',
                'is_reprint'        => 'Y',
            );

            if (! $newProjectPrint->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Redirect to the next page

            if (array_key_exists('button-update-ticket', $_POST)) {
                $this->redirectPage = 'prints/jobticket?id=' . $newProjectPrint->id;
            } else {
                $this->redirectPage = 'prints/view?id=' . $newProjectPrint->id;
            }

        return true;
    }


    protected function problemResolutionUploadReslice()
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

        //-- Attempt to get a lock to prevent duplication of new print

            $updateTime = \time();

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] != PROJECT_PRINT_STATUS_PROBLEM) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Update the current Project Print job to resolved

            $params = array(
                'status' => PROJECT_PRINT_STATUS_RESOLVED
            );

            if (! $this->projectPrint->update($params)) {
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

        //-- Store new project STL file with enabled set (optional)

            if ($stlFileContents != '') {

                $updateTime++;

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
                    $this->formErrorMessage = ERROR_MESSAGE_021;
                    return false;
                }

            }


        //-- Store new project gcode file with enabled set

            $updateTime++;

            $projectFile = new \App\Table\ProjectFilesRecord();

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
                $this->formErrorMessage = ERROR_MESSAGE_021;
                return false;
            }

            $newProjectFileID = $projectFile->lastInsertID();

            if ($newProjectFileID === false) {
                $this->formErrorMessage = ERROR_MESSAGE_021;
                return false;
            }


        //-- Update project print problem record

            $updateTime++;

            $params = array(
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_UPLOAD_RESLICE,
                'action_taken_date'   => $updateTime,
            );

            if (! $this->projectPrintProblem->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Create a new project print record

            $updateTime++;

            $newProjectPrint = new \App\Table\ProjectPrintsRecord();

            $params = array(
                'project_id'        => $this->project->id,
                'printer_id'        => $this->projectPrint->printer_id,
                'file_id'           => $newProjectFileID,
                'status'            => PROJECT_PRINT_STATUS_WAITING,
                'created'           => $updateTime,
                'wait_list_number'  => '',
                'job_ticket_number' => '',
                'is_reprint'        => 'Y',
            );

            if (! $newProjectPrint->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

        //-- Redirect to the next page

            if (array_key_exists('button-update-ticket', $_POST)) {
                $this->redirectPage = 'prints/jobticket?id=' . $newProjectPrint->id;
            } else {
                $this->redirectPage = 'prints/view?id=' . $newProjectPrint->id;
            }

        return true;
    }


    protected function problemResolutionLearnerPickup()
    {
        //-- Attempt to get a lock to prevent duplication of new print

            $record = \App\Request::$db->getRowLock('project_prints','id', $this->projectPrint->id);

            if ($record === false) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }

            if ($record['status'] != PROJECT_PRINT_STATUS_PROBLEM) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Update the current Project Print job to resolved

            $current_wait_list_number = $this->projectPrint->wait_list_number;

            $params = array(
                'status' => PROJECT_PRINT_STATUS_RESOLVED
            );

            if (! $this->projectPrint->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Update project print problem record

            $updateTime = \time();

            $params = array(
                'action_taken'        => PROJECT_PRINT_ACTION_TAKEN_LEARNER_PICKUP,
                'action_taken_date'   => $updateTime,
            );

            if (! $this->projectPrintProblem->update($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Create a new project print record

            $updateTime++;

            $newProjectPrint = new \App\Table\ProjectPrintsRecord();

            $params = array(
                'project_id'        => $this->project->id,
                'printer_id'        => $this->projectPrint->printer_id,
                'file_id'           => $this->projectPrint->file_id,
                'status'            => PROJECT_PRINT_STATUS_WAITING,
                'created'           => $updateTime,
                'wait_list_number'  => $current_wait_list_number,
                'job_ticket_number' => '',
                'is_reprint'        => 'N',
            );

            if (! $newProjectPrint->insert($params)) {
                $this->formErrorMessage = ERROR_MESSAGE_011;
                return false;
            }


        //-- Redirect to the next page

            if (array_key_exists('button-update-ticket', $_POST)) {
                $this->redirectPage = 'prints/jobticket?id=' . $newProjectPrint->id;
            } else {
                $this->redirectPage = 'prints/view?id=' . $newProjectPrint->id;
            }

        return true;
    }

}

