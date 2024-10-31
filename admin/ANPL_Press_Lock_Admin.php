<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://presslock.net
 * @since      1.0.0
 *
 * @package    press-lock
 * @subpackage press-lock/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    press-lock
 * @subpackage press-lock/admin
 * @author     Stefan Astefanesei <stefan.astefanesei@presslock.net>
 */
class ANPL_Press_Lock_Admin
{
    /**
     * Messages notification alert
     */
    const NOTIFICATION_MESSAGES = [
        'ssl' => [
            'success' => 'The changes SSL have been saved successfully!',
            'info' => 'You have not made any changes to SSL!',
            'error' => 'Error saving data SSL!'
        ],
        'smtp' => [
            'success' => 'The changes SMTP have been saved successfully!',
            'info' => 'You have not made any changes to SMTP!',
            'error' => 'Error saving data SMTP! Please check the filled fields!'
        ],
        'reset-smtp' => [
            'success' => 'The SMTP settings are reverted to default!',
            'info' => 'You have not made any changes to SMTP!',
            'error' => 'Error resetting SMTP data!'
        ],
        'smtp-test' => [
            'success' => 'The email was sent successfully!',
            'info' => 'You must configure SMTP!',
            'error' => 'Error: the settings entered for SMTP are incorrect, please change them with the correct ones!'
        ],
        'hide-wp-admin' => [
            'success' => 'The changes hide wp admin have been saved successfully!',
            'info' => 'You have not made any changes to hide wp admin!',
            'error' => 'Error resetting hide wp admin data!'
        ],
    ];

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in press-lock_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The press-lock_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/press-lock-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700&display=swap', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap-4.4.1.min.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-fontawesome', 'https://use.fontawesome.com/releases/v5.6.0/css/all.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in press-lock_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The press-lock_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/press-lock-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . '-bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap-4.4.1.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . '-popper', plugin_dir_url(__FILE__) . 'js/popper-1.14.7.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-tabs');
	}

	public function addAdminMenu() {
	    if ($this->checkEnroll()) {
            add_menu_page( 'PressLock Wizard', 'PressLock Wizard', 'manage_options', 'press-lock/admin/partials/checkWizard.php');
        } else {
            add_menu_page( 'PressLock Dashboard', 'PressLock Dashboard', 'manage_options', 'press-lock/admin/partials/dashboard.php');
        }
    }

    public function wizardEnroll() {

        if (isset($_POST['wizard_nonce']) && wp_verify_nonce($_POST['wizard_nonce'], 'wizard_info')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'press_lock_settings';
            $inputValue = sanitize_email($_POST['wizard-email']);
            $wpdb->insert(
                $table_name,
                array(
                    'setting' => 'wizard_email',
                    'value' => $inputValue
                    ),
                array( '%s','%s' )
            );

            $this->setServerStatus($this->getServerStatus());
            wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php'));
            exit();
        }
        wp_die( __FILE__." ".__LINE__);
    }

    public function checkEnroll() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';
        $checkEmail = $wpdb->get_results( "SELECT * FROM $table_name WHERE setting= 'wizard_email' LIMIT 1", ARRAY_A );
        return !$checkEmail;
    }

    public function saveSmtpData() {
        if (isset($_POST['smtp_nonce']) && wp_verify_nonce($_POST['smtp_nonce'], 'smtp_info')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'press_lock_settings';
            $settingsSMTP = [
                "from_email" => sanitize_email($_POST['from_email']),
                "from_name" => sanitize_text_field($_POST['from_name']),
                "reply_to" => sanitize_email($_POST['reply_to']),
                "host" => sanitize_text_field($_POST['host']),
                "port" => sanitize_text_field($_POST['port']),
                "encryption" => sanitize_text_field($_POST['encryption']),
                "authentication" => sanitize_text_field($_POST['authentication']),
                "username" => sanitize_text_field($_POST['username'])
            ];

            if( empty($settingsSMTP["from_email"]) || empty($settingsSMTP["from_name"]) ||empty($settingsSMTP["host"]) ||empty($settingsSMTP["port"]) ) {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp&error=1'));
                exit();
            }

            if (!empty($_POST['password'])) {
                $settingsSMTP['password'] = sanitize_text_field($_POST['password']);
            } else {
                $settingsDataSMTP = $wpdb->get_results("SELECT * FROM {$table_name} WHERE setting= 'smtp_email' LIMIT 1", ARRAY_A);
                $settingsDataSMTP = json_decode($settingsDataSMTP[0]['value'], true);
                $settingsSMTP['password'] = empty($settingsDataSMTP) ? '' : sanitize_text_field($settingsDataSMTP['password']);
            }

            try {
                $response = $wpdb->update(
                    $table_name,
                    array(
                        'setting' => 'smtp_email',
                        'value' => json_encode($settingsSMTP)
                    ),
                    array('setting' => 'smtp_email'),
                    array('%s', '%s')
                );

                if ($response) {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp&success=1'));
                } else {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp&info=1'));
                }
            } catch (Exception $exception) {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp&error=1'));
            }

            exit();
        }
        wp_die( __FILE__." ".__LINE__);
    }

    public function resetSmtpData() {
        if (isset($_POST['smtp_nonce']) && wp_verify_nonce($_POST['smtp_nonce'], 'smtp_info')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'press_lock_settings';
            try {
                $wpdb->delete( $table_name, array('setting' => 'smtp_email') );
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=reset-smtp&success=1'));
            } catch (Exception $exception) {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=reset-smtp&error=1'));
            }

            exit();
        }
        wp_die( __FILE__." ".__LINE__);
    }

    /**
     * Test send mail smtp
     */
    public function testSMTPData()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';

        if (isset($_POST['smtp_nonce']) && wp_verify_nonce($_POST['smtp_nonce'], 'smtp_info_test')) {
            $settingsDataSMTP = $wpdb->get_results("SELECT * FROM {$table_name} WHERE setting= 'smtp_email' LIMIT 1", ARRAY_A);
            $settingsDataSMTP = json_decode($settingsDataSMTP[0]['value'], true);

            if ($settingsDataSMTP['host'] && $settingsDataSMTP['port']) {
                try {
                    $sendMail = wp_mail(sanitize_text_field($_POST['email']), 'Mail test!', 'Test mail success!');

                    if ($sendMail) {
                        wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp-test&success=1'));
                    } else {
                        wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp-test&error=1'));
                    }
                } catch (Exception $exception) {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp-test&error=1'));
                }
            } else {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=smtp-test&info=1'));
            }
        }
    }

    /**
     * Save SSL data
     */
    public function saveSSLData()
    {
        if (isset($_POST['ssl_nonce']) && wp_verify_nonce($_POST['ssl_nonce'], 'ssl_info')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'press_lock_settings';
            $settingsSSL = [
                "force_ssl" => isset($_POST['force_ssl']) ? sanitize_text_field($_POST['force_ssl']) : false,
                "to_strict_transport_security" => isset($_POST['to_strict_transport_security']) ? sanitize_text_field($_POST['to_strict_transport_security']) : false
            ];

            try {
                $response = $wpdb->update(
                    $table_name,
                    array(
                        'setting' => 'ssl',
                        'value' => json_encode($settingsSSL)
                    ),
                    array('setting' => 'ssl'),
                    array('%s', '%s')
                );

                if ($response) {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=ssl&success=1'));
                } else {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=ssl&info=1'));
                }
            } catch (Exception $exception) {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=ssl&error=1'));
            }

            exit();
        }
    }

    /**
     * Hide wp admin save data
     */
    public function saveHideWpAdminData()
    {
        if (isset($_POST['hide_wp_admin_nonce']) && wp_verify_nonce($_POST['hide_wp_admin_nonce'], 'hide_up_admin')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'press_lock_settings';
            $settings = [
                'admin' => sanitize_text_field($_POST['admin']),
                'login' => sanitize_text_field($_POST['login']),
                'register' => sanitize_text_field($_POST['register']),
                'reset_password' => sanitize_text_field($_POST['reset_password']),
                'log_out' => sanitize_text_field($_POST['log_out']),
                "show_404_admin" => true,
                "show_404_login" => true
//                "show_404_admin" => isset($_POST['show_404_admin']) ? $_POST['show_404_admin'] : false,
//                "show_404_login" => isset($_POST['show_404_login']) ? $_POST['show_404_login'] : false
            ];

            try {
                $response = $wpdb->update(
                    $table_name,
                    array(
                        'setting' => 'hide_wp_admin',
                        'value' => json_encode($settings)
                    ),
                    array('setting' => 'hide_wp_admin'),
                    array('%s', '%s')
                );

                if (!empty($settings["admin"]) || !empty($settings["login"]) || !empty($settings["register"])
                    || !empty($settings["reset_password"]) || !empty($settings["log_out"])) {
                    $subject = 'DO NOT DELETE! - PressLock hide WP Admin links settings';
                    $template= '<p>
                                The following data has been saved in order to hide the default WP links:<br><br>
                                New wp-admin link: '.$settings["admin"].'<br>
                                New login link: '.$settings["login"].'<br>
                                New registration link: '.$settings["register"].'<br>
                                New reset password link: '.$settings["reset_password"].'<br>
                                New log out link: '.$settings["log_out"].'<br><br>
                                
                                It is EXTREMLY IMPORTANT TO SAVE THIS EMAIL, in case you forget the new custom WordPress links!<br><br>
                            </p>';
                    $this->sendNotificationEmail($subject, $template);
                }

                if ($response) {
                    $hideWpAdmin = new ANPL_Press_Lock_Hide_Wp_Admin();
                    $hideWpAdmin->rewriteURL();
                    flush_rewrite_rules();

                    if (empty($settings['admin'])) {
                        wp_safe_redirect(site_url('wp-admin' . '/admin.php?page=press-lock/admin/partials/dashboard.php&tab=hide-wp-admin&success=1'));
                    } else {
                        wp_safe_redirect(site_url($settings['admin'] . '/admin.php?page=press-lock/admin/partials/dashboard.php&tab=hide-wp-admin&success=1'));
                    }
                } else {
                    wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=hide-wp-admin&info=1'));
                }
            } catch (Exception $exception) {
                wp_safe_redirect(admin_url('admin.php?page=press-lock/admin/partials/dashboard.php&tab=hide-wp-admin&error=1'));
            }
            exit();
        }
    }

    public static function getServerStatus() {
	    global $required_php_version;
	    global $wp_version;
	    $serverStatus = [
            'phpVersion'=> false,
            'ssl'       => false,
            'isLatest'  => "No",
            'webEngine' => false,
            'debug'     => false,
            'debugLog'  => false,
            'installedPlugins' => 0
        ];
        if(version_compare(phpversion(),$required_php_version)>=0){
            $serverStatus['phpVersion'] = true;
        }

        if(is_ssl()) {
            $serverStatus['ssl'] = true;
        }

        $serverStatus['webEngine'] = $_SERVER['SERVER_SOFTWARE'];
        if( version_compare( $wp_version, '5.5', '>=' )) {
            $serverStatus['isLatest'] = "Yes";
        }

        $serverStatus['debug'] = WP_DEBUG;
        $serverStatus['debugLog'] = WP_DEBUG_LOG;
        $serverStatus['installedPlugins'] = count(get_plugins());

        return $serverStatus;
    }

    protected function setServerStatus(array $serverStatus ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';
        $wpdb->insert(
            $table_name,
            array(
                'setting' => 'server_status',
                'value' => json_encode($serverStatus)
            ),
            array( '%s','%s' )
        );
    }

    protected function sendNotificationEmail($subject, $template) {

        global $wpdb;
        $table_name = $wpdb->prefix . 'press_lock_settings';
        $checkEmail = $wpdb->get_results( "SELECT * FROM $table_name WHERE setting= 'wizard_email' LIMIT 1", ARRAY_A );

        $to = $checkEmail[0]['value'];
        $body = $template;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        //$_SERVER['HTTP_HOST']
        $isSent = wp_mail( $to, $subject, $body, $headers );

    }


}
