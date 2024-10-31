<?php

/**
 * Fired during plugin activation
 *
 * @link       https://presslock.net
 * @since      1.0.0
 *
 * @package    press-lock
 * @subpackage press-lock/includes
 */

class ANPL_Press_Lock_Activator {

	/**
	 * Create plugin database requirements
	 *
	 * Create the required table press_lock_settings
     * Compile the hashes for the existing selected files
     * Save the compiled hashes in the created table
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::createDB();
        self::saveFileHashes(self::fileHashes());
	}

	protected static function createDB() {
        global $wpdb;
        $version = get_option( 'press-lock', '1.0' );
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'press_lock_settings';

        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		setting varchar(250) DEFAULT 'setting' NOT NULL,
		value text NOT NULL,		
		UNIQUE KEY id (id),
		INDEX setting (setting)
	    ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    protected static function fileHashes() {
	    $fileHashes = [
            "wp-blog-header.php" => ABSPATH . 'wp-blog-header.php',
            "index.php" => ABSPATH . 'index.php',
            ".htaccess" => ABSPATH . '.htaccess',
            "wp-login.php" => ABSPATH . 'wp-login.php',
            "wp-load.php" => ABSPATH . 'wp-load.php'
        ];

	    foreach ($fileHashes as $key=>$value) {
	        $fileHashes[$key] = hash_file('sha1', $value );
        }

        return $fileHashes;
    }

    protected static function saveFileHashes(array $fileHashes) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';
        $wpdb->insert(
            $table_name,
            array(
                'setting' => 'file_hashes',
                'value' => json_encode($fileHashes)
            ),
            array( '%s','%s' )
        );
    }
}
