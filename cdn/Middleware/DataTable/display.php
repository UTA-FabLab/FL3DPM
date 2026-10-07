<?php
/**
 * Middleware/DataTable/display.php
 *
 * Displays a standardized data table 
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
?>

<?php if ($this->withHeader === true) { ?>
    <div style="padding-bottom: 10px;">
        <div class="float-start d-flex justify-content-start">
            <div class="d-flex align-items-center">
                <?php $this->renderStartToolBar(); ?>
            </div>
        </div>
        <div class="float-end d-flex justify-content-end">
            <div class="d-flex align-items-center">
                <?php $this->renderEndToolBar(); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
<?php } ?>

<div class="d-flex justify-content-center" style="padding-bottom: 20px">
    <h1><?= $this->tableTitle ?></h1>
</div>

<div>
    <table id="<?= $this->tableID ?>" class="table table-striped display app-datatable" style="width:100%">
        <thead>
            <tr>
            <?php
                foreach($this->getHeader() as $fieldName => $headerConfig) {
                    print '<th scope="col"';

                    if ($headerConfig['cellStyle'] != '') {
                        print ' style="' . $headerConfig['cellStyle'] . '"'; 
                    }

                    print '>';
                    print $headerConfig['title'];
                    print '</th>' . PHP_EOL;
                }
            ?>
            </tr>
        </thead>
        <tbody>
            <?php while($record = $this->getRow()) { ?>
                <tr>
                    <?php
                        foreach($this->getHeader() as $fieldName => $headerConfig) {
                            print '<td';

                            if ($headerConfig['cellStyle'] != '') {
                                print ' style="' . $headerConfig['cellStyle'] . '"'; 
                            }

                            print '>';
                            print $record[$fieldName];
                            print '</td>' . PHP_EOL;
                        }
                    ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

