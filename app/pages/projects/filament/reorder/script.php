<?php
/**
 * app/pages/projects/filament/reorder/script.php
 *
 * Handles the process of reordering filaments for a project
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

define('MOVE_DIRECTION_UP',   'U');
define('MOVE_DIRECTION_DOWN', 'D');

//-- Get the project filament id

    $projectFilamentID = \App\Request::getQP('id');

    if ($projectFilamentID === false) {
        \Framework\redirectPage('projects/list');
        exit;
    }


//-- Get the direction

    $moveDirection = \App\Request::getQP('up');

    if ($moveDirection === false) {
        $moveDirection = \App\Request::getQP('down');
        if ($moveDirection === false) {
            \Framework\redirectPage('projects/list');
            exit;
        }
        $moveDirection = MOVE_DIRECTION_DOWN;
    } else {
        $moveDirection = MOVE_DIRECTION_UP;
    }


//-- Find the project filament record

    $projectFilamentID = trim($projectFilamentID);

    if ($projectFilamentID == '') {
        \Framework\redirectPage('projects/list');
        exit;
    }

    $projectFilament = new \App\Table\ProjectFilamentsRecord;

    if (! $projectFilament->findByID($projectFilamentID)) {
        \Framework\redirectPage('projects/list');
        exit;
    }


//-- Find the project

    $project = new \App\Table\ProjectsRecord;

    if (! $project->findByID($projectFilament->project_id)) {
        \Framework\redirectPage('projects/list');
        exit;
    }

    switch ($project->activePrintStatus()) {
        case PROJECT_PRINT_STATUS_WAITING:
        case PROJECT_PRINT_STATUS_PROBLEM:
            break;
        default:
            \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
            exit;
    }


//-- Collect a list of the filaments defined for the project

    $filamentList = new \App\Table\ProjectFilamentsList;

    $filamentList->whereExpressions(array($filamentList->asName() . '.project_id = :project_id'));

    $filamentList->orderBy('filament_order ASC');

    $params = array(
        'project_id' => $project->id
    );

    if (! $filamentList->query($params)) {
        \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
        exit;
    }


//-- Make a list of the records;

    $newList = array();

    $ndx = 1;

    while($filamentList->next()) {
        $newList[$ndx] = array(
            'id'                  => $filamentList->id,
            'filament_order'      => $filamentList->filament_order
        );
        $ndx++;
    }


//-- Find the target filament to move

    $targetEntry = false;
    foreach($newList as $ndx => $entry) {
        if ($projectFilament->id == $entry['id']) {
            $targetEntry = $ndx;
            break;
        }
    }

    if ($targetEntry === false) {
        \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
        exit;
    }


//-- Are we moving up?

    if ($moveDirection == MOVE_DIRECTION_UP) {
        if ($targetEntry > 1) {
            $entry1 = $newList[$targetEntry - 1];
            $entry2 = $newList[$targetEntry];
            $newList[$targetEntry - 1] = $entry2;
            $newList[$targetEntry]     = $entry1;
        }
    } else {
        if ($targetEntry < count($newList)) {
            $entry1 = $newList[$targetEntry];
            $entry2 = $newList[$targetEntry + 1];
            $newList[$targetEntry]     = $entry2;
            $newList[$targetEntry + 1] = $entry1;
        }
    }


//-- Reorder the list

    foreach($newList as $ndx => $entry) {
        $newList[$ndx]['filament_order'] = $ndx;
    }


//-- Update the records

    if (! \App\Request::$db->beginTransaction()) {
        \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
        exit;
    }

    foreach($newList as $ndx => $entry) {

        $projectFilament = new \App\Table\ProjectFilamentsRecord;

        if (! $projectFilament->findByID($newList[$ndx]['id'])) {
            \App\Request::$db->rollbackTransaction();
            \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
            exit;
        }

        $params = array(
            'filament_order' => $newList[$ndx]['filament_order']
        );

        if (! $projectFilament->update($params)) {
            \App\Request::$db->rollbackTransaction();
            \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');
            exit;
        }

    }

    \App\Request::$db->commitTransaction();

    \Framework\redirectPage('projects/view?id=' . $project->id . '&tab=filaments');

