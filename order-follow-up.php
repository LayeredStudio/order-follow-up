<?php
/*
Plugin Name: Order Follow Up for WooCommerce
Plugin URI: https://layered.studio
Description: Send customers follow up emails (to review or share product) after completed orders.
Version: 0.1
Text Domain: layered
Author: Layered
Author URI: https://layered.studio
License: GPL2
*/

/*  Copyright 2018 Layered (email: hello@layered.studio)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include 'vendor/autoload.php';

define('ORDER_FOLLOW_UP_TEMPLATES', plugin_dir_path(__FILE__) . 'templates/');


// Start the plugin
add_action('plugins_loaded', 'Layered\OrderFollowUp\Emails::start');

// Plugin maintenance tasks
register_activation_hook(__FILE__, 'Layered\OrderFollowUp\Emails::onActivation');
register_deactivation_hook(__FILE__, 'Layered\OrderFollowUp\Emails::onDeactivation');
