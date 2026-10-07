<?php
/**
 * app/pages/projects/view/tab_status.php
 *
 * Displays project status tab content
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

    <div class="float-end" class="app-view-tab-icon">
        <?php
            if ($this->project->canEditProject()) {
                \App\Render::editButton(
                    'projects/edit?id=' . $this->project->id,
                    'Edit Project',
                    '');
            }
        ?>
    </div>
    <div class="clearfix"></div>

    <div class="app-div-table">

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Status:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('status') ?>
                <?php
                    switch($this->project->status) {
                        case PROJECT_STATUS_ACTIVE:
                            print ' - ' . $this->projectPrints->normalize('status',$this->project->activePrintStatus());
                            break;

                        case PROJECT_STATUS_CLOSED:
                            $cancelReason = $this->project->normalize('cancel_reason');
                            if ($cancelReason != 'Undefined') {
                                print ' - ' . $cancelReason;
                            }
                            break;
                    }
                ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                College:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('college_id') ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                <?= APP_UNIVERSITY_SHORT_NAME ?> ID:
            </div>
            <div class="col-md-11">
                <span id="customer_id_hidden">******** <span id="customer_id" style="display: none"><?= $this->project->customer_id ?></span></span>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Email:
            </div>
            <div class="col-md-11">
                <?= $this->project->email ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Last Name:
            </div>
            <div class="col-md-11">
                <?= $this->project->last_name ?>
            </div>
        </div>
        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                First Name:
            </div>
            <div class="col-md-11">
                <?= $this->project->first_name ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Created:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('created') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Cancel Date:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('cancel_date') ?>
            </div>
        </div>

        <div class="row app-div-table-row-striped">
            <div class="col-md-1 d-flex justify-content-end">
                Category:
            </div>
            <div class="col-md-11">
                <?= $this->project->normalize('category') ?>
            </div>
        </div>

        <div class="row app-view-tab-bottom-button-bar">
            <div class="col-md-12 d-flex justify-content-end">
                <?php
                    if ($this->project->status == PROJECT_STATUS_ACTIVE) {
                        switch($this->project->activePrintStatus()) {
                            case PROJECT_PRINT_STATUS_PRINTING:
                                \App\Render::textButton(
                                    'activePrint',
                                    'prints/view?id=' . $this->project->activePrintID(),
                                    'btn btn-primary',
                                    'Active Print');
                                break;

                            case PROJECT_PRINT_STATUS_WAITING:
                                if ($this->projectFilaments->count() > 0) {
                                    \App\Render::textButton(
                                        'jobTicket',
                                        'prints/jobticket?id=' . $this->project->activePrintID(),
                                        'btn btn-primary',
                                        'Job Ticket');
                                } else {
                                    \App\Render::textButton(
                                        'needFilament',
                                        'projects/view?id=' . $this->project->id . '&tab=filaments',
                                        'btn btn-primary',
                                        'Need Filament');
                                }
                                break;
                        }

                        if (\App\Request::$user->hasACL(array(APP_ACL_FULL_ACCESS, APP_ACL_STUDENT_LEAD))) {
                            print '&nbsp; &nbsp; ';
                            \App\Render::textButton(
                                'cancelProject',
                                'projects/cancel?id=' . $this->project->id,
                                'btn btn-warning',
                                'Cancel Project');
                        }
                    }
                ?>
            </div>
        </div>

    </div>

</div> <!-- end status tag -->

