<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://presslock.net
 * @since      1.0.0
 *
 * @package    press-lock
 * @subpackage press-lock/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    press-lock
 * @subpackage press-lock/includes
 * @author     Stefan Astefanesei <stefan.astefanesei@presslock.net>
 */
class ANPL_Press_Lock {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      press-lock_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ANPL_PRESS_LOCK_VERSION' ) ) {
			$this->version = ANPL_PRESS_LOCK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'press-lock';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->ssl();
		$this->smtp();
		$this->hideWpAdmin();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - ANPL_Press_Lock_Loader. Orchestrates the hooks of the plugin.
	 * - press-lock_i18n. Defines internationalization functionality.
	 * - press-lock_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ANPL_Press_Lock_Loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ANPL_Press_Lock_i8n.php';

        /**
         * This class responsible for SSL
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/ANPL_Press_Lock_SSL.php';

        /**
         * This class responsible for Force SMTP
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ANPL_Press_Lock_SMTP.php';

        /**
         * This class responsible for Hide Wp Admin Url
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ANPL_Press_Lock_Hide_Wp_Admin.php';
    
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/ANPL_Press_Lock_Admin.php';

		$this->loader = new ANPL_Press_Lock_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the press-lock_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new ANPL_Press_Lock_i8n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new ANPL_Press_Lock_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'addAdminMenu' );
        $this->loader->add_action( 'admin_post_save_wizard_email', $plugin_admin, 'wizardEnroll');
        $this->loader->add_action( 'admin_post_save_smtp', $plugin_admin, 'saveSmtpData');
        $this->loader->add_action( 'admin_post_reset_smtp', $plugin_admin, 'resetSmtpData');
        $this->loader->add_action( 'admin_post_test_smtp', $plugin_admin, 'testSmtpData');
        $this->loader->add_action( 'admin_post_save_ssl', $plugin_admin, 'saveSSLData');
        $this->loader->add_action( 'admin_post_save_hide_wp_admin', $plugin_admin, 'saveHideWpAdminData');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    press-lock_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

    /**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * SSL add action
     */
    public function ssl()
    {
      $plugin = new ANPL_Press_Lock_SSL();

      if ($plugin->ssl) {
          $this->loader->add_action('template_redirect', $plugin, 'redirect');
      }

      if ($plugin->hsts) {
          $this->loader->add_action('send_headers', $plugin, 'toStrictTransportSecurity');
      }
    }

    /**
    * SMTP add action config custom data
    */
	public function smtp()
    {
        $plugin = new ANPL_Press_Lock_SMTP();
        if ($plugin->validateExistenceOfSmtpSettings()) {
            $this->loader->add_action( 'phpmailer_init', $plugin, 'activate');
        }
    }

    /**
     * Hide wp admin add action
     */
    public function hideWpAdmin()
    {
        $plugin = new ANPL_Press_Lock_Hide_Wp_Admin();

        if (!get_option('permalink_structure')) {
            $this->loader->add_action('admin_notices', $plugin, 'permalinkStructureAdminNotice');
        }

        if (isset($plugin->admin) && !empty($plugin->admin)) {
            $plugin->setCookieConstants();
            $this->loader->add_filter('admin_url', $plugin, 'admin', 10, 3);
            $this->loader->add_filter('redirect_post_location', $plugin, 'setPostCookie', PHP_INT_MAX, 2);
            $this->loader->add_action('set_auth_cookie', $plugin, 'setAuthCookie', PHP_INT_MAX, 2);
            $this->loader->add_action('clear_auth_cookie', $plugin, 'setCleanCookie', PHP_INT_MAX);
            $this->loader->add_action('set_logged_in_cookie', $plugin, 'setLoginCookie', PHP_INT_MAX, 2);
        }

        if (isset($plugin->login) && !empty($plugin->login)) {
            $this->loader->add_filter('login_url', $plugin, 'login', 10, 2);
        }

        if (isset($plugin->register) && !empty($plugin->register)) {
            $this->loader->add_filter('register', $plugin, 'register');
        }

        if (isset($plugin->resetPassword) && !empty($plugin->resetPassword)) {
            $this->loader->add_filter('lostpassword_url', $plugin, 'resetPassword');
        }

        if (isset($plugin->logOut) && !empty($plugin->logOut)) {
            $this->loader->add_filter('logout_url', $plugin, 'logOut', 15, 2);
        }

        $this->loader->add_action('init', $plugin, 'redirectNewRoute');
    }
}
