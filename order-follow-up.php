<?php
/*
Plugin Name: Order Follow Up for WooCommerce
Plugin URI: https://layered.studio
Description: Engage with your customers through email after a completed order or approved product review
Version: 0.1
Text Domain: layered
Author: Layered
Author URI: https://layered.studio
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

include 'vendor/autoload.php';

define('ORDER_FOLLOW_UP_TEMPLATES', plugin_dir_path(__FILE__) . 'templates/');


// Start the plugin
add_action('plugins_loaded', 'Layered\OrderFollowUp\Emails::start');


// Maintenance tasks
register_activation_hook(__FILE__, 'Layered\OrderFollowUp\Emails::onActivation');
register_deactivation_hook(__FILE__, 'Layered\OrderFollowUp\Emails::onDeactivation');
