<?php
/**
* Plugin Name:  LOOPIS Mailer
* Plugin URI:   https://github.com/LOOPIS-app/loopis-mailer
* Description:  Plugin for configuring mail and mail templates in LOOPIS.app
* Version:      0.01
* Author:       The Develoopers
* Author URI:   https://loopis.org
* License:      GPL-3.0-or-later
* License URI:  https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain:  loopis-mailer
*/

/*
 * Copyright (C) 2026 LOOPIS
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Define plugin version
define('LOOPIS_USERS_VERSION', '0.01');
// Define paths
define('LOOPIS_MAILER_DIR', plugin_dir_path(__FILE__));     // Server-side path to /wp-content/plugins/loopis-config/
define('LOOPIS_MAILER_URI', plugin_dir_url(__FILE__));      // Client-side path to https://site.com/wp-content/plugins/loopis-config/
// Fetches the mailing 
function loopis_get_mail(){
    $absolute_path = LOOPIS_MAILER_DIR . '/includes/functions';
    if (is_dir($absolute_path)) {
        foreach (glob($absolute_path . '/*.php') as $file) {
            include_once $file;
        }
    } else {
        error_log("LOOPIS mailer failed to include folder: {$folder_name} ({$base_theme_dir})");
    }
    //Templates
    include_once  LOOPIS_MAILER_DIR . '/includes/templates/loopis-mail-footer.php';
    include_once  LOOPIS_MAILER_DIR . '/includes/templates/loopis-mail-template.php';
    include_once  LOOPIS_MAILER_DIR . '/includes/templates/loopis-mail-headers.php';
}
