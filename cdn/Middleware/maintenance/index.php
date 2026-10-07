<?php
/**
 * Middleware/maintenance/script.php
 *
 * Displays a "maintenance" page for the application
 *
 * @package Middleware
 *
 * @copyright Copyright (c) 2026, The University of Texas at Arlington
 * @license CETD-Attribution-NonCommercial-ShareAlike
 *
 * @version 2026.08.01.01
 */

declare(strict_types = 1);

$isProduction = false;

if (defined('APP_ENVIRONMENT')) {
    if (APP_ENVIRONMENT == 'prod') {
        $isProduction = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= APP_FULL_NAME ?></title>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?= CDN_URL . 'vendor/bootstrap/css/bootstrap.min.css' ?>" rel="stylesheet">

    <link rel="stylesheet" href="<?= CDN_URL . 'Middleware/css/ula_style.css' ?>">

    <?php if (! $isProduction) { ?>
        <link rel="stylesheet" href="<?= CDN_URL . 'Middleware/css/ula_dev.css' ?>">
    <?php } ?> 

    <link rel="stylesheet" href="<?= CDN_URL . 'vendor/jquery-ui/base/jquery-ui.min.css' ?>">
    <link rel="stylesheet" href="<?= CDN_URL . 'vendor/jquery-ui/base/jquery-ui.theme.min.css' ?>">

    <script src="<?= CDN_URL . 'vendor/jquery/jquery.min.js' ?>"></script>

    <script src="<?= CDN_URL . 'vendor/jquery-ui/base/jquery-ui.min.js' ?>"></script>

    <script src="<?= CDN_URL . 'vendor/bootstrap/js/bootstrap.min.js' ?>"></script>
</head>

<body>
    <header id="header" class="header" role="banner">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <img alt="Site logo" class="navbar-brand" src="<?= SITE_URL ?>assets/img/site-logo.png" />
                <h1 class="site-title"> <?= APP_FULL_NAME ?></h1>
                <div class="navbar-nav ms-auto">
                    <div class="text-end">
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <BR>

    <div class="container-fluid" style="padding-right:40px; padding-left:40px" role="region"> 
        <article>
            <h1>We&rsquo;ll be back soon!</h1>
            <div>
                <p>Sorry for the inconvenience. We&rsquo;re performing some maintenance at the moment.  We&rsquo;ll be back up shortly!</p>
            </div>
        </article>
    </div>

    <?php
        if (APP_ERRORS_ON) {
            if (array_key_exists('MAINTENANCE_MESSAGE', $_SESSION)) {
                print '<div class="container-fluid" style="padding-right:40px; padding-left:40px" role="region">' . PHP_EOL;
                print '            <p>' . $_SESSION['MAINTENANCE_MESSAGE'] . '</p>' . PHP_EOL;
                print '        </div>' . PHP_EOL;
            }
        }
    ?>

</body>
</html>
