<?php
/**
 * app/pages/projects/view/tab_filaments.php
 *
 * Displays project filaments tab content
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

<div id="filamentsInfo"
    role="tabpanel"
    aria-labelledby="filaments-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'filaments' ? 'active' : '') ?>">

    <div class="app-div-table app-view-tab">
        <div class="row app-div-table-row-header">
            <div class="col-md-1">
                <b>Order</b>
            </div>
            <div class="col-md-11">
                <b>Filament</b>
            </div>
        </div>
        <?php $lastFilament = $this->projectFilaments->count(); ?>
        <?php while($this->projectFilaments->next()) { ?>
            <div class="row app-div-table-row-striped">
                <div class="col-md-1">
                    <?= $this->projectFilaments->filament_order ?>
                </div>
                <div class="col-md-11">
                    <?= $this->projectFilaments->name ?>
                </div>
            </div>
        <?php } ?>
    </div>

</div> <!-- end filaments tag -->

