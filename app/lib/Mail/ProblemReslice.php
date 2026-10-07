<?php
/**
 * app/lib/Mail/ProblemReslice.php
 *
 * Sends email to user indicating need to reslice project
 *
 * If you want to provide a customer version of this email, you can copy this
 * script into app/instance/Mail, and then modify.
 *
 * Variables that need to be set
 * -----------------------------
 * emailSubject: required string
 * emailMessage: required string
 * emailBCC: optional array
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

    $emailSubject = APP_ENTITY . ' - ' . APP_FULL_NAME . ' - Reslice Request';

    $emailMessage  = '<html>';
    $emailMessage .= '  <head>';
    $emailMessage .= '    <title>' . $emailSubject . '</title>';
    $emailMessage .= '  </head>';
    $emailMessage .= '  <body>';

    $emailMessage .= '<p>';
    $emailMessage .= 'Regarding your recent consultation with our staff on your 3D print project,';
    $emailMessage .= '</p>';
 
    $emailMessage .= '<p>';
    $emailMessage .= 'Unfortunately, it appears the G-code file we have on file for your print contains issues ';
    $emailMessage .= 'that are preventing it from printing successfully.';
    $emailMessage .= '</p>';
 
    $emailMessage .= '<p>';
    $emailMessage .= 'To resolve this, please return to the ' . APP_TEAM_SVC_NAME . ' at your earliest convenience to re-slice your ';
    $emailMessage .= 'STL file and mention this email.  Once the print has been properly resliced for our printers ';
    $emailMessage .= 'it will allow us to complete your print job successfully.  We apologize for the inconvenience, ';
    $emailMessage .= 'and we look forward to getting your project back on track!';
    $emailMessage .= '</p>';

    $emailMessage .= '<p>';
    $emailMessage .= '~Sincerely, ' . APP_TEAM_NAME;
    $emailMessage .= '</p>';

    $footerImage = file_get_contents(APP_ROOT . 'assets/img/email-appteamsvc-logo.png');

    $emailMessage .= '<img src="data:image/jpeg;base64,' . base64_encode($footerImage) . '"/>' . PHP_EOL;

    $emailMessage .= ' &nbsp; ';

    $footerImage = file_get_contents(APP_ROOT . 'assets/img/email-entity-logo.png');

    $emailMessage .= '<img src="data:image/jpeg;base64,' . base64_encode($footerImage) . '"/>' . PHP_EOL;

    $emailMessage .= '  </body>';
    $emailMessage .= '</html>';

    //-- pull in application BCC

        $applicationBCC = \Middleware\getConfigValue('APPLICATION_EMAIL_BCC');

        if ($applicationBCC !== false) {
            $emailBCC = array_merge($emailBCC, explode(',', $applicationBCC));
        }


