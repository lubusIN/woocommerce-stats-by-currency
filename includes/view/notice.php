<?php

/**
 * Notice output 
 * 
 * @package WSBC
 */

$adminurl = admin_url();
$pluginpage = admin_url('plugins.php');
?>

<style type="text/css">
    body {
        border-left: solid 5px #dc3232;
    }
    .button {
        line-height: 2.15384615;
        min-height: 30px;
        margin: 0;
        padding: 0 10px;
        border-width: 1px;
        white-space: nowrap;
    }

    .primary {
        background: #007cba;
        border-color: #007cba;
        color: #fff;
    }

    .primary:hover {
        background: #0071a1;
        border-color: #0071a1;
        color: #fff;
    }
</style>
<div class="wsbc-notice">
    <p>Sorry, This plugin requires <strong>WooCommerce</strong> and <strong>WooCommerce Price Based on Country</strong> plugins installed and activated to work.</p>
    <div>
        <?php printf( __( '<a class="button primary" href="%s">Install Plugins</a>', 'wsbc'), $pluginpage ); ?>
        <?php printf( __( '<a class="button" href="%s">Dashboard</a>', 'wsbc'), $adminurl ); ?>
    </div>
</div>