<?php

/**
 * Plugin Name:       PressLock
 * Plugin URI:        https://presslock.net
 * Description:       PressLock makes it easy to incorporate the most effective security measures on your website, with only a few clicks. SSL, SMTP, WP core file monitoring, custom WP core links & more.
 * Version:           1.0.0
 * Author:            PressLock
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       press-lock
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'ANPL_PRESS_LOCK_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/ANPL_Press_Lock_Activator.php
 */
function ANPL_activate_press_lock() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/ANPL_Press_Lock_Activator.php';
    ANPL_Press_Lock_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/ANPL_Press_Lock_Deactivator.php
 */
function ANPL_deactivate_press_lock() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/ANPL_Press_Lock_Deactivator.php';
	ANPL_Press_Lock_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'ANPL_activate_press_lock' );
register_deactivation_hook( __FILE__, 'ANPL_deactivate_press_lock' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/ANPL_Press_Lock.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function ANPL_run_press_lock() {

	$plugin = new ANPL_Press_Lock();
	$plugin->run();

}
ANPL_run_press_lock();
