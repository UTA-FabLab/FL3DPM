<?php
/**
 * app/lib/Mail/ProblemColor.php
 *
 * Sends email to user indicating need to select a new color
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

    $emailSubject = APP_ENTITY . ' - ' . APP_FULL_NAME . ' - Select Color Request';

    $emailMessage  = '<html>';
    $emailMessage .= '  <head>';
    $emailMessage .= '    <title>' . $emailSubject . '</title>';
    $emailMessage .= '  </head>';
    $emailMessage .= '  <body>';

    $emailMessage .= '<p>';
    $emailMessage .= 'Regarding your recent consultation with our staff regarding your 3D print project,';
    $emailMessage .= '</p>';
 
    $emailMessage .= '<p>';
    $emailMessage .= 'We wanted to let you know that, unfortunately, the filament color you selected is currently out of stock.';
    $emailMessage .= '</p>';
 
    $emailMessage .= '<p>';
    $emailMessage .= 'To keep your project moving forward without delay, we kindly ask that you choose an alternative ';
    $emailMessage .= 'filament color. Please feel free to reply to this email message with your new selection, or stop by the ';
    $emailMessage .= APP_TEAM_SVC_NAME . ' to view the available options in person.  We apologize for the inconvenience and appreciate ';
    $emailMessage .= 'your understanding. Let us know how you’d like to proceed!';
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

