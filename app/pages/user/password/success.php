<?php
/**
 * app/pages/user/password/success.php
 *
 * Displays a form to indicate that password change was successful
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

namespace Middleware;
?>

<div class="app-form-spacing">
    <div class="row" role="region" aria-label="<?= $this->formTitle ?>">
        <div class="col-6 offset-3">
            <h2><?= $this->formTitle ?></h2>
            <div class="form-shadow-box">
                <p>You password was updated successfully. </p>
            </div>
        </div>
    </div>
</div>

