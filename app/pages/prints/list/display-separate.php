<?php
/**
 * app/pages/prints/list/display-separate.php
 *
 * Displays a page to view the list of projects that are in an active state
 * in a separated format
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

<div class="float-end" style="padding-bottom: 20px">
    <?php
        \App\Render::textButton(
            'combinedPrints',
            'prints/list?view=' . VIEW_PRINTS_COMBINED,
            'btn btn-primary',
            'Combined');
?>
</div>

<div class="clearfix"></div>

<?php foreach(array(PROJECT_PRINT_STATUS_PROBLEM, PROJECT_PRINT_STATUS_WAITING, PROJECT_PRINT_STATUS_PRINTING ) as $projectPrintStatus) { ?>

    <div class="d-flex"><h3><?= \App\ProjectPrintStatuses::getText($projectPrintStatus) ?></h3></div>
    <div>
        <table id="<?= $projectPrintStatus ?>Queue" class="table table-bordered display app-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 200px;">Project ID</th>
                    <th scope="col">File Name</th>
                    <th scope="col">File Size</th>
                    <th scope="col">Wait Ticket #</th>
                    <th scope="col">Print Ticket #</th>
                    <th scope="col">Created</th>
                    <?php
                        if ($projectPrintStatus == PROJECT_PRINT_STATUS_PROBLEM) {
                            print '                    <th scope="col">Action Needed</th>' . PHP_EOL;
                        }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    $project = new \App\Table\ProjectsRecord();
                    $projectFile = new \App\Table\ProjectFilesRecord();
                    $projectPrint = new \App\Table\ProjectPrintsRecord();
                    $projectPrintProblems = new \App\Table\ProjectPrintProblemsList;

                    $linePrinted = false;
                    foreach($this->listRecords as $pPrint) {

                        if ($pPrint['status'] != $projectPrintStatus) {
                            continue;
                        }

                        if ($projectPrintStatus == PROJECT_PRINT_STATUS_PROBLEM) {

                            $projectPrintProblems = new \App\Table\ProjectPrintProblemsList;

                            $projectPrintProblems->whereExpressions(array(
                                'print_id = :print_id',
                                'AND action_taken = :action_taken',
                            ));

                            $projectPrintProblems->orderBy('action_needed_date DESC');

                            $projectPrintProblems->limitRows(1);

                            $params = array(
                                'print_id' => $pPrint['id'],
                                'action_taken' => PROJECT_PRINT_ACTION_TAKEN_NONE,
                            );

                            $projectPrintProblems->query($params);

                            $projectPrintProblems->next();
                        }

                        $linePrinted = true;
                        print '            <tr>';
                        print '<td>';
                            print '<a href="';

                            switch($pPrint['status']) {
                                case PROJECT_PRINT_STATUS_PROBLEM:
                                    print \Framework\siteURL('prints/view?id=' . $pPrint['id'] . '&tab=problems');
                                    break;
                                default:
                                    print \Framework\siteURL('prints/view?id=' . $pPrint['id']);
                                    break;
                            }

                            print '">';
                            print \App\Normalize::projectID($pPrint['project_type'],$pPrint['project_year'],$pPrint['project_id']);
                            print '</a>';

                            if (array_key_exists($pPrint['project_id'], $this->noteCounts)) {
                                if ($this->noteCounts[$pPrint['project_id']] > 0) {
                                    print ' &nbsp; ';
                                    \App\Render::stickyButton(
                                        'viewProjectNotes' . $pPrint['project_id'],
                                        'projects/view?id=' . $pPrint['project_id'] . '&tab=notes',
                                        'View Project ' . $pPrint['project_id'] . ' Notes');
                                }
                            }

                        print '</td>';

                        print '<td>' . $pPrint['file_name'] . '</td>';

                        print '<td>' . $projectFile->normalize('file_size', $pPrint['file_size']) . '</td>';

                        print '<td>';
                            print $pPrint['wait_list_number'];
                            if ($pPrint['is_reprint'] == VALUE_YES) {
                                print ' (reprint)';
                            }
                        print '</td>';

                        print '<td>';
                            if ($pPrint['status'] == PROJECT_PRINT_STATUS_WAITING) {

                                if (array_key_exists($pPrint['project_id'], $this->filamentCounts)) {
                                    if ($this->filamentCounts[$pPrint['project_id']] > 0) {
                                        \App\Render::textButton(
                                            'assignJobTicket',
                                            'prints/jobticket?id=' . $pPrint['id'],
                                            'btn btn-primary',
                                            'Assign Job Ticket');
                                    } else {
                                        \App\Render::textButton(
                                            'needFilament',
                                            'projects/view?id=' . $pPrint['project_id'] . '&tab=filaments',
                                            'btn btn-primary',
                                            'Need Filament');
                                    }
                                }

                            } else {
                                if ($this->jobTicketURL === false) {
                                    print $pPrint['job_ticket_number'];
                                } else {
                                    print '<a href="' . $this->jobTicketURL . $pPrint['job_ticket_number'] . '" TARGET="fabapp">';
                                    print $pPrint['job_ticket_number'];
                                    print '</a>';
                                }
                            }
                        print '</td>';

                        print '<td>' . $projectPrint->normalize('created', $pPrint['created']) . '</td>';

                        if ($projectPrintStatus == PROJECT_PRINT_STATUS_PROBLEM) {
                            print '<td>';
                            if (! $projectPrintProblems->atEnd()) {
                                \App\Render::textButton(
                                    'actionNeeded',
                                    'prints/action/needed/' . $projectPrintProblems->action_needed . '?id=' . $projectPrintProblems->id,
                                    'btn btn-warning',
                                    $projectPrintProblems->normalize('action_needed'));
                            } else {
                                print 'Unknown';
                            }
                            print '</td>';
                        }

                        print "</tr>\n";
                    }

                    if (! $linePrinted) {
                        print '            <tr><td colspan=6 align=center>No prints</td></tr>';
                    }

            ?>
            </tbody>
        </table>
    </div>
    <BR><BR>
<?php } ?>

