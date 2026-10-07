<?php
/**
 * Middleware/lib/Tables.php
 *
 * Contains the tables classes specific to applications
 *
 * @package MiddleWare
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare\Table;


//---------------------------------------------------------------------------------
//-- ACL Table Interface
//---------------------------------------------------------------------------------

    class ACLFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'            => 'ACL ID',
            'user_id'       => 'User ID',
            'tag'           => 'ACL Tag',
        );
    }

    class ACLTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'acl';
            $this->asName = 'acl';
            $this->indexField = 'id';
            $this->relatedField = 'user_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_MIDDLEWARE_ACL;
        }

        public function getFieldNames()
        {
            return ACLFieldNames::getKeys();
        }

        public function fetchSQL()
        {
            $this->selectExpressions = array(
                $this->asName . '.*',
                'i.last_name',
                'i.first_name',
                'i.email',
            );

            $this->joinClauses = array(
                'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . ' AS i ON ' . $this->asName . '.user_id = i.id'
            );

            return parent::fetchSQL();
        }
    }

    class ACLRecord extends ACLTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
            $this->deleteAllowed = true;
        }
    }

    class ACLList extends ACLTable
    {
        use \Framework\TableListInterface;
    }



//---------------------------------------------------------------------------------
//-- Settings Table Interface
//---------------------------------------------------------------------------------

    class SettingsFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'            => 'Setting ID',
            'tag'           => 'Setting Tag',
            'type'          => 'Setting Type',
            'tag_desc'      => 'Setting Description',
            'value'         => 'Setting Value',
            'editable'      => 'Editing',
        );
    }


    class SettingsTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'settings';
            $this->asName = 'sl';
            $this->indexField = 'id';
            $this->relatedField = 'id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_MIDDLEWARE_CONFIG;
        }

        public function getFieldNames()
        {
            return SettingsFieldNames::getKeys();
        }
    }

    class SettingsRecord extends SettingsTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->updateAllowed = true;
        }
    }

    class SettingsList extends SettingsTable
    {
        use \Framework\TableListInterface;
    }



//------------------------------------------------------------------------------
//-- User Preferences Table Interface
//------------------------------------------------------------------------------

    class UserPreferencesFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'user_id'   => 'User ID',
            'css_style' => 'CSS Style',
        );
    }

    class UserPreferencesTable extends \Framework\TableInterface
    {
        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_DB;
            $this->tableName = 'user_preferences';
            $this->asName = 'up';
            $this->indexField = 'user_id';
            $this->relatedField = 'user_id';
            $this->changeLog = new \App\ChangeLog;
            $this->logTableTag = LOG_TABLE_MIDDLEWARE_USER_PREFERENCES;
        }

        public function getFieldNames()
        {
            return UserPreferencesFieldNames::getKeys();
        }
    }

    class UserPreferencesList extends UserPreferencesTable
    {
        use \Framework\TableListInterface;
    }

    class UserPreferencesRecord extends UserPreferencesTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }
    }



//------------------------------------------------------------------------------
//-- Staff Identity Table Interface
//------------------------------------------------------------------------------

    trait StaffIdentityNormalize
    {
        public function fullname()
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return $record['last_name'] . ', ' . $record['first_name'];
        }  
    }


    class StaffIdentityFieldNames
    {
        use \Framework\FieldNames;
        protected static $fieldNames = array(
            'id'                   => 'Identity ID',
            'email'                => 'Email',
            'first_name'           => 'First Name',
            'last_name'            => 'Last Name',
            'password_hash'        => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'enabled'              => 'Enabled',
            'last_updated_on'      => 'Last Updated On',
        );
    }

    class StaffIdentityTable extends \Framework\TableInterface
    {
        use StaffIdentityNormalize;

        public function __construct()
        {
            parent::__construct();
            $this->dbName = APP_MYSQL_IDENTITY_DB;
            $this->tableName = APP_MYSQL_IDENTITY_TABLE;
            $this->asName = 'ci';
            $this->indexField = 'id';
            $this->relatedField = 'id';
        }

        public function getFieldNames()
        {
            return StaffIdentityFieldNames::getKeys();
        }

        protected function enabledNormalize($inValue = false)
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return \Framework\Normalize::yes_no(($inValue == false) ? $record['enabled'] : $inValue);
        }

        protected function last_updated_onNormalize($inValue = false)
        {
            $record = ($this->record === NULL ? $this->record() : $this->record);
            return \MiddleWare\Normalize::dateYMD(($inValue == false) ? $record['last_updated_on'] : $inValue);
        }
    }

    class StaffIdentityList extends StaffIdentityTable
    {
        use \Framework\TableListInterface;
    }

    class StaffIdentityRecord extends StaffIdentityTable
    {
        use \Framework\TableRecordInterface;

        public function __construct()
        {
            parent::__construct();
            $this->insertAllowed = true;
            $this->updateAllowed = true;
        }

        public function findByEmail($inEmail)
        {
            $this->whereExpressions[] = $this->asName . '.email = :email';
            return $this->findRecord($this->fetchSQL(), array('email' => $inEmail));
        }

        public function findByPasswordResetToken($inToken)
        {
            $this->whereExpressions[] = $this->asName . '.password_reset_token LIKE :token';
            return $this->findRecord($this->fetchSQL(), array('token' => '%:' . $inToken));
        }
    }


