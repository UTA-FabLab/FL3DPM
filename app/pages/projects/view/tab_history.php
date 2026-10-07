<?php
/**
 * app/pages/projects/view/tab_history.php
 *
 * Displays the history tab content
 *
 * @package App
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);
?>

<div id="historyInfo" 
    role="tabpanel" 
    aria-labelledby="history-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'history' ? 'active' : '') ?>">

<?php
    $changeLog = new \Framework\ChangeLog;

    print '    <div class="card border-bottom" style="padding: 0px; margin: 0px;">' . PHP_EOL;
    print '        <div class="card-body" style="padding: 0px; margin: 0px;">' . PHP_EOL;
    print '            <table class="table table-striped" style="padding: 0px; margin: 0px;">' . PHP_EOL;
    print '                <caption><span class="visually-hidden">Staff Member Edit History</span></caption>' . PHP_EOL;
    print '                <tr>' . PHP_EOL;
    print '                    <th scope="col" style="border: none; text-align: left; width: 20%;">Date</th>' . PHP_EOL;
    print '                    <th scope="col" style="border: none; text-align: left; width: 30%;">By</th>' . PHP_EOL;
    print '                    <th scope="col" style="border: none; text-align: left; width: 20%;">Change Type</th>' . PHP_EOL;
    print '                    <th scope="col" style="border: none; text-align: left; width: 10%;">Update</th>' . PHP_EOL;
    print '                </tr>' . PHP_EOL;
    print '            </table>' . PHP_EOL;
    print '        </div>' . PHP_EOL;
    print '    </div>' . PHP_EOL;


    $historyPrinted = false;

    foreach ($this->projectHistory as $record) {

        $historyPrinted = true;

        print '    <div class="card border-bottom" style="padding: 0px; margin: 0px;">' . PHP_EOL;
        print '        <div class="card-body" style="padding: 0px; margin: 0px;">' . PHP_EOL;
        print '            <table class="table" style="padding: 0px; margin: 0px;">' . PHP_EOL;
        print '                <caption><span class="visually-hidden">Staff Member History Detail</span></caption>' . PHP_EOL;
        print '                <tr>' . PHP_EOL;
        print '                    <td style="border: none; width: 20%;">' . date('Y-m-d H:i:s',intval($record['timestamp'])) . '</td>' . PHP_EOL;
        print '                    <td style="border: none; width: 30%;">' . $record['last_name'] . ', ' . $record['first_name'] . '</td>' . PHP_EOL;
        print '                    <td style="border: none; width: 20%;">' . \Framework\ChangeLogtypes::getText($record['log_type']) . '</td>' . PHP_EOL;
        print '                    <td style="border: none; width: 10%;"><button data-bs-toggle="collapse" data-bs-target="#record' . $record['id'] . '">View</button></th>' . PHP_EOL;
        print '                </tr>' . PHP_EOL;
        print '            </table>' . PHP_EOL;

        print '             <div id="record' . $record['id'] . '" class="collapse">' . PHP_EOL;
        print '                 <div style="padding: 20px;">' . PHP_EOL;
        $changeLog->displayLogEntry($record['log_data']);
        print '                 </div>' . PHP_EOL;
        print '             </div>' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
    }

    if (! $historyPrinted) {
        print '    <div class="card border-bottom" style="padding: 0px; margin: 0px;">' . PHP_EOL;
        print '        <div class="card-body" style="padding: 20px; margin: 0px;">' . PHP_EOL;
        print '         No history records' . PHP_EOL;
        print '        </div>' . PHP_EOL;
        print '    </div>' . PHP_EOL;
    }

    print '</table>' . "\n";
?>
</div> <!-- history -->

