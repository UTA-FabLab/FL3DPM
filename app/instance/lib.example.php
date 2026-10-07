<?php
/**
 * app/instance/lib.php
 *
 * Instance specific functions
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

//---------------------------------------------------------------------------------
//-- Password Complexity check for local auth passwords
//--
//-- NOTE: if you are using local authentication, then this function can be
//--       modified to meet your local institutions password complexity checks.
//---------------------------------------------------------------------------------

    define('PASSWORD_COMPLEXITY_ERR_MSG','Passwords be at least 8 characters in length, ' .
           'and have at least one digit, one lower case character, one upper case character, and one symbol.');

    function passwordComplexityCheck($inPassword)
    {
        if (strlen($inPassword) < 8) {
            return false;
        }

        if (preg_match('/[0-9]/', $inPassword) !== 1) {
            return false;
        }

        if (preg_match('/[a-z]/', $inPassword) !== 1) {
            return false;
        }

        if (preg_match('/[A-Z]/', $inPassword) !== 1) {
            return false;
        }

        if (preg_match('/[\/\\\~\[\]*.!@$%^&(){}:;<>,.?_+-=|]/', $inPassword) !== 1) {
            return false;
        }

        return true;
    }
 


//---------------------------------------------------------------------------------
//-- Validate an University ID 
//--
//-- NOTE: used by the Middleware UniversityID Form Variables so the function
//--       needs to be defined.  If you are excepting any ID form, you can
//--       just return true 
//---------------------------------------------------------------------------------

    function validUniversityID(string $inUniversityID)
    {
        if (preg_match('/^\d\d\d\d\d\d\d\d\d\d$/',$inUniversityID) != 1) {
            return false;
        }

        $prefix = substr($inUniversityID,0,4);

        switch($prefix) {
            case '1000':
                break;

            default:
                return false;
        }

        return true;
    }


//---------------------------------------------------------------------------------
//-- Validate an University Email
//--
//-- NOTE: used by the Middleware UniversityEmail Form Variables so the function
//--       needs to be defined.  If you are excepting any Email form, you can
//--       just return true 
//---------------------------------------------------------------------------------

    function validUniversityEmail(string $inUniversityEmail)
    {
        if (strpos($inUniversityEmail, '@') === false) {
            return false;
        }

        list($extra,$emailSuffix) = explode('@', trim($inUniversityEmail));

        switch($emailSuffix) {
            case 'uos.edu':
                break;
            default:
                return false;
        }

        return true;
    }


//---------------------------------------------------------------------------------
//-- Instance Collect User Details
//--
//-- NOTE: used to pull in custom information for the user
//---------------------------------------------------------------------------------

    function instanceCollectUserDetails(&$inIdentity)
    {
    }


