<?php
/**
 * app/pages/prints/list/display-combined.php
 *
 * Displays a page to view the list of projects that are in an active state
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

<div class="float-end" style="padding-bottom: 20px;">
    <div class="float-end" style="margin-left: 20px;">
        <?php
            \App\Render::textButton(
                'separatePrints',
                'prints/list?view=' . VIEW_PRINTS_SEPARATE,
                'btn btn-primary',
                'Separate');
        ?>
    </div>
    <div class="float-end">
        <div class="form-check form-switch d-flex align-items-center">
            <input class="form-check-input" type="checkbox" id="showAll" <?= ($this->showAll) ? 'checked' : '' ?>>
            <label class="form-check-label" for="showAll">&nbsp; Show All</label>
        </div>
    </div>
</div>
<div class="clearfix"></div>


<div class="d-flex justify-content-center"><h3>Project Prints</h3></div>

<div>
    <table id="printsList" class="table display app-table">
        <thead>
            <tr>
                <th scope="col" width="150px;">Project ID</th>
                <th scope="col">Status</th>
                <th scope="col">File Name</th>
                <th scope="col">File Size</th>
                <th scope="col">Wait Ticket #</th>
                <th scope="col">Print Ticket #</th>
                <th scope="col">Created</th>
                <th scope="col">Action Needed</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $project = new \App\Table\ProjectsRecord();
                $projectFile = new \App\Table\ProjectFilesRecord();
                $projectPrint = new \App\Table\ProjectPrintsRecord();

                foreach($this->listRecords as $pPrint) {
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
                                \App\Render::iconButton(
                                    'viewProjectNotes',
                                    'projects/view?id=' . $pPrint['project_id'] . '&tab=notes',
                                    'btn btn-primary',
                                    ICON_STICKY,
                                    'View Project Notes',
                                    array('title' => 'View Project Notes'));
// FIXIT
// font-size: 1.75rem
                            }
                        }

                    print '</td>';
                    print '<td>';
                    if ($pPrint['project_status'] != PROJECT_STATUS_ACTIVE) {
                        print $project->normalize('status', $pPrint['project_status']);
                    } else {
                        print $projectPrint->normalize('status', $pPrint['status']);
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
                    print '<td>';

                    if ($pPrint['status'] == PROJECT_PRINT_STATUS_PROBLEM) {

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

                        if (! $projectPrintProblems->atEnd()) {
                            \App\Render::textButton(
                                'actionNeeded',
                                'prints/action/needed/' . $projectPrintProblems->action_needed . '?id=' . $projectPrintProblems->id,
                                'btn btn-warning',
                                $projectPrintProblems->normalize('action_needed'));
                        } else {
                            print 'Unknown';
                        }
                    }

                    print '</td>';
                    print "</tr>\n";
                }
        ?>
        </tbody>
    </table>
</div>

