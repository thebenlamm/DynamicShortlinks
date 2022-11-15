<?php
/*
Plugin Name: Dynamic Shortlinks
Description: Dynamic Shortlinks creates shortlinks based on parameters sent from an external API
Author: Ben Lamm
Version: 1.1.1
Author URI: https://www.ben-lamm.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

define('DYSL_SHORTLINKS_OPTION_NAME', 'dysl_short_links');
define('DYSL_SETTINGS_PAGE_TITLE', 'Dynamic Shortlinks');
define('DYSL_SETTINGS_MENU_LINK_NAME', 'Dynamic Shortlinks');
define('DYSL_SETTINGS_PAGE_SLUG', 'dynamic-shortlinks-settings-configuration');
define('DYSL_SHORTCODE_PREFIX', 'dysl_');

// error_reporting(E_ALL);

require_once plugin_dir_path(__FILE__) . 'includes/dysl-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/dysl-cron.php';