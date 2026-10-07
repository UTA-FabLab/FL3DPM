<?php
/**
 * Middlware/settings/list/script.php
 *
 * Handles the process of displaying a list of application settings
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace MiddleWare;

include(MIDDLEWARE_ROOT . 'DataTable/script.php');

class SettingsList extends \MiddleWare\DataTable
{
    public function __construct()
    {
        $this->clearStack = true;

        parent::__construct();

        $this->pageTitle = 'Application Settings';
        $this->tableTitle = 'Application Settings';
        $this->exportFileName = 'settings.csv';
        self::footIncludeJS( \Framework\cdnURL('Middleware/js/settings-list.js') );
    }


    protected function userHasAccess()
    {
        if (\App\Request::$user->hasACL(array(APP_ACL_MANAGE_SETTINGS))) {
            return true;
        }
        return false;
    }


    protected function buildContent()
    {
        //-- Call parent

            parent::buildContent();

        //-- find the settings 

            $settings = new \MiddleWare\Table\SettingsList;

            if (defined('APP_INCLUDE_UTA_EXTENSIONS') && (APP_INCLUDE_UTA_EXTENSIONS === true)) {
                $settings->selectExpressions(
                    array_merge(
                        $settings->selectFieldList(inAsArray: true),
                        array(
                            'ei.last_name AS ei_last_name',
                            'ei.first_name AS ei_first_name',
                            'ui.last_name AS ui_last_name',
                            'ui.first_name AS ui_first_name',
                        )
                    )
                );

                $settings->joinClauses(array(
                    'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.staff_details AS esd ON sl.value = esd.emplid and sl.type = "E"',
                    'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . ' AS ei ON esd.user_id = ei.id',
                    'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . ' AS ui ON sl.value = ui.id AND sl.type = "U"',
                ));
            } else {
                $settings->selectExpressions(
                    array_merge(
                        $settings->selectFieldList(inAsArray: true),
                        array(
                            'ui.last_name AS ui_last_name',
                            'ui.first_name AS ui_first_name',
                        )
                    )
                );

                $settings->joinClauses(array(
                    'LEFT JOIN ' . APP_MYSQL_IDENTITY_DB . '.' . APP_MYSQL_IDENTITY_TABLE . ' AS ui ON sl.value = ui.id AND sl.type = "U"',
                ));
            }

            $settings->query();

            while($settings->next()) {
                if ($settings->editable != VALUE_YES) {
                    continue;
                }

                if ((! defined('APP_INCLUDE_UTA_EXTENSIONS')) || (APP_INCLUDE_UTA_EXTENSIONS !== true)) {
                    if ($settings->type == SETTING_TYPE_EMPL_ID) {
                        continue;
                    }
                    if ($settings->type == SETTING_TYPE_LOCATION) {
                        continue;
                    }
                }

                $record = $settings->record();

                if ($this->filter($record, array('tag_desc' => true, 'value' => true))) {
                    continue;
                }

                $this->records[] = $record;
            }

            usort($this->records, array('\MiddleWare\SettingsList','sortRecords'));

        return true;
    }


    protected function renderEndToolBar()
    {
        \App\Render::printButton();
        \App\Render::exportButton();
    }


    protected function getHeader()
    {
        $header = array(
            'tag_desc'  => array('title' => 'Setting', 'cellStyle' => ''),
            'value'     => array('title' => 'Value', 'cellStyle' => ''),
        );

        if ($this->withHeader === true) {
            $header['links'] = array('title' => '<span class="visually-hidden">Action</span>', 'cellStyle' => 'text-align: right');
        }

        return $header;
    }        


    protected function getRow()
    {
        $this->recordNdx++;

        if ($this->recordNdx >= count($this->records)) {
            return false;
        }

        $record = $this->records[$this->recordNdx];

        switch($record['type']) {
            case SETTING_TYPE_AMOUNT:
                $record['value'] = '$' . number_format(floatval($record['value']),2);
                break;

            case SETTING_TYPE_DATE:
                $record['value'] = date('m/d/Y', intval($record['value']));
                break;

            case SETTING_TYPE_EMPL_ID:
                if (defined('APP_INCLUDE_UTA_EXTENSIONS') && (APP_INCLUDE_UTA_EXTENSIONS === true)) {
                    $record['value'] = $record['ei_last_name'] . ', ' . $record['ei_first_name'];
                }
                break;

            case SETTING_TYPE_ENABLED:
                $record['value'] = ($record['value'] == VALUE_YES ? 'Yes' : 'No');
                break;

            case SETTING_TYPE_LOCATION:
                $record['value'] = \MiddleWare\SiteLocations::buildingFromLocation($record['value']) . ' ' . \MiddleWare\SiteLocations::parseRoom($record['value']);
                break;

            case SETTING_TYPE_USERID:
                $record['value'] = $record['ui_last_name'] . ', ' . $record['ui_first_name'];
                break;
        }


        if ($this->withHeader === true) {
            $record['links'] = '';

            switch($record['type']) {
                case SETTING_TYPE_AMOUNT:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/amount?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_DATE:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/date?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_EMAIL:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/email?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_EMPL_ID:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/emplid?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_ENABLED:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/enabled?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_LOCATION:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/location?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_NUMBER:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/number?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_PHONE:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/phone?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_STRING:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/string?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

                case SETTING_TYPE_USERID:
                    $record['links'] = 
                        \App\Render::iconButton(
                            'editButton' . $record['id'],
                            \Framework\siteURL('manage/settings/edit/userid?id=' . $record['id']), 
                            'btn',
                            ICON_EDIT,
                            'Edit Setting ' . $record['tag_desc'],
                            'style="font-size: 1.5em;"', inPrint: false);
                    break;

            }
        }

        return $record;
    }

    protected function sortRecords($a, $b)
    {
        return strcasecmp($a['tag_desc'],$b['tag_desc']);
    }

}


$page = new SettingsList();
$page->render();

