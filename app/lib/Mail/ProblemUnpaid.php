<?php
/**
 * app/lib/Mail/ProblemUnpaid.php
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

    $emailSubject = APP_ENTITY . ' - ' . APP_FULL_NAME . ' - Pickup Request';

    $emailMessage  = '<html>';
    $emailMessage .= '  <head>';
    $emailMessage .= '    <title>' . $emailSubject . '</title>';
    $emailMessage .= '  </head>';
    $emailMessage .= '  <body>';

    $emailMessage .= '<p>';
    $emailMessage .= 'We are reaching out to inform you that you are unable to complete your 3d print at this time due to you ';
    $emailMessage .= 'having an outstanding charge for another 3D print which was not picked up.';
    $emailMessage .= '</p>';

    $emailMessage .= '<p>';
    $emailMessage .= 'Per our policy, we only allow one active print per learner, and learners are responsible for the costs of ';
    $emailMessage .= 'their prints regardless of if they are picked up or not.';
    $emailMessage .= '</p>';

    $emailMessage .= '<p>';
    $emailMessage .= 'You will need to come in and pick up your previous 3d print before we can proceed with printing your new item.';
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


