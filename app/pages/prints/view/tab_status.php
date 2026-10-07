<?php
/**
 * app/pages/projects/view/tab_status.php
 *
 * Displays project print status tab content
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

<div id="statusInfo" 
    role="tabpanel" 
    aria-labelledby="status-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'status' ? 'active' : '') ?>">

    <div class="app-div-table">
        <div class="row app-div-table-row-striped">
            <div class="col-md-2 d-flex justify-content-end">
                Print ID:
            </div>
            <div class="col-md-10">
                <?= $this->projectPrint->id ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-2 d-flex justify-content-end">
                Status:
            </div>
            <div class="col-md-10">
                <?= $this->projectPrint->normalize('status') ?>
            </div>
        </div>

        <?php if ($this->projectPrint->job_ticket_number != '') { ?>
            <div class="row app-div-table-row-striped">
                <div class="col-md-2 d-flex justify-content-end">
                    Job Ticket #:
                </div>
                <div class="col-md-10">
                    <?php if ($this->jobTicketURL !== false) { ?>
                        <a href="<?= $this->jobTicketURL . $this->projectPrint->job_ticket_number ?>" target="fabapp"><?= $this->projectPrint->job_ticket_number ?></a>
                    <?php } ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="row app-div-table-row-striped">
                <div class="col-md-2 d-flex justify-content-end">
                    Wait List #:
                </div>
                <div class="col-md-10">
                    <?= $this->projectPrint->wait_list_number ?>
                </div>
            </div>
        <?php } ?>

        <div class="row app-div-table-row-striped">
            <div class="col-md-2 d-flex justify-content-end">
                Printer #:
            </div>
            <div class="col-md-10">
                <?= $this->projectPrint->normalize('printer_id') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-2 d-flex justify-content-end">
                Cancel Date:
            </div>
            <div class="col-md-10">
                <?= $this->project->normalize('cancel_date') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-2 d-flex justify-content-end">
                Category:
            </div>
            <div class="col-md-10">
                <?= $this->project->normalize('category') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-12 d-flex justify-content-end">
                <?php
                    if ($this->project->status == PROJECT_STATUS_ACTIVE) {
                        switch($this->projectPrint->status) {
                            case PROJECT_PRINT_STATUS_WAITING:
                                if ($this->projectFilaments->count() > 0) {
                                    \App\Render::textButton(
                                        'jobTicket',
                                        'prints/jobticket?id=' . $this->projectPrint->id,
                                        'btn btn-primary',
                                        'Job Ticket');
                                } else {
                                    \App\Render::textButton(
                                        'needFilament',
                                        'projects/view?id=' . $this->projectPrint->project_id . '&tab=filaments',
                                        'btn btn-primary',
                                        'Need Filaments');
                                }

                                print ' &nbsp; &nbsp; ';
                                \App\Render::textButton(
                                    'uploadFiles',
                                    'prints/upload?id=' . $this->projectPrint->id,
                                    'btn btn-primary',
                                    'Upload Files');

                                print ' &nbsp; &nbsp; ';
                                \App\Render::textButton(
                                    'unpaidPrint',
                                    'prints/unpaidprint?id=' . $this->projectPrint->id,
                                    'btn btn-warning',
                                    'Unpaid Print');
                                break;

                            case PROJECT_PRINT_STATUS_PRINTING:
                                \App\Render::textButton(
                                    'completePrintJob',
                                    'prints/complete?id=' . $this->projectPrint->id,
                                    'btn btn-primary',
                                    'Print Job Complete');

                                print ' &nbsp; &nbsp; ';
                                \App\Render::textButton(
                                    'printJobProblem',
                                    'prints/problem?id=' . $this->projectPrint->id,
                                    'btn btn-warning',
                                    'Problem');
                        }
                    }

                    print ' &nbsp; &nbsp; ';
                    \App\Render::textButton(
                        'viewProject',
                        'projects/view?id=' . $this->projectPrint->project_id,
                        'btn btn-primary',
                        'Project');
                ?>
            </div>
        </div>
    </div>

</div> <!-- end status tag -->

