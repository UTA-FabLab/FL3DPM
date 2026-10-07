<?php
/**
 * Middleware/DataTable/script.php
 *
 * Class to display data tables with print and export capabilities
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

define('LIST_OUTPUT_MODE_DISPLAY', 'display');
define('LIST_OUTPUT_MODE_PRINT',   'print');
define('LIST_OUTPUT_MODE_CSV',     'csv');


class DataTable extends \App\Page
{
    protected $tableTitle;

    protected $records;
    protected $recordNdx;

    protected $outputMode;
    protected $withHeader;
    protected $exportFileName;
    protected $clearStack = true;
    protected $pushStack = true;
    protected $filterRecords = false;
    protected $searchPieces = array();
    protected $tableID = 'dataTable';

    public function __construct()
    {
        parent::__construct();

        $this->pageDirectory = MIDDLEWARE_ROOT . 'DataTable/';

        $this->tableTitle = '';

        $this->records = array();
        $this->recordNdx = -1;

        $this->outputMode = LIST_OUTPUT_MODE_DISPLAY;
        $this->withHeader = true;
        $this->exportFileName = 'export.csv';

        if ($this->pushStack === true) {
            \Framework\NavStack::push($this->clearStack);
        }
    }


    public function ndxRewind()
    {
        $this->recordNdx = -1;
    }


    public function exportCSV()
    {
        //-- send the export headers

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $this->exportFileName . '";');
  
            $out = fopen('php://output', 'w');

        //-- output the header row

            $ndx = 0;
            $header = array();

            foreach($this->getHeader() as $fieldName => $headerConfig) {
                $header[$ndx] = $headerConfig['title'];
                $ndx++;
            }

            fputcsv($out, $header);

        //-- output the detail rows

            $this->ndxRewind();   
 
            while($record = $this->getRow()) {
                $ndx = 0;
                $data = array();

                foreach($this->getHeader() as $fieldName => $headerConfig) {
                    $data[$ndx] = $record[$fieldName];
                    $ndx++;
                }

                fputcsv($out, $data);
            }

        //-- close the output handle

            fclose($out);
    }


    //-- child class should implement function to return array:
    //-- array('fieldname' => array('title' => 'title', 'cellStyle' => 'css style'))
    protected function getHeader()
    {
        return array();
    }


    //-- can be extended by child class to return modified record
    protected function getRow()
    {
        $this->recordNdx++;

        if ($this->recordNdx >= count($this->records)) {
            return false;
        }

        return $this->records[$this->recordNdx];
    }


    //-- can be overwritten by child class to render a toolbar

    protected function renderStartToolBar()
    {
    }

    protected function renderEndToolBar()
    {
    }


    //-- true is returned if record is being filtered out
    protected function filter($inRecord, $inSearchFields = array())
    {
        if ($this->filterRecords === false) {
            return false;
        }

        if (count($this->searchPieces) == 0) {
            return false;
        }

        foreach($inRecord as $ndx => $value) {
            if (! array_key_exists($ndx, $inSearchFields)) {
                continue;
            }
            foreach($this->searchPieces as $term) {
                if (stripos(strval($value),$term) !== false) {
                    return false;
                }
            }
        }
        return true;
    }


    protected function collectFilter()
    {
        $this->filterRecords = true;

        $search = \App\Request::getQP('search');

        if ($search === false) {
            $search = '';
        }

        $search = urldecode($search);

        $this->searchPieces = explode(' ', $search);

        foreach($this->searchPieces as $ndx => $value) {
            $value = trim($value);
            if (strlen($value) < 2 ) {
                unset($this->searchPieces[$ndx]);
            } else {
                $this->searchPieces[$ndx] = $value;
            }
        }
    }


    public function renderPage()
    {
        $this->outputMode = \App\Request::getQP('om');

        switch($this->outputMode) {

            case LIST_OUTPUT_MODE_CSV:
                $this->withHeader = false;
                $this->collectFilter();
                $this->buildContent();
                $this->exportCSV();
                exit;
                break;

            case LIST_OUTPUT_MODE_PRINT:
                $this->withHeader = false;
                $this->collectFilter();
                define('WITHOUT_HEADER', true);
                break;

            case LIST_OUTPUT_MODE_DISPLAY:
            default:
                self::headIncludeSS( \Framework\cdnURL('vendor/DataTables/datatables.css') );
                self::footIncludeJS( \Framework\cdnURL('vendor/DataTables/datatables.min.js') );
                break;
        }

        parent::renderPage(); 
    }
}


