<?php
/**
 * app/pages/projects/view/tab_files.php
 *
 * Displays project files tab content
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

<div id="filesInfo" 
    role="tabpanel" 
    aria-labelledby="files-tab"
    class="tab-pane app-view-tab <?= ($this->currentTab == 'files' ? 'active' : '') ?>">

    <div class="app-view-tab-container">
        <div class="d-flex justify-content-center"><h3>G-code Files</h3></div>
        <div class="app-div-table">
            <div class="row app-div-table-row-header">
                <div class="col-md-4">
                    <b>File Name</b>
                </div>
                <div class="col-md-2">
                    <b>File Type</b>
                </div>
                <div class="col-md-2">
                    <b>File Size</b>
                </div>
                <div class="col-md-2">
                    <b>Uploaded</b>
                </div>
                <div class="col-md-1">
                    <b>Status</b>
                </div>
                <div class="col-md-1">
                    &nbsp;
                </div>
            </div>
            <?php
                $this->projectFiles->rewind();
                $gCodeFileDisplayed = false;
                while ($this->projectFiles->next()) {
                    if ($this->projectFiles->file_type != PROJECT_FILE_TYPE_GCODE) {
                        continue;
                    }
                    $gCodeFileDisplayed = true;
             ?>
                    <div class="row app-div-table-row-striped">
                        <div class="col-md-4">
                            <?= $this->projectFiles->file_name ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('file_type') ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('file_size') ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('uploaded') ?>
                        </div>
                        <div class="col-md-1">
                            <?= ($this->projectFiles->status == PROJECT_FILE_STATUS_ENABLED ? '<div class="d-flex justify-content-center app-view-tab-file-enabled">Current</div>' : '') ?>
                        </div>
                        <div class="col-md-1 d-flex justify-content-end">
                            <?php
                                $btnType = 'btn-secondary';
                                if ($this->projectFiles->status == PROJECT_FILE_STATUS_ENABLED) {
                                    $btnType = 'btn-primary';
                                }
                                \App\Render::downloadButton(
                                    'projects/file/download?id=' . $this->projectFiles->id,
                                    'Download File ' . $this->projectFiles->id,
                                    strval($this->projectFiles->id));
                            ?>
                        </div>
                    </div>
            <?php
                }

                if (! $gCodeFileDisplayed) {
                    print '<div class="row app-div-table-row-striped">';
                    print '<div class="col-md-12">';
                    print 'No G-code files located';
                    print '</div>';
                    print '</div>' . PHP_EOL;
                }
                
            ?>
        </div>

        <div class="d-flex justify-content-center" style="margin-top: 20px;"><h3>Other Files</h3></div>
        <div class="app-div-table">
            <div class="row app-div-table-row-header">
                <div class="col-md-4">
                    <b>File Name</b>
                </div>
                <div class="col-md-2">
                    <b>File Type</b>
                </div>
                <div class="col-md-2">
                    <b>File Size</b>
                </div>
                <div class="col-md-2">
                    <b>Uploaded</b>
                </div>
                <div class="col-md-2">
                    &nbsp;
                </div>
            </div>
            <?php
                $this->projectFiles->rewind();
                $otherFileDisplayed = false;
                while($this->projectFiles->next()) {
                    if ($this->projectFiles->file_type == PROJECT_FILE_TYPE_GCODE) {
                        continue;
                    }
                    $otherFileDisplayed = true;
             ?>
                    <div class="row app-div-table-row-striped">
                        <div class="col-md-4">
                            <?= $this->projectFiles->file_name ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('file_type') ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('file_size') ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->projectFiles->normalize('uploaded') ?>
                        </div>
                        <div class="col-md-2 d-flex justify-content-end">
                        <?php
                                \App\Render::downloadButton(
                                    'projects/file/download?id=' . $this->projectFiles->id,
                                    'Download File ' . $this->projectFiles->id,
                                    strval($this->projectFiles->id));
                        ?>
                        </div>
                    </div>
            <?php
                }

                if (! $otherFileDisplayed) {
                    print '<div class="row app-div-table-row-striped">';
                    print '<div class="col-md-12">';
                    print 'No files located';
                    print '</div>';
                    print '</div>' . PHP_EOL;
                }
                
            ?>
        </div>

    </div>

</div> <!-- end files tag -->

