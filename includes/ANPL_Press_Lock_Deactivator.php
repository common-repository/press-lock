<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://presslock.net
 * @since      1.0.0
 *
 * @package    press-lock
 * @subpackage press-lock/includes
 */

class ANPL_Press_Lock_Deactivator {

	/**
	 * Remove plugin dependencies
	 *
	 * Remove the press_lock_settings table
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        self::dropDB();
	}

	protected static function dropDB() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $wpdb->query($sql);
    }

}
