<?php

class ANPL_Press_Lock_SSL
{
    /**
     * SSL Enable/Disable
     *
     * @var bool
     */
    public $ssl = false;

    /**
     * Strict transport security
     *
     * @var bool
     */
    public $hsts = false;

    /**
     * ANPL_Press_Lock_SSL constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init settings
     *
     * @return bool
     */
    private function init()
    {
        global $wpdb;
        $this->ssl = false;
        $this->hsts = false;

        $table_name = $wpdb->prefix . 'press_lock_settings';
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            $settingsSSL = $wpdb->get_results("SELECT * FROM {$table_name} WHERE setting = 'ssl' LIMIT 1", ARRAY_A);
            if (!$settingsSSL) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'setting' => 'ssl',
                        'value' => json_encode([
                            'force_ssl' => false,
                            'to_strict_transport_security' => false
                        ])
                    )
                );
            } else {
                $settingsSSL = json_decode($settingsSSL[0]['value'], true);
                $this->ssl = $settingsSSL['force_ssl'];
                $this->hsts = $settingsSSL['to_strict_transport_security'];
            }
        }

        return true;
    }

    /**
     * Perform the redirect to HTTPS if loaded over HTTP
     */
    public function redirect()
    {
        if (!is_ssl()) {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            die;
        }
    }

    /**
     * Send the HTTP Strict Transport Security (HSTS) header.
     */
    public function toStrictTransportSecurity()
    {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}