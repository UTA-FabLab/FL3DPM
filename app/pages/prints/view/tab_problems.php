<?php
/**
 * app/pages/projects/view/tab_problems.php
 *
 * Displays project problems tab content
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

<div id="problemsInfo"
    role="tabpanel"
    aria-labelledby="problems-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'problems' ? 'active' : '') ?>">

    <div class="app-div-table">
        <div class="row app-div-table-row-header">
            <div class="col-md-2">
                <b>Problem</b>
            </div>
            <div class="col-md-4">
                <b>Action Needed</b>
            </div>
            <div class="col-md-4">
                <b>Action Taken</b>
            </div>
            <div class="col-md-2">
                <b>Record</b>
            </div>
        </div>
        <?php while($this->projectPrintProblems->next()) { ?>
            <div class="row app-div-table-row-striped">
                <div class="col-md-2">
                    <b><?= $this->projectPrintProblems->normalize('problem') ?></b>
                </div>
                <div class="col-md-4">
                    <b><?= $this->projectPrintProblems->normalize('action_needed') ?></b>
                </div>
                <div class="col-md-4">
                    <?php
                        if ($this->projectPrintProblems->action_taken == PROJECT_PRINT_ACTION_TAKEN_NONE) {

                            \App\Render::textButton(
                                'resolve' . $this->projectPrintProblems->id,
                                'prints/action/needed/' . $this->projectPrintProblems->action_needed . '?id=' . $this->projectPrintProblems->id,
                                'btn btn-warning',
                                'Resolve');
                        } else {
                            print '<b>' . $this->projectPrintProblems->normalize('action_taken') . '</b>';
                        }
                    ?>
                </div>
                <div class="col-md-2">
                    <b><?= $this->projectPrintProblems->normalize('action_needed_date') ?></b>
                </div>
            </div>
        <?php } ?>
    </div>

</div> <!-- end problems tag -->


