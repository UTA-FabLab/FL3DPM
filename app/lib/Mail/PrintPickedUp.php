<?php
/**
 * app/lib/Mail/PrintPickedup.php
 *
 * Script for generating email to indicate that print has been completed and
 * picked up by the customer.
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

    $emailSubject = APP_ENTITY . ' - ' . APP_FULL_NAME . ' - Print Completion';

    $emailMessage  = '<html>';
    $emailMessage .= '  <head>';
    $emailMessage .= '    <title>' . $emailSubject . '</title>';
    $emailMessage .= '  </head>';
    $emailMessage .= '  <body>';

    $emailMessage .= '<p>';
    $emailMessage .= 'Your ' . APP_TEAM_SVC_NAME . ' print has been completed and picked up successfully! ';
    $emailMessage .= '</p>';

    $emailMessage .= '<p>';
    $emailMessage .= 'We also have 3D print weeding and trimming kits (subject to availability) available for ';
    $emailMessage .= 'checkout through the ' . APP_TEAM_LENDING_PROGRAM_NAME . ', if should you need them to help ';
    $emailMessage .= 'complete your project. Thank you for using the ' . APP_TEAM_SVC_NAME . '! We look forward to seeing you soon. ';
    $emailMessage .= 'If you have any other projects that you need assistance with, please come in for a consultation ';
    $emailMessage .= 'with any of our employees. You can also email us at ';
    $emailMessage .= '<a href="mailto:' . APP_TEAM_EMAIL . '">' . APP_TEAM_EMAIL . '</a> ';
    $emailMessage .= 'or call us at ' . APP_TEAM_PHONE . ' if you have any additional questions or concerns.';
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


