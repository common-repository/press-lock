<?php

class ANPL_Press_Lock_SMTP
{
    /**
     * Activate/disable debug smtp
     */
    const DEBUG = false;

    /**
     * SMTP host name
     *
     * @var string|null
     */
    private $host = null;

    /**
     * SMTP username
     *
     * @var string|null
     */
    private $username = null;

    /**
     * SMTP Password
     *
     * @var string|null
     */
    private $password = null;

    /**
     * SMTP port
     *
     * @var int|null
     */
    private $port = null;

    /**
     * SMTP encryption
     *
     * @var null
     */
    private $encryption = null;

    /**
     * SMTP authentication
     *
     * @var bool
     */
    private $auth = false;

    /**
     * SMTP from email
     *
     * @var string|null
     */
    private $formEmail = null;

    /**
     * SMTP form name
     *
     * @var string|null
     */
    private $formName = null;

    /**
     * SMTP replay to
     *
     * @var string|null
     */
    private $replyTo = null;

    /**
     * ANPL_Press_Lock_SMTP constructor.
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

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            $settingsSMTP = $wpdb->get_results("SELECT * FROM {$table_name} WHERE setting= 'smtp_email' LIMIT 1", ARRAY_A);

            if (!$settingsSMTP) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'setting' => 'smtp_email',
                        'value' => json_encode([
                            'from_email' => '',
                            'from_name' => '',
                            'reply_to' => '',
                            'host' => '',
                            'username' => '',
                            'password' => '',
                            'port' => '',
                            'authentication' => false,
                            'encryption' => 'none'
                        ])
                    )
                );
            } else {
                $settingsSMTP = json_decode($settingsSMTP[0]['value'], true);
                $this->formEmail = $settingsSMTP['from_email'];
                $this->formName = $settingsSMTP['from_name'];
                $this->replyTo = $settingsSMTP['reply_to'];
                $this->host = $settingsSMTP['host'];
                $this->username = $settingsSMTP['username'];
                $this->password = $settingsSMTP['password'];
                $this->port = $settingsSMTP['port'];
                $this->auth = $settingsSMTP['authentication'];
                $this->encryption = $settingsSMTP['encryption'];
            }
        }
        return $this;
    }

    /**
     * Activate smtp settings
     *
     * @param PHPMailer $mailer
     * @return bool
     */
    public function activate(PHPMailer $mailer)
    {
        $mailer->setFrom($this->formEmail, $this->formName);
        $mailer->addReplyTo($this->replyTo, $this->replyTo);
        $mailer->IsSMTP();
        $mailer->Host = $this->host;
        $mailer->Port = $this->port;

        if ($this->auth) {
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->username;
            $mailer->Password = $this->password;
        } else {
            $mailer->SMTPAuth = false;
        }

        if ($this->encryption == 'ssl/tls') {
            $mailer->SMTPAutoTLS = true;
        } elseif ($this->encryption == 'starttls') {
            $mailer->SMTPSecure = 'tls';
        }

        if (self::DEBUG) {
            $mailer->SMTPDebug = 4;
        }

        return true;
    }

    public function validateExistenceOfSmtpSettings()
    {
        if (empty($this->formEmail) || empty($this->formName) || empty($this->host)
            || empty($this->port) || empty($this->username) ) {
            return false;
        }
        return true;
    }
}