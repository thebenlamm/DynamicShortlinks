<?php
/**
 * @package Dynamic Shortlinks
 * @version 1.0.1
 */
/*
Plugin Name: Dynamic Shortlinks
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: This plugin dynamically creates shortlinks based on parameters sent from an external API
Author: Ben Lamm
Version: 1.0.1
Author URI: https://www.ben-lamm.com
*/

require_once plugin_dir_path(__FILE__) . 'includes/dysl-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/dysl-cron.php';