<?php

class ANPL_Press_Lock_Hide_Wp_Admin
{
    /**
     * Admin route
     *
     * @var string
     */
    public $admin;

    /**
     * Login route
     *
     * @var string
     */
    public $login;

    /**
     * Register route
     *
     * @var string
     */
    public $register;

    /**
     * Reset password route
     *
     * @var string
     */
    public $resetPassword;

    /**
     * Log out route
     *
     * @var string
     */
    public $logOut;

    /**
     * Show 404 Not Found Error when visitors access /wp-admin
     *
     * @var bool
     */
    public $show404Admin;

    /**
     * Show 404 Not Found Error when visitors access /wp-login.php
     *
     * @var bool
     */
    public $show404Login;

    /**
     * ANPL_Press_Lock_Hide_Wp_Admin constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initiate settings form database
     *
     * @return $this
     */
    public function init()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'press_lock_settings';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name) {
            $settingsHideWpAdmin = $wpdb->get_results("SELECT * FROM {$table_name} WHERE setting= 'hide_wp_admin' LIMIT 1", ARRAY_A);

            if (!$settingsHideWpAdmin) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'setting' => 'hide_wp_admin',
                        'value' => json_encode([
                            'admin' => '',
                            'login' => '',
                            'register' => '',
                            'reset_password' => '',
                            'log_out' => '',
                            'show_404_admin' => false,
                            'show_404_login' => false
                        ])
                    )
                );
            } else {
                $settingsHideWpAdmin = json_decode($settingsHideWpAdmin[0]['value'], true);
                $this->admin = $settingsHideWpAdmin['admin'];
                $this->login = $settingsHideWpAdmin['login'];
                $this->register = $settingsHideWpAdmin['register'];
                $this->resetPassword = $settingsHideWpAdmin['reset_password'];
                $this->logOut = $settingsHideWpAdmin['log_out'];
                $this->show404Admin = $settingsHideWpAdmin['show_404_admin'];
                $this->show404Login = $settingsHideWpAdmin['show_404_login'];
            }
        }

        return $this;
    }

    /**
     * Add htaccess new route
     */
    public function rewriteURL()
    {
        if (isset($this->admin) && !empty($this->admin)) {
            add_rewrite_rule("{$this->admin}/(.*)", 'wp-admin/$1?%{QUERY_STRING}', 'top');
        }

        if (isset($this->login) && !empty($this->login)) {
            add_rewrite_rule("{$this->login}/?$", 'wp-login.php', 'top');
        }

        if (isset($this->register) && !empty($this->register)) {
            add_rewrite_rule("{$this->register}/?$", 'wp-login.php?action=register', 'top');
        }

        if (isset($this->resetPassword) && !empty($this->resetPassword)) {
            add_rewrite_rule("{$this->resetPassword}/?$", 'wp-login.php?action=lostpassword', 'top');
            add_rewrite_rule("{$this->resetPassword}?checkemail=confirm/?$", 'wp-login.php?checkemail=confirm', 'top');
        }

        if (isset($this->logOut) && !empty($this->logOut)) {
            add_rewrite_rule("{$this->logOut}/?$", 'wp-login.php?action=logout', 'top');
        }
    }

    /**
     * Admin route
     *
     * @param $url
     * @param $path
     * @param $orig_scheme
     * @return string|string[]|null
     */
    public function admin($url, $path, $orig_scheme)
    {
        $old = array("/(wp-admin)/");
        $adminDir = $this->admin;
        $new = array($adminDir);

        return preg_replace($old, $new, $url, 1);
    }

    /**
     * Set the cookie constants in case of admin change
     */
    public function setCookieConstants()
    {
        if (!defined('ANPL_HMW_ADMIN_COOKIE_PATH')) {
            if (is_multisite()) {
                global $blog_id;
                switch_to_blog($blog_id);
                ms_cookie_constants();

                if (!is_subdomain_install() || trim(parse_url(get_option('siteurl'), PHP_URL_PATH), '/')) {
                    define('ANPL_HMW_ADMIN_COOKIE_PATH', SITECOOKIEPATH);
                } else {
                    define('ANPL_HMW_ADMIN_COOKIE_PATH', SITECOOKIEPATH . $this->admin);
                }

                restore_current_blog();
            } else {
                wp_cookie_constants();
                define('ANPL_HMW_ADMIN_COOKIE_PATH', SITECOOKIEPATH . $this->admin);
            }
        }
    }

    /**
     * Set post cookie new route
     *
     * @param $location
     * @param $post_id
     * @return mixed
     */
    public function setPostCookie($location, $post_id)
    {
        if (defined('ANPL_HMW_ADMIN_COOKIE_PATH')) {
            if ($post_id > 0) {
                if (isset($_COOKIE['wp-saving-post']) && $_COOKIE['wp-saving-post'] === $post_id . '-check') {
                    setcookie('wp-saving-post', $post_id . '-saved', time() + DAY_IN_SECONDS, ANPL_HMW_ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl());
                }
            }
        }

        return $location;
    }

    /**
     * Set auth cookie for new route admin
     *
     * @param $auth_cookie
     * @param $expire
     */
    public function setAuthCookie($auth_cookie, $expire)
    {
        if (defined('ANPL_HMW_ADMIN_COOKIE_PATH')) {
            $secure = is_ssl();

            if ($secure) {
                $auth_cookie_name = SECURE_AUTH_COOKIE;
            } else {
                $auth_cookie_name = AUTH_COOKIE;
            }

            setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
            setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, $secure, true);
            setcookie($auth_cookie_name, $auth_cookie, $expire, ANPL_HMW_ADMIN_COOKIE_PATH, $this->getCookieDomain(), $secure, true);
        }
    }

    /**
     * Clean cookie
     */
    public function setCleanCookie()
    {
        if (defined('ANPL_HMW_ADMIN_COOKIE_PATH') && defined('PLUGINS_COOKIE_PATH')) {
            setcookie(AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, ANPL_HMW_ADMIN_COOKIE_PATH, $this->getCookieDomain());
            setcookie(SECURE_AUTH_COOKIE, ' ', time() - YEAR_IN_SECONDS, ANPL_HMW_ADMIN_COOKIE_PATH, $this->getCookieDomain());
            setcookie('wordpress_logged_address', ' ', time() - YEAR_IN_SECONDS, ANPL_HMW_ADMIN_COOKIE_PATH, $this->getCookieDomain());
        }
    }

    /**
     * Set login cookie
     *
     * @param $logged_in_cookie
     * @param $expire
     */
    public function setLoginCookie($logged_in_cookie, $expire)
    {
        // Front-end cookie is secure when the auth cookie is secure and the site's home URL is forced HTTPS.
        $secure_logged_in_cookie = is_ssl() && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);

        setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, $this->getCookieDomain(), $secure_logged_in_cookie, true);

        if (COOKIEPATH != SITECOOKIEPATH) {
            setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, $this->getCookieDomain(), $secure_logged_in_cookie, true);
        }

        setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);

        if (COOKIEPATH != SITECOOKIEPATH)
            setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true);
    }

    /**
     * Get cookie domain
     *
     * @return string|string[]|null
     */
    public function getCookieDomain()
    {
        $domain = COOKIE_DOMAIN;

        if (is_multisite()) {
            global $blog_id;
            switch_to_blog($blog_id);
            $current_network = get_network();
            $domain = preg_replace('|^www\.|', '', parse_url(get_option('siteurl'), PHP_URL_HOST));

            if (!empty($current_network->cookie_domain)) {
                if (strpos($current_network->cookie_domain, $domain) === false) {
                    $domain = '.' . $domain;
                }
            } elseif (strpos($current_network->domain, $domain) === false) {
                $domain = '.' . $domain;
            }

            restore_current_blog();
        }

        return $domain;
    }

    /**
     * Login route
     *
     * @param $link
     * @return string|string[]
     */
    public function login($link)
    {
        return str_replace(site_url('wp-login.php', 'login'), site_url($this->login, 'login'), $link);
    }

    /**
     * Register route
     *
     * @param $link
     * @return string|string[]
     */
    public function register($link)
    {
        return str_replace(site_url('wp-login.php?action=register', 'login'), site_url($this->register, 'login'), $link);
    }

    /**
     * Reset password route
     *
     * @param $link
     * @return string|string[]
     */
    public function resetPassword($link)
    {
        return str_replace('?action=lostpassword', '', str_replace(network_site_url('wp-login.php', 'login'), site_url($this->resetPassword, 'login'), $link));
    }

    /**
     * Redirect after reset password
     *
     * @return mixed
     */
    public function resetPasswordRedirect()
    {
        return home_url($this->resetPassword . '?checkemail=confirm', 'login');
    }

    /**
     * Log out route
     *
     * @param $link
     * @return string|string[]
     */
    public function logOut($link)
    {
        return str_replace(site_url('wp-login.php', 'login'), site_url($this->logOut, 'login'), $link);
    }

    /**
     * Access old route, redirect to new route or error 404
     *
     * @return mixed
     */
    public function redirectNewRoute()
    {
        $url = $_SERVER['REQUEST_URI'];

        if (!empty($this->admin) && $url == '/' . $this->admin) {
            echo wp_redirect(admin_url());
        }

        if (!empty($this->admin) && strpos($url, 'wp-admin') == 1) {
            if ($this->show404Admin) {
                $this->redirect404();
            }

            return wp_redirect(admin_url(str_replace('/wp-admin', '', $url)));
        }

        if (!empty($this->register) && strpos($url, 'wp-login.php?action=register') && !is_user_logged_in() && empty($_POST)) {
            if ($this->show404Login) {
                $this->redirect404();
            }

            return wp_redirect(site_url($this->register));
        }

        if (!empty($this->resetPassword) && strpos($url, 'wp-login.php?action=lostpassword') && !is_user_logged_in() && empty($_POST)) {
            if ($this->show404Login) {
                $this->redirect404();
            }

            return wp_redirect(site_url($this->resetPassword));
        }

        if (!empty($this->login) && $url == '/wp-login.php' && !is_user_logged_in() && empty($_POST)) {
            if ($this->show404Login) {
                $this->redirect404();
            }

            return wp_redirect(site_url($this->login));
        }
    }

    /**
     * Redirect 404 page error
     */
    private function redirect404()
    {
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        get_template_part(404);

        exit();
    }

    /**
     * Check if enable
     */
    public function permalinkStructureAdminNotice()
    {
        echo '<div id="message" class="error"><p>Please Make sure to enable <a href="options-permalink.php">Permalinks</a>.</p></div>';
    }
}