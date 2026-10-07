<?php
/**
 * Middleware/lib/Common.php
 *
 * Contains the procedures and classes specific to applications
 *
 * @package MiddleWare
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;


//---------------------------------------------------------------------------------
//-- CSS Styles for user preferences
//---------------------------------------------------------------------------------

    define('CSS_STYLE_LIGHT', 'light');
    define('CSS_STYLE_DARK',  'dark');

    class CSSStyles
    {
        use \Framework\VariableState;
        protected static $values = array(
            CSS_STYLE_LIGHT => array('text' => 'Light', 'selectable' => true, 'assignable' => true),
            CSS_STYLE_DARK  => array('text' => 'Dark',  'selectable' => true, 'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//-- Define user class for handling authentication / authorization
//---------------------------------------------------------------------------------

    define('APP_SESSION_DATA_TIMEOUT', 300);

    class User extends \Framework\User
    {
        protected $userDetails;
        protected $userACL;
        protected $userPreferences = array();
        protected $forceReload;
        protected $storeInSession;

        //---------------------------------------------------------------------------------
        //-- Resets the class variables

            public function reset()
            {
                parent::reset();
                $this->userDetails = array();
                $this->userACL = array();
                $this->userPreferences = array();
                $this->forceReload = false;
                $this->storeInSession = true;
            }


        //-----------------------------------------------------------------------------------------
        //-- Attempts to load user from session

            public function loadFromSession()
            {
                //-- call parent

                    if (! parent::loadFromSession()) {
                        return false;
                    }

                //-- determine if we are forcing a reload

                    if (array_key_exists('reload_session', $_SESSION[APP_SESSION_KEY])) {
                        unset($_SESSION[APP_SESSION_KEY]['reload_session']);
                        $this->forceReload = true;
                    }

                //-- collect information

                    $this->collectDetails();
                    $this->collectACL();
                    $this->collectPreferences();

                return true;
            }


        //-----------------------------------------------------------------------------------------
        //-- Collect user information once user has been loaded from session

            public function collectDetails()
            {
                //-- see if we have user details

                    $this->userDetails = array();

                    if ($this->storeInSession) {
                        if (! array_key_exists('user_details', $_SESSION[APP_SESSION_KEY])) {
                            $_SESSION[APP_SESSION_KEY]['user_details'] = array(
                                'ts' => 0,
                                'data' => array()
                            );
                        }
                    }

                //-- check if we are logged in

                    if (! $this->loggedIn) {
                        return;
                    }

                //-- load data from session if we don't need to collect

                    if (! $this->forceReload) {
                        if ($this->storeInSession) {
                            if ($_SESSION[APP_SESSION_KEY]['user_details']['ts'] > \time()) {
                                $this->userDetails = $_SESSION[APP_SESSION_KEY]['user_details']['data'];
                                return;
                            }
                        }
                    }

                //-- pull in the information from the identity record

                    $user = new \Middleware\Table\StaffIdentityRecord;

                    if (! $user->findByEmail($this->userIdentity)) {
                        if ($this->storeInSession) {
                            $_SESSION[APP_SESSION_KEY]['user_details'] = array(
                                'ts' => \time() + APP_SESSION_DATA_TIMEOUT,
                                'data' => array()
                            );
                        };
                        return;
                    }

                    $identity = $user->record();

                //-- pull in the instance information

                    \App\instanceCollectUserDetails($identity);

                //-- store in class and session if requested

                    $this->userDetails = $identity;

                    unset($identity['password_hash']);
                    unset($identity['password_reset_token']);

                    if ($this->storeInSession) {
                        $_SESSION[APP_SESSION_KEY]['user_details'] = array(
                            'ts' => \time() + APP_SESSION_DATA_TIMEOUT,
                            'data' => $identity
                        );
                    }
            }


        //----------------------------------------------------------------------
        //-- Collect user ACL once user has been loaded from session

            public function collectACL()
            {
                //-- see if we have user details

                    $this->userACL = array();

                    if ($this->storeInSession) {
                        if (! array_key_exists('user_acl', $_SESSION[APP_SESSION_KEY])) {
                            $_SESSION[APP_SESSION_KEY]['user_acl'] = array(
                                'ts' => 0,
                                'data' => array()
                            );
                        }
                    }

                //-- check if we are logged in

                    if (! $this->loggedIn) {
                        return;
                    }

                //-- load data from session if we don't need to collect

                    if (! $this->forceReload) {
                        if ($this->storeInSession) {
                            if ($_SESSION[APP_SESSION_KEY]['user_acl']['ts'] > \time()) {
                                $this->userACL = $_SESSION[APP_SESSION_KEY]['user_acl']['data'];
                                return;
                            }
                        }
                    }

                //-- pull in the information from the acl records

                    $query = 'SELECT tag FROM ' . APP_MYSQL_DB . '.acl WHERE user_id = :user_id';

                    $params = array(
                        ':user_id' => $this->userID
                    );

                    $permissionRecords = \App\Request::$db->fetchAll($query,$params);

                    $userACL = array();
                    foreach($permissionRecords as $record) {
                        $userACL[ $record['tag'] ] = 1;
                    }

                    if ($this->storeInSession) {
                        $_SESSION[APP_SESSION_KEY]['user_acl'] = array(
                            'ts' => \time() + APP_SESSION_DATA_TIMEOUT,
                            'data' => $userACL
                        );
                    }

                    $this->userACL = $userACL;
            }


        //----------------------------------------------------------------------
        //-- Collect user pereferences once user has been loaded from session

            public function collectPreferences()
            {
                //-- see if we have user preferences

                    $this->userPreferences = array();

                    if ($this->storeInSession) {
                        if (! array_key_exists('user_preferences', $_SESSION[APP_SESSION_KEY])) {
                            $_SESSION[APP_SESSION_KEY]['user_preferences'] = array(
                                'ts' => 0,
                                'data' => array()
                            );
                        }
                    }

                //-- check if we are logged in

                    if (! $this->loggedIn) {
                        return;
                    }

                //-- load data from session if we don't need to collect

                    if (! $this->forceReload) {
                        if ($this->storeInSession) {
                            if ($_SESSION[APP_SESSION_KEY]['user_preferences']['ts'] > \time()) {
                                $this->userPreferences = $_SESSION[APP_SESSION_KEY]['user_preferences']['data'];
                                return;
                            }
                        }
                    }

                //-- pull in the information from the user preferences

                    $query = 'SELECT * FROM ' . APP_MYSQL_DB . '.user_preferences WHERE user_id = :user_id';

                    $params = array(
                        ':user_id' => $this->userID
                    );

                    $this->userPreferences = \App\Request::$db->fetchRecord($query,$params);

                    if ($this->userPreferences === false) {
                        $this->userPreferences = array();
                    }

                    if ($this->storeInSession) {
                        $_SESSION[APP_SESSION_KEY]['user_preferences'] = array(
                            'ts' => \time() + APP_SESSION_DATA_TIMEOUT,
                            'data' => $this->userPreferences
                        );
                    }
            }


        //----------------------------------------------------------------------
        //-- Determine if the current user has an ACL permission tag

            public function hasACL($inACL) 
            {
                if (! $this->loggedIn) {
                    return false;
                }

                if (! is_array($this->userACL)) {
                    return false;
                }

                if (! is_array($inACL)) {
                    if (! is_string($inACL)) {
                        return false;
                    }
                    $inACL = array($inACL);
                }

                foreach($inACL as $tag) {
                    if (! is_string($tag)) {
                        continue;
                    }
                    if (array_key_exists($tag, $this->userACL)) {
                        return true;
                    }
                }

                return false;
            }


        //----------------------------------------------------------------------
        //-- Attempts to find a user by email
    
            public function findByEmail($inUserEmail)
            {
                $this->storeInSession = false;
                $this->userIdentity = $inUserEmail;
                $this->collectDetails();

                return (count($this->userDetails) > 0 ? true : false);
            }


        //----------------------------------------------------------------------
        //-- Validate a user password against hash

            public function authenticate($inPassword)
            {
                if (! password_verify($inPassword, $this->userDetails['password_hash'])) {
                    return false;
                }

                return true;
            }



        //----------------------------------------------------------------------
        //-- Returns a user preference

            public function getPreference($inPreference)
            {
                if (! is_array($this->userPreferences)) {
                    return null;
                }

                if (! array_key_exists($inPreference, $this->userPreferences)) {
                    return null;
                }

                return $this->userPreferences[$inPreference];
            }



        //----------------------------------------------------------------------
        //-- Returns the value from the user array

            public function __get(string $inName)
            {

                $outValue = parent::__get($inName);

                if ($outValue !== NULL) {
                    return $outValue;
                }

                if (! is_string($inName)) {
                    return NULL;
                }

                switch($inName) {

                    case '':
                        return NULL;

                    case 'userID':
                        if (! $this->loggedIn) {
                            return 0;
                        }

                        if (! is_array($this->userDetails)) {
                            return 0;
                        }

                        if (! array_key_exists('id', $this->userDetails)) {
                            return 0;
                        }

                        return intval($this->userDetails['id']);

                    case 'userEmail':
                        if (! $this->loggedIn) {
                            return false;
                        }

                        if (! is_array($this->userDetails)) {
                            return false;
                        }

                        if (! array_key_exists('email', $this->userDetails)) {
                            return false;
                        }

                        return $this->userDetails['email'];
                }

                return NULL;
            }


        //----------------------------------------------------------------------
        //-- Returns the user details

            public function userDetails()
            {
                $details = $this->userDetails;
                unset($details['password_hash']);
                unset($details['password_reset_token']);
                return $details;
            }

    }



//---------------------------------------------------------------------------------
//-- Define Base Request Class
//---------------------------------------------------------------------------------

    class Request extends \Framework\Request
    {
        public static function init()
        {
            if (! parent::init()) {
                return false;
            }

            self::$user = new \App\User;

            return true;
        }
    }



//---------------------------------------------------------------------------------
//-- Define Base Page functions to used by multiple pages
//---------------------------------------------------------------------------------

    trait BasePage
    {
        protected $userTheme = false;

        public function buildHeadIncludes()
        {
            parent::buildHeadIncludes();

            $this->headIncludeSS(\Framework\cdnURL('vendor/bootstrap/css/bootstrap.min.css'));
            $this->headIncludeSS(\Framework\cdnURL('vendor/bootstrap-icons/bootstrap-icons.min.css'));

            $this->headIncludeSS(\Framework\cdnURL('Middleware/css/ula_style.css'));

            if ($this->userTheme != false) {
                $this->headIncludeSS(\Framework\cdnURL('Middleware/css/' . $this->userTheme . '.css'));
            }

            if (! \MiddleWare\isProduction()) {
                $this->headIncludeSS(\Framework\cdnURL('Middleware/css/ula_dev.css'));
            }

            $this->headIncludeSS(\Framework\cdnURL('Middleware/css/bootstrap_wcag_21_AA.css'));

            $this->headIncludeSS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.min.css'));
            $this->headIncludeSS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.theme.min.css'));

            $this->headIncludeJS(\Framework\cdnURL('vendor/jquery/jquery.min.js'));
            $this->headIncludeJS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.min.js'));
            $this->headIncludeJS(\Framework\cdnURL('vendor/popper/popper.js'));
            $this->headIncludeJS(\Framework\cdnURL('vendor/bootstrap/js/bootstrap.min.js'));
        }

        public function renderBodyNavigation()
        {
            include(APP_ROOT . 'app/instance/page-header.php');
        }

        public function renderBodyContent()
        {
            print '    <a href="#main-content" class="skip-to-main-content-link">Skip to main content</a>' . PHP_EOL;
            $this->renderBodyNavigation();
            print '    <div id="main-content" class="container-fluid"> ' . PHP_EOL;

            parent::renderBodyContent();

            print '    </div>' . PHP_EOL;
        }

    }


//---------------------------------------------------------------------------------
//-- Define Session Timeout Page functions to used by multiple pages
//---------------------------------------------------------------------------------

    trait PageSessionTimeout
    {
        public function renderPostBody()
        {
            print PHP_EOL;
            print '<dialog id="sessionTimeoutModal" class="modal-dialog" style="max-width: 50%; margin-top: 50px;">' . PHP_EOL;
            print '    <div class="modal-content">' . PHP_EOL;
            print '        <div class="modal-header">' . PHP_EOL;
            print '            <h5 class="modal-title" id="sessionTimeoutModalLabel">Session Timeout Notification</h5>' . PHP_EOL;
            print '            <button id="closeSessionBtn" type="button" class="btn-close" aria-label="Close"></button>' . PHP_EOL;
            print '        </div>' . PHP_EOL;
            print '        <div class="modal-body" style="padding: 15px;">' . PHP_EOL;
            print '            Your session timeout will expire in two minutes.  Press any key to extend your session.' . PHP_EOL;
            print '        </div>' . PHP_EOL;
            print '        <div class="modal-footer">' . PHP_EOL;
            print '            <button id="extendSessionBtn" type="button" class="btn btn-secondary">Extend Session</button>' . PHP_EOL;
            print '        </div>' . PHP_EOL;
            print '    </div>' . PHP_EOL;
            print '</dialog>' . PHP_EOL;
            print PHP_EOL;
            $this->footIncludeJS(\Framework\siteURL('assets/js/sessionTimeout.js'));
        }
    }


//---------------------------------------------------------------------------------
//-- Define functions to be used to load user preferences into pages
//---------------------------------------------------------------------------------

    trait PageLoadPreferences
    {
        protected function loadPreferences()
        {
            $this->userTheme = \App\Request::$user->getPreference('css_style');

            switch($this->userTheme) {
                case 'dark':
                    require_once(MIDDLEWARE_ROOT . 'lib/icons-dark.php');
                    if (file_exists(APP_ROOT . 'app/lib/icons-dark.php')) {
                        require_once(APP_ROOT . 'app/lib/icons-dark.php');
                    }
                    break;
                default:
                    require_once(MIDDLEWARE_ROOT . 'lib/icons-default.php');
                    if (file_exists(APP_ROOT . 'app/lib/icons-default.php')) {
                        require_once(APP_ROOT . 'app/lib/icons-default.php');
                    }
                    $this->userTheme = 'light';
            }

            $this->htmlTagExtra =  'data-bs-theme="' . $this->userTheme . '"';
        }
    }


//---------------------------------------------------------------------------------
//-- Define common page
//---------------------------------------------------------------------------------

    class Page extends \Framework\Page
    {
        use BasePage;
        use PageLoadPreferences;
        use PageSessionTimeout;

        public function __construct()
        {
            parent::__construct();
            $this->loadPreferences();
        }
    }


//---------------------------------------------------------------------------------
//-- Define common form
//---------------------------------------------------------------------------------

    class Form extends \Framework\Form
    {
        use BasePage;
        use PageLoadPreferences;
        use PageSessionTimeout;

        public function __construct()
        {
            parent::__construct();
            $this->loadPreferences();
        }
    }


//---------------------------------------------------------------------------------
//-- Define Login Base Page functions to used by multiple login pages
//---------------------------------------------------------------------------------

    trait LoginBasePage
    {
        protected $userTheme = false;

        public function buildHeadIncludes()
        {
            parent::buildHeadIncludes();

            $this->headIncludeSS(\Framework\cdnURL('vendor/bootstrap/css/bootstrap.min.css'));

            $this->headIncludeSS(\Framework\cdnURL('Middleware/css/ula_style.css'));

            if (! \MiddleWare\isProduction()) {
                $this->headIncludeSS(\Framework\cdnURL('Middleware/css/ula_dev.css'));
            }

            $this->headIncludeSS(\Framework\cdnURL('Middleware/css/bootstrap_wcag_21_AA.css'));

            $this->headIncludeSS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.min.css'));
            $this->headIncludeSS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.theme.min.css'));

            $this->headIncludeJS(\Framework\cdnURL('vendor/jquery/jquery.min.js'));
            $this->headIncludeJS(\Framework\cdnURL('vendor/jquery-ui/base/jquery-ui.min.js'));
            $this->headIncludeJS(\Framework\cdnURL('vendor/bootstrap/js/bootstrap.min.js'));
        }

        public function renderBodyNavigation()
        {
            include(APP_ROOT . 'app/instance/page-login-header.php');
        }

        public function renderBodyContent()
        {
            print '    <a href="#main-content" class="skip-to-main-content-link">Skip to main content</a>' . PHP_EOL;
            $this->renderBodyNavigation();
            print '    <div id="main-content" class="container-fluid">' . PHP_EOL;

            parent::renderBodyContent();

            print '    </div>' . PHP_EOL;
        }
    }


//---------------------------------------------------------------------------------
//-- Define login page
//---------------------------------------------------------------------------------

    class LoginPage extends \Framework\Page
    {
        use PageLoadPreferences;
        use LoginBasePage;

        public function __construct()
        {
            parent::__construct();
            $this->pageTitle = 'Login Page';
            $this->loadPreferences();
        }
    }


//---------------------------------------------------------------------------------
//-- Define login error page
//---------------------------------------------------------------------------------

    class LoginErrorPage extends LoginPage
    {
        protected $errMsg;

        public function __construct($inPageDirectory)
        {
            parent::__construct();
            $this->pageDirectory = $inPageDirectory;
            $this->pageTitle = 'Error';
            $this->errMsg = '';
            $this->loadPreferences();
        }

        public function setErrMsg($inErrMsg)
        {
            $this->errMsg = $inErrMsg;
        }

        protected function userHassAccess()
        {
            return true;
        }
    }


//---------------------------------------------------------------------------------
//-- Define login form
//---------------------------------------------------------------------------------

    class LoginForm extends \Framework\Form
    {
        use LoginBasePage;
        use PageLoadPreferences;

        public function __construct()
        {
            parent::__construct();
            $this->loadPreferences();
        }
    }



//---------------------------------------------------------------------------------
//-- Indicates if the system is production
//---------------------------------------------------------------------------------

    function isProduction()
    {
        if (! defined('APP_ENVIRONMENT')) {
            return false;
        }

        if (APP_ENVIRONMENT == 'prod') {
            return true;
        }

        return false;
    }


//---------------------------------------------------------------------------------
//-- Get Config value by key
//---------------------------------------------------------------------------------

    define('SETTING_TYPE_AMOUNT',   'A'); // float
    define('SETTING_TYPE_DATE',     'D');
    define('SETTING_TYPE_ENABLED',  'B');
    define('SETTING_TYPE_EMAIL',    'M');
    define('SETTING_TYPE_EMPL_ID',  'E');
    define('SETTING_TYPE_LOCATION', 'L');
    define('SETTING_TYPE_NUMBER',   'N'); // integer
    define('SETTING_TYPE_PHONE',    'P');
    define('SETTING_TYPE_STRING',   'S');
    define('SETTING_TYPE_USERID',   'U');

    function getConfigValue($inTag)
    {
        $query = 'SELECT value FROM ' . APP_MYSQL_DB . '.settings WHERE tag = :tag';

        $params = array(
            'tag' => $inTag
        );

        $record = \App\Request::$db->fetchRecord($query, $params);

        if ($record == false) {
            return false;
        }

        return $record['value'];
    }


//---------------------------------------------------------------------------------
//-- Function used to send email
//---------------------------------------------------------------------------------
   
    function mail(string $inEmailTo, string $inSubject = '', string $inMessage = '', array $inBCC = array())
    {
        //-- There must be a subject and a message

            if ($inSubject == '') {
                return false;
            }

            if ($inMessage == '') {
                return false;
            }


        //-- Find the address to send email as

            $applicationEmailFrom = \Middleware\getConfigValue('APPLICATION_EMAIL_FROM');
    
            if ($applicationEmailFrom === false) {
                return false;
            }
   
        //-- Build the beginning header array
 
            $headers = array();
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type:text/html;charset=UTF-8;';
            $headers[] = 'From: ' . $applicationEmailFrom;

        //-- Handle redirects, prod/dev, etc customization
    
            if (REDIRECT_ALL_EMAIL) {
                $inEmailTo = REDIRECT_EMAIL_TO;
            } else if (\Middleware\isProduction()) {
                if (count($inBCC) > 0) {
                    $headers[] = 'bcc: ' . implode(',', $inBCC);
                }
            } else {
                $inEmailTo = \App\Request::$user->userEmail;
                if ($inEmailTo === false) {
                    $inEmailTo = \Middleware\getConfigValue('APPLICATION_EMAIL_ADMIN');
                    if ($inEmailTo === false) {
                        $inEmailTo = REDIRECT_EMAIL_TO;
                    }
                }
            }

            if (! \Middleware\isProduction()) {
                $inSubject .= ' (dev)';
            }

        //-- Build header string

            $headerString = implode("\r\n", $headers);

        //-- Send the email 

            return \mail($inEmailTo, $inSubject, $inMessage, $headerString);
    }



//------------------------------------------------------------------------------
//-- Gets a list of active staff members
//------------------------------------------------------------------------------

    define('INDEX_BY_ID',       0);
    define('INDEX_BY_USER_ID',  1);

    function getActiveStaff($inIndexBy=0, $inExcludeList=false)
    {
        $checkExclude = false;

        if (is_array($inExcludeList)) {
            $checkExclude = true;
        }

        $staffList = new \MiddleWare\Table\StaffIdentityList;

        $staffList->query();

        $activeList = array();

        switch($inIndexBy) {
            // For public releases, ID and USER ID are the same
            case INDEX_BY_USER_ID:
            default:
                while($staffList->next()) {
                    if ($checkExclude) {
                        if (array_key_exists($staffList->id, $inExcludeList)) {
                            continue;
                        }
                    }
                    $activeList[ $staffList->id ] = $staffList->last_name . ', ' . $staffList->first_name;
                }
                break;
        }

        asort($activeList);

        return $activeList;
    }



//---------------------------------------------------------------------------------
//-- Define a Render class for common elements 
//---------------------------------------------------------------------------------

    define('ICON_EXTRA_STYLE_LARGER_BUTTON' , 'style="font-size: 2.5rem;"');
    define('ICON_EXTRA_STYLE' , 'style="font-size: 1.75rem;"');
    
    class Render
    {
        public static function textButton(string $inID, string $inHREF, string $inBtnClass, string $inText, bool $inPrint = true)
        {
            if (! str_starts_with('http', $inHREF)) {
                $inHREF = \Framework\siteURL($inHREF);
            }

            $output = '<a id="' . $inID .'" role="button" href="' . $inHREF . '"' . 
                    ' class="btn ' . $inBtnClass . '">' . $inText . '</a></span>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }


        public static function iconButton($inID, $inHREF, $inButtonClass, $inIconClass, $inText, $inIconExtra = false, bool $inPrint = true)
        {
            if (! str_starts_with($inHREF, 'http')) {
                $inHREF = \Framework\siteURL($inHREF);
            }

            $output  = '<a id="' . $inID . '" role="button" href="' . $inHREF . '"';
            $output .= ' class="' . $inButtonClass . '"';
            $output .= ' title="' . $inText . '"';
            $output .= '>';
            $output .= '<i class="' . $inIconClass . '"';
    
            if ($inIconExtra !== false) {
                if (is_array($inIconExtra)) {
                    $output .= ' ' . implode(' ', $inIconExtra);
                } else {
                    $output .= ' ' . $inIconExtra;
                }
            }
    
            $output .= '></i>';
            $output .= '<span class="visually-hidden">' . $inText . '</span>';
            $output .= '</a>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }


        public static function goBackButton(string $inHREF = '', bool $inPrint = true)
        {
            if ($inHREF == '') {
                $inHREF = \Framework\NavStack::getBackURL();
            }

            $output = self::iconButton(
                inID: 'goback',
                inHREF: $inHREF,
                inButtonClass: 'goback btn',
                inIconClass: ICON_GO_BACK,
                inText: 'Go Back Button',
                inIconExtra: ICON_EXTRA_STYLE_LARGER_BUTTON,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function submitButton(string $inButtonText, string $inBtnClass = 'btn-primary', string $inNameID = '', string $inValue = '', bool $inPrint = true, bool $inDisabled = false)
        {
            $output = '<button type="submit" class="btn ' . $inBtnClass . ' float-end"';

            if ($inNameID != '') {
                $output .= 'id="' . $inNameID . '" name="' . $inNameID . '" ';
            }

            if ($inValue != '') {
                $output .= 'value="' . $inValue . '"';
            }

            if ($inDisabled === true) {
                $output .= 'disabled ';
            }

            $output .= '>' . $inButtonText . '</button>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function cancelButton(string $inHREF, bool $inPrint = true)
        {
            if (! str_starts_with('http', $inHREF)) {
                $inHREF = \Framework\siteURL($inHREF);
            }

            $output = '<a href="' . $inHREF . '" role="button" class="btn btn-outline-dark">Cancel</a>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function loginButton(bool $inPrint = true)
        {
            $output  = '<span style="font-size: 25px;">Please click the button below to login.<BR>';
            $output .= '<a id="login" role="button" href="';
            $output .= \Framework\siteURL(APP_LOGIN_BUTTON_PATH);
            $output .= '" ';
            $output .= 'class="btn btn-primary">';
            $output .= 'Login</a></span>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function clearLoginButton(bool $inPrint = true)
        {
            $output = '<span style="font-size: 25px;">Please click the button below to login.<BR>';
            $output .= '<a id="login" role="button" href="';
            $output .= \Framework\siteURL(APP_CLEAR_LOGIN_BUTTON_PATH);
            $output .= '" ';
            $output .= 'class="btn btn-primary">';
            $output .= 'Login</a></span>';
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function homeButton(bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'home',
                inHREF: '',
                inButtonClass: 'btn',
                inIconClass: ICON_HOME . ' toolbar-icons',
                inText: 'Home Button',
                inIconExtra: ICON_EXTRA_STYLE_LARGER_BUTTON,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function logoutButton(bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'logout',
                inHREF: APP_LOGOUT_BUTTON_PATH,
                inButtonClass: 'btn',
                inIconClass: ICON_LOGOUT . ' toolbar-icons',
                inText: 'Logout Button',
                inIconExtra: ICON_EXTRA_STYLE_LARGER_BUTTON,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function searchButton(string $inHREF, string $inText, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'searchButton',
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_SEARCH,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
   
 
        public static function addButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'addButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_ITEM_ADD,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
  

        public static function editButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'editButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_EDIT,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function viewButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'viewButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_VIEW,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function trashButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'trashButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_TRASH,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function downloadButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'downloadButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_DOWNLOAD,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function upButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'upButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_UP,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function downButton(string $inHREF, string $inText, string $inExtraID, bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'downButton' . $inExtraID,
                inHREF: $inHREF,
                inButtonClass: 'btn',
                inIconClass: ICON_DOWN,
                inText: $inText,
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function printButton(bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'printButton',
                inHREF: '#print',
                inButtonClass: 'btn',
                inIconClass: ICON_PRINT,
                inText: 'Print Button',
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function exportButton(bool $inPrint = true)
        {
            $output = self::iconButton(
                inID: 'exportButton',
                inHREF: '#export',
                inButtonClass: 'btn',
                inIconClass: ICON_DOWNLOAD,
                inText: 'Export Button',
                inIconExtra: ICON_EXTRA_STYLE,
                inPrint: false);
    
            if ($inPrint === true) {
                print $output . PHP_EOL;
                return;
            }
    
            return $output;
        }
    
    
        public static function formField($inFormField, bool $inInsideForm = true)
        {
            if ($inFormField->hidden) {
                $inFormField->renderElement();
                print PHP_EOL;
                return;
            }
   
            $containerDivClasses = '';
 
            switch($inFormField->type) {
                case 'checkbox':
                    if ($inFormField->switch) {
                        $containerDivClasses = 'form-switch app-form-switch';
                    }

                    $labelClasses = array('form-check-label','app-form-check-label');
                    if ($inInsideForm === true) {
                        $labelClasses[] = 'col-sm-2';
                    }
                    if ($inFormField->switch) {
                        $inputClasses = array('form-check-input','app-form-check-input','app-form-switch');
                    } else {
                        $inputClasses = array('form-check-input','app-form-check-input');
                    }
                    break;
    
                case 'radio':
                    $labelClasses = array('form-check-label','app-form-check-label');
                    if ($inInsideForm === true) {
                        $labelClasses[] = 'col-sm-2';
                    }
                    $inputClasses = array('form-check-input');
                    $inFormField->buttonLabelClasses = array('form-label app-form-label app-form-radio-label');
                    break;
    
                case 'select':
                    $labelClasses = array('form-label','app-form-label');
                    if ($inInsideForm === true) {
                        $labelClasses[] = 'col-sm-2';
                    }
                    $inputClasses = array('form-select','autolen');
                    break;
   
                case 'color':
                    $labelClasses = array('form-label','app-form-label');
                    if ($inInsideForm === true) {
                        $labelClasses[] = 'col-sm-2';
                    }
                    $inputClasses = array('form-control','app-form-input-color');
                    break; 

                default:
                    $labelClasses = array('form-label','app-form-label');
                    if ($inInsideForm === true) {
                        $labelClasses[] = 'col-sm-2';
                    }
                    $inputClasses = array('form-control','autolen');
            }
    
            if ($inInsideForm === true) {
                print '<div class="row mb-3 ' . $containerDivClasses . '">';
                    $inFormField->renderLabel($labelClasses);
                    print '<div class="col-sm-10">';
                        $inFormField->renderElement($inputClasses);
                        $inFormField->renderErr('invalid-feedback');
                    print '</div>';
                print '</div>' . PHP_EOL;
            } else {
                print '<div class="float-start d-flex justify-content-start">';
                print '    <div class="d-flex align-items-center">';
                $inFormField->renderLabel($labelClasses);
                print ': &nbsp; ';
                print '    </div>';
                print '</div>';
                print '<div class="float-end d-flex justify-content-start">';
                print '    <div class="d-flex align-items-center">';
                $inFormField->renderElement($inputClasses);
                $inFormField->renderErr('invalid-feedback');
                print '    </div>';
                print '</div>';
                print PHP_EOL;
            }
    
        }
    
    }



//---------------------------------------------------------------------------------
//-- Defines and Functions for Locations Codes and Buildings
//---------------------------------------------------------------------------------

    define('SITE_LOCATION_OFFSITE', '000');

    class SiteLocations
    {
        use \Framework\VariableState;

        protected static $values = array(
            SITE_LOCATION_OFFSITE => array('text' => 'OFFSITE', 'selectable' => true, 'assignable' => true),
        );

        public static function parseBuilding($inLocation)
        {
            if (! is_string($inLocation)) {
                return $inLocation;
            }

            $i = strpos($inLocation,'-');

            if ($i === false) {
                return $inLocation;
            }

            return substr($inLocation,0,$i);
        }

        public static function buildingFromLocation($inLocation)
        {
            return self::getText(self::parseBuilding($inLocation));
        }

        public static function parseRoom($inLocation)
        {
            if (! is_string($inLocation)) {
                return $inLocation;
            }

            $i = strpos($inLocation,'-');

            if ($i === false) {
                return $inLocation;
            }

            return substr($inLocation,$i+1);
        }
    }


    define('TEXT_SITE_BUILDINGS', array(
        SITE_LOCATION_OFFSITE  => 'OFFSITE',
    ));


    class SiteBuildings
    {
        use \Framework\VariableState;
        protected static $values = array(
            SITE_LOCATION_OFFSITE  => array('text' => 'OFFSITE',                        'selectable' => true, 'assignable' => true),
        );
    }



//---------------------------------------------------------------------------------
//--Normalization Definitions
//---------------------------------------------------------------------------------

    class Normalize extends \Framework\Normalize
    {
        public static function staff_id($inID, $inEmptyOnUnfound=false)
        {
            if (is_int($inID)) {
                $inID = intval($inID);
            }
            if ($inID < 1) {
                if ($inEmptyOnUnfound) {
                    return '';
                }
                return 'unknown';
            }
            $staff = new \MiddleWare\Table\StaffIdentityRecord;
            if (! $staff->findByID($inID)) {
                return 'unknown';
            }
            return $staff->last_name . ', ' . $staff->first_name;
        }
   
    }




//---------------------------------------------------------------------------------
//-- Defines Table Log Types
//---------------------------------------------------------------------------------

    define('LOG_TABLE_MIDDLEWARE_ACL', 'ACL');
    define('LOG_TABLE_MIDDLEWARE_CONFIG', 'CFG');
    define('LOG_TABLE_MIDDLEWARE_USER_PREFERENCES', 'UP');


//---------------------------------------------------------------------------------
//-- Defines Change Log Types
//---------------------------------------------------------------------------------

    class ChangeLogTypes extends \Framework\ChangeLogTypes
    {
        use \Framework\VariableState;
    }


//------------------------------------------------------------------------------
//-- Class to manage the process of logging and viewing changes
//--   note: must be extended
//------------------------------------------------------------------------------

    class ChangeLog extends \Framework\ChangeLog
    {
        public function __construct()
        {
            $this->tableName = APP_MYSQL_DB . '.change_log';
        }
    }


