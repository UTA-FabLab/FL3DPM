<?php
/**
 * app/pages/projects/file/download/script.php
 *
 * Handles the process of downloading a file
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

//-- Validate user has permissions to access this function

    if (! \App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT))) {
        http_response_code(401);
        exit;
    }


//-- collect the project file id number

    $projectFileID = \App\Request::getQP('id');

    if ($projectFileID === false) {
        http_response_code(400);
        exit;
    }

    $projectFileID = trim($projectFileID);

    if ($projectFileID == '') {
        http_response_code(400);
        exit;
    }


//-- Find the project file record 

    $projectFile = new \App\Table\ProjectFilesRecord;
    $projectFile->withFile();

    if (! $projectFile->findByID($projectFileID)) {
        http_response_code(400);
        exit;
    }


//-- Send the image back

    header('Content-Type: application/octet-stream');
    header('Content-Length:' . $projectFile->file_size);
    header('Content-Disposition: filename="' . $projectFile->file_name . '"');
    echo base64_decode($projectFile->file_blob);

