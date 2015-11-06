<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 16.10.15
 * Time: 16:45
 */



if(!function_exists('hiweb')) {
    add_action('admin_notices', function () {
        echo '<div class="error">
        <p><b>hiWeb Core</b> : `hiWeb Core` not Installed / Activate...<br /><b><a href="plugin-install.php?tab=search&type=term&s=hiweb">Install this plugin from WP repository</a></b></p>
    </div>';
    });
    return;
}