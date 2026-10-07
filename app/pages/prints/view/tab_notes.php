<?php
/**
 * app/pages/projects/view/tab_notes.php
 *
 * Displays project notes tab content
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


<div id="notesInfo" 
    role="tabpanel" 
    aria-labelledby="notes-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'notes' ? 'active' : '') ?>">

    <div class="app-div-table">

        <div class="float-end" class="app-view-tab-icon">
            <?php
                if ($this->project->canEditProject()) {
                    \App\Render::addButton(
                        'projects/note?id=' . $this->project->id,
                        'Add Note',
                        '');
                }
            ?>
        </div>

        <div class="clearfix" style="margin-bottom: 20px;"></div>

        <?php while ($this->projectNotes->next()) { ?>

            <div class="card app-view-note-card">
                <div class="card-header">
                    <?= $this->projectNotes->normalize('posted') ?> by <?= $this->projectNotes->last_name . ', ' . $this->projectNotes->first_name ?>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= $this->projectNotes->note ?> </p>
                </div>
            </div>
        <?php } ?>

        <?php if ($this->projectNotes->count() == 0) { ?>
            <h6 class="card-title" style="margin: 30px;">No notes have been added to this project</h6>
        <?php } ?>

    </div>

</div> <!-- end notes tag -->

