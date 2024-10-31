<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    global $wpdb;
    $table_name = $wpdb->prefix . 'press_lock_settings';
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE setting= 'server_status' LIMIT 1", ARRAY_A );
    if (count($retrieve_data)>0 && isset($retrieve_data[0]['value'])) {
        $serverStatus = json_decode($retrieve_data[0]['value'], true);
    }


    $settingsSMTP = $wpdb->get_results( "SELECT * FROM $table_name WHERE setting= 'smtp_email' LIMIT 1", ARRAY_A );
    if (count($settingsSMTP)>0 && isset($settingsSMTP[0]['value'])) {
        $settingsSMTP = json_decode($settingsSMTP[0]['value'], true);
    } else {
        $settingsSMTP = [
            "from_email" =>"",
            "from_name" =>"",
            "reply_to" =>"",
            "host" =>"",
            "port" =>"",
            "encryption" =>"",
            "authentication" =>"",
            "username" =>"",
            "password" =>"",
        ];
    }

    $settingsSSL = $wpdb->get_results("SELECT * FROM $table_name WHERE setting= 'ssl' LIMIT 1", ARRAY_A);
    if (count($settingsSSL) > 0 && isset($settingsSSL[0]['value'])) {
        $settingsSSL = json_decode($settingsSSL[0]['value'], true);
    } else {
        $settingsSSL = [
            "force_ssl" => false,
            "to_strict_transport_security" => false
        ];
    }

    $settingsHideWpAdmin = $wpdb->get_results("SELECT * FROM $table_name WHERE setting= 'hide_wp_admin' LIMIT 1", ARRAY_A);
    if (count($settingsHideWpAdmin) > 0 && isset($settingsHideWpAdmin[0]['value'])) {
        $settingsHideWpAdmin = json_decode($settingsHideWpAdmin[0]['value'], true);
    } else {
        $settingsHideWpAdmin = [
            "admin" => '',
            "login" => '',
            'register' => '',
            'reset_password' => '',
            'log_out' => '',
            'show_404_admin' => false,
            'show_404_login' => false
        ];
    }

    $checkedFiles = [
        [
            "name" => "wp-login.php",
            "modified" => false,
            "path" => ABSPATH."wp-login.php",
        ],
        [
            "name" => "index.php",
            "modified" => false,
            "path" => ABSPATH."index.php",
        ],
        [
            "name" => ".htaccess",
            "modified" => false,
            "path" => ABSPATH.".htaccess",
        ],
        [
            "name" => "wp-load.php",
            "modified" => false,
            "path" => ABSPATH."wp-load.php",
        ],
        [
            "name" => "wp-blog-header.php",
            "modified" => false,
            "path" => ABSPATH."wp-blog-header.php",
        ],
    ];
    $modifiedFilesPaths = [];
    $isFileListExportable = false;

    $settingsFilesHashes = $wpdb->get_results("SELECT * FROM $table_name WHERE setting= 'file_hashes' LIMIT 1", ARRAY_A);
    $settingsFilesHashes = json_decode($settingsFilesHashes[0]["value"]);
    foreach ($checkedFiles as $key=>$checkedFile) {
        if(hash_file('sha1', $checkedFile["path"]) != $settingsFilesHashes->$checkedFile["name"]) {
            $checkedFiles[$key]["modified"] = true;
            $isFileListExportable = true;
        }
    }

// Notifications Alert
if (isset($_GET['success'])) {
?>
    <div class="notice notice-success is-dismissible">
        <p><?=esc_html(ANPL_Press_Lock_Admin::NOTIFICATION_MESSAGES[$_GET['tab']]['success'])?></p>
    </div>
<?php
} elseif (isset($_GET['error'])) {
?>
    <div class="notice notice-error is-dismissible">
        <p><?=esc_html(ANPL_Press_Lock_Admin::NOTIFICATION_MESSAGES[$_GET['tab']]['error'])?></p>
    </div>
<?php
} elseif (isset($_GET['info'])) {
?>
    <div class="notice notice-info is-dismissible">
        <p><?=esc_html(ANPL_Press_Lock_Admin::NOTIFICATION_MESSAGES[$_GET['tab']]['info'])?></p>
    </div>
<?php
}
?>

<div class="container-fluid pl_master-container">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-12">

                    <div id="tabs">
                        <ul id="pl_tabs_menu">
                            <li><a href="#tabs-1"><i class="fas fa-bug"></i> Status report</a></li>
                            <li><a href="#tabs-2"><i class="fas fa-sync-alt"></i> Basic scan</a></li>
                            <li><a href="#tabs-3"><i class="fas fa-book-reader"></i> Learning center</a></li>
                            <li <? if(isset($_GET['tab']) && ($_GET['tab'] == 'smtp' || $_GET['tab'] == 'smtp-test' || $_GET['tab'] == 'reset-smtp')) echo 'class="ui-tabs-active ui-state-active"';?>>
                                <a href="#tabs-4"><i class="fas fa-envelope-open"></i> SMTP</a>
                            </li>
                            <li <? if(isset($_GET['tab']) && $_GET['tab'] == 'ssl') echo 'class="ui-tabs-active ui-state-active"';?>>
                                <a href="#tabs-5"><i class="fas fa-user-lock"></i> SSL</a>
                            </li>
                            <li <? if(isset($_GET['tab']) && $_GET['tab'] == 'hide-wp-admin') echo 'class="ui-tabs-active ui-state-active"';?>>
                                <a href="#tabs-8"><i class="fas fa-eye-slash"></i> Hide wp admin</a>
                            </li>
<!--                            <li><a href="#tabs-6"><i class="fas fa-question"></i> Help me now</a></li>-->
                            <li><a href="#tabs-7"><i class="fas fa-building"></i> About</a></li>
                        </ul>
                        <div class="pl_clear"></div>
                        <!-- start tab 1 -->
                        <div id="tabs-1" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 15px;">
                                    <h3><i class="fas fa-stethoscope"></i> General system report </h3>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col-12">
                                    <div class="progress">
                                        <div id="statusReportProgressBar" class="dynamic-progress progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
                                            <span id="current-progress"></span>
                                        </div>
                                        <span class="text">
                                            Your progress towards being secured!
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row" id="statusReportBody">
                                <div class="col-md-6 pl_status-form-cols">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4>Recommended server requirements <a href="https://wordpress.org/about/requirements/" target="_blank"><i class="fas fa-info-circle"></i></a></h4>
                                            <table class="pl_status_table">
                                                <tr>
                                                    <td class="pl_status_icon">
                                                        <?php
                                                            if($serverStatus['phpVersion'] ){
                                                                echo "<i class=\"fas fa-check\"></i>";
                                                            } else {
                                                                echo "<i class=\"fas fa-exclamation\"></i>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="pl_status_name">PHP version</td>
                                                    <td class="pl_status_info"><?=phpversion();?></td>
                                                </tr>
<!--                                                <tr>-->
<!--                                                    <td class="pl_status_icon"><i class="fas fa-check-double"></i></td>-->
<!--                                                    <td class="pl_status_name">Database type</td>-->
<!--                                                    <td class="pl_status_info">MySQL</td>-->
<!--                                                </tr>-->
<!--                                                <tr>-->
<!--                                                    <td class="pl_status_icon"><i class="fas fa-exclamation"></i></td>-->
<!--                                                    <td class="pl_status_name">?Database version?</td>-->
<!--                                                    <td class="pl_status_info">5.6</td>-->
<!--                                                </tr>-->
                                                <tr>
                                                    <td class="pl_status_icon">
                                                        <?php
                                                            if($serverStatus['ssl']){
                                                                echo "<i class=\"fas fa-check\"></i>";
                                                            } else {
                                                                echo "<i class=\"fas fa-exclamation\"></i>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="pl_status_name">HTTPS support</td>
                                                    <td class="pl_status_info">
                                                        <?php
                                                        if($serverStatus['ssl']){
                                                            echo "on";
                                                        } else {
                                                            echo "off";
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pl_status_icon"><i class="fas fa-check"></i></td>
                                                    <td class="pl_status_name">Web engine type</td>
                                                    <td class="pl_status_info"><?=esc_html($serverStatus['webEngine']);?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 pl_status-form-cols">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4>Current website status</h4>
                                            <table class="pl_status_table">
                                                <tr>
                                                    <td class="pl_status_icon"><i class="fas fa-check"></i></td>
                                                    <td class="pl_status_name">WP current version</td>
                                                    <td class="pl_status_info"><?=esc_html($wp_version);?></td>
                                                </tr>
                                                <tr>
                                                    <td class="pl_status_icon">
                                                        <?php
                                                        if($serverStatus['isLatest'] == "Yes"){
                                                            echo "<i class=\"fas fa-check-double\"></i>";
                                                        } else {
                                                            echo "<i class=\"fas fa-exclamation\"></i>";
                                                        }
                                                        ?>
                                                        </td>
                                                    <td class="pl_status_name">WP latest version</td>
                                                    <td class="pl_status_info"><?=esc_html($serverStatus['isLatest']);?></td>
                                                </tr>
                                                <tr>
                                                    <td class="pl_status_icon"><i class="fas fa-exclamation"></i></td>
                                                    <td class="pl_status_name">Installed plugins</td>
                                                    <td class="pl_status_info"><?=esc_html($serverStatus['installedPlugins']);?></td>
                                                </tr>
<!--                                                <tr>-->
<!--                                                    <td class="pl_status_icon"><i class="fas fa-check"></i></td>-->
<!--                                                    <td class="pl_status_name">?Plugins status?</td>-->
<!--                                                    <td class="pl_status_info">yes</td>-->
<!--                                                </tr>-->
                                                <tr>
                                                    <td class="pl_status_icon">
                                                        <?php
                                                            if($serverStatus['debug'] == false){
                                                                echo "<i class=\"fas fa-check\"></i>";
                                                            } else {
                                                                echo "<i class=\"fas fa-exclamation\"></i>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="pl_status_name">Debug status</td>
                                                    <td class="pl_status_info">
                                                        <?php
                                                            if($serverStatus['debug'] == false){
                                                                echo "off";
                                                            } else {
                                                                echo "on";
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pl_status_icon">
                                                        <?php
                                                        if($serverStatus['debugLog'] == false){
                                                            echo "<i class=\"fas fa-check\"></i>";
                                                        } else {
                                                            echo "<i class=\"fas fa-exclamation\"></i>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="pl_status_name">Debug log</td>
                                                    <td class="pl_status_info">
                                                        <?php
                                                        if($serverStatus['debugLog'] == false){
                                                            echo "off";
                                                        } else {
                                                            echo "on";
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <small>
                                        <table>
                                            <tr>
                                                <td class="text-center"><i class="fas fa-check" style="color:#33be53"></i></td>
                                                <td>Minimum requirement achieved</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><i class="fas fa-check-double" style="color:#338346"></i></td>
                                                <td>Recommended requirement</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><i class="fas fa-exclamation" style="color:#ff0000"></i></td>
                                                <td>Not up to standard</td>
                                            </tr>
                                        </table>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- end tab 1 -->
                        <!-- start tab 2 -->
                        <div id="tabs-2" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 15px;">
                                    <h3><i class="far fa-file-code"></i> Files vulnerabilities scan</h3>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col-12">
                                    <div class="progress">
                                        <div id="statusBasicScanProgressBar" class="dynamic-progress progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
                                            <span id="current-progress"></span>
                                        </div>
                                        <span class="text">
                                            Your progress towards being secured!
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="statusBasicScanBody" style="margin-top:30px;">
                                <div class="col-12">
                                    <table class="pl_files_status_table table table-hover">
                                        <thead>
                                            <tr>
                                                <th>File name</th>
                                                <th style="text-align:center;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            foreach($checkedFiles as $checkedFile){
                                                echo "<tr><td>".esc_html($checkedFile["name"])."</td>";
                                                if($checkedFile["modified"] === true) {
                                                    $modifiedFilesPaths[] = $checkedFile["path"];
                                                    echo "<td class=\"pl_status_icon\"><i class=\"fas fa-exclamation\"></i></td>";
                                                } else {
                                                    echo "<td class=\"pl_status_icon\"><i class=\"fas fa-check\"></i></td>";
                                                }
                                                echo "</tr>";
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php if ($isFileListExportable) { ?>
                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-6">
                                        <a href="modified_files_paths.txt" class="btn btn-warning" download>Download paths for modified files</a>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-12">
                                    <small>
                                        <div class="card" style="max-width: none;">
                                            <i class="fas fa-info-circle"></i><br>
                                            The Basic Scan feature ensures that the core files of your WordPress installation are not modified or altered by any third party entities. <br>PressLock is constantly proofing those files. If it finds any inconsistencies, it reports them with an exclamation mark. You should have those files marked as such inspected immediately!

                                        </div>
                                    </small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <small>
                                        <table>
                                            <tr>
                                                <td class="text-center"><i class="fas fa-check" style="color:#33be53"></i></td>
                                                <td>File is ok</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><i class="fas fa-exclamation" style="color:#ff0000"></i></td>
                                                <td>File has been modified</td>
                                            </tr>
                                        </table>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- end tab 2 -->
                        <!-- start tab 3 -->
                        <div id="tabs-3" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12">
                                    <h3><i class="fas fa-tachometer-alt"></i> Learning center</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4>Info</h4>
                                            <hr>
                                            <ul class="pl_rec-list">
                                                <li><a href="https://presslock.net/what-is-a-brute-force-attack/" target="_blank"><i class="fas fa-angle-right"></i> What is a Brute Force attack ?</a></li>
                                                <li><a href="https://presslock.net/insufficient-transport-layer-security/" target="_blank"><i class="fas fa-angle-right"></i> What is TLS (transport layer security) ?</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4>Hints</h4>
                                            <hr>
                                            <ul class="pl_rec-list">
                                                <li><a href="https://presslock.net/how-to-choose-between-handcoded-and-wordpress-website/" target="_blank"><i class="fas fa-angle-right"></i> How to chose between handcoded vs WordPress website !</a></li>
                                                <li><a href="https://presslock.net/insecure-password-storage/" target="_blank"><i class="fas fa-angle-right"></i> Insecure password storage</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end tab 3 -->
                        <!-- start tab 4 -->
                        <div id="tabs-4" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12">
                                    <h3><i class="fas fa-envelope"></i> SMTP Configuration</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <form class="pl_intro-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >
                                <?php wp_nonce_field('smtp_info', 'smtp_nonce', true, true); ?>
                                <input type="hidden" name="action" value="save_smtp" />
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">From Email address</label>
                                            <input type="email" class="form-control" id="from_email" aria-describedby="emailHelp" name="from_email" value="<?=$settingsSMTP["from_email"] ?>">
                                            <small id="emailHelp" class="form-text text-muted">This email address will be used in the 'From' field.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Reply-To Email Address</label>
                                            <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="reply_to_help" name="reply_to" value="<?=$settingsSMTP["reply_to"] ?>">
                                            <small id="reply_to_help" class="form-text text-muted">Optional. This email address will be used in the 'Reply-To' field of the email. Leave it blank to use 'From' email as the reply-to value.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">From Name</label>
                                            <input type="text" class="form-control" id="from_name" aria-describedby="from_name_help" name="from_name" value="<?=$settingsSMTP["from_name"] ?>">
                                            <small id="from_name_help" class="form-text text-muted">This text will be used in the 'FROM' field.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">SMTP Host</label>
                                            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="host_help" name="host" value="<?=$settingsSMTP["host"] ?>">
                                            <small id="host_help" class="form-text text-muted">Your mail server</small>
                                        </div>
                                        <fieldset class="form-group pl-radio-align">
                                            <legend class="col-form-label">Type of Encryption</legend>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="encryption" id="encryption1" value="none" <? if ($settingsSMTP["encryption"] == 'none') echo 'checked' ?>>
                                                <label class="form-check-label" for="encryption1">
                                                    NONE
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="encryption" id="encryption2" value="ssl/tls" <? if ($settingsSMTP["encryption"] == 'ssl/tls') echo 'checked' ?>>
                                                <label class="form-check-label" for="encryption2">
                                                    SSL/TLS
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="encryption" id="encryption3" value="starttls" <? if ($settingsSMTP["encryption"] == 'starttls') echo 'checked' ?>>
                                                <label class="form-check-label" for="encryption3">
                                                    STARTTLS
                                                </label>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">SMTP port</label>
                                            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="port_help" name="port" value="<?=$settingsSMTP["port"] ?>">
                                            <small id="port_help" class="form-text text-muted">The port to your mail server.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <fieldset class="form-group pl-radio-align">
                                            <legend class="col-form-label">SMTP Authentication</legend>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="authentication" id="authentication1" value="1" <?php if ($settingsSMTP["authentication"]==1) echo "checked" ?> >
                                                <label class="form-check-label" for="authentication1">
                                                    YES
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="authentication" id="authentication2" value="0" <?php if ($settingsSMTP["authentication"]==0) echo "checked" ?>>
                                                <label class="form-check-label" for="authentication2">
                                                    NO
                                                </label>
                                            </div>
                                            <small id="authentication2" class="form-text text-muted" style="float:left; clear: left;">This options should always be checked 'Yes'</small>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">SMTP Password</label>
                                            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="password_help" name="password">
                                            <small id="password_help" class="form-text text-muted">The password to login to your mail server</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">SMTP Username</label>
                                            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="username_help" name="username" value="<?=$settingsSMTP["username"] ?>">
                                            <small id="username_help" class="form-text text-muted">The username to login to your mail server.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">Keep me Secured</button>
                                    </div>
                                </div>
                            </form>

                            <form class="pl_intro-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >
                                <?php wp_nonce_field('smtp_info', 'smtp_nonce', true, true); ?>
                                <input type="hidden" name="action" value="reset_smtp" />
                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">Reset Settings</button>
                                    </div>
                                </div>
                            </form>

                            <!--SMTP Test-->
                            <br><br><br>
                            <div class="row">
                                <div class="col-12">
                                    <h3><i class="fas fa-envelope"></i> SMTP Test</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <form class="pl_intro-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >
                                <?php wp_nonce_field('smtp_info_test', 'smtp_nonce', true, true); ?>
                                <input type="hidden" name="action" value="test_smtp" />
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="InputEmail">Email</label>
                                            <input type="email" class="form-control" id="from_email" aria-describedby="emailHelp" name="email" required>
                                            <small id="emailHelp" class="form-text text-muted">Enter the email address to which you want to receive the test email.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Send mail</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- end tab 4 -->
                        <!-- start tab 5 -->
                        <div id="tabs-5" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12">
                                    <h3><i class="fas fa-lock"></i> SSL</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                <?php wp_nonce_field('ssl_info', 'ssl_nonce', true, true); ?>
                                <input type="hidden" name="action" value="save_ssl" />
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="enable_ssl">Force SSL</label><br>
                                            <div class="toggle-wrapper">
                                                <input type="checkbox" id="enable_ssl" name="force_ssl" value="true" <?php if($settingsSSL['force_ssl']) echo "checked" ?>>
                                                <label for="enable_ssl" class="toggle"><span class="toggle_handler"></span></label>
                                            </div>
                                            <small id="enable_ssl_help" class="form-text text-muted">Visitators will be autocatically redirected from HTTP to HTTPS for all page, posts and other WP content.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="enable_hsts">Enable HTTP Strict Transport Security (HSTS)</label><br>
                                            <div class="toggle-wrapper">
                                                <input type="checkbox" id="enable_hsts" name="to_strict_transport_security" value="true" <?php if($settingsSSL['to_strict_transport_security']) echo "checked" ?>>
                                                <label for="enable_hsts" class="toggle"><span class="toggle_handler"></span></label>
                                            </div>
                                            <small id="enable_hsts_help" class="form-text text-muted">HSTS is a web security policy mechanism that helps to protect your site against protocol downgrade attacks and cookie hijacking.
                                                It allows web servers to declare that web browsers should interact with it using only HTTPS connections.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- end tab 5 -->
                        <!-- start tab 8 -->
                        <div id="tabs-8" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-12">
                                    <h3><i class="fas fa-eye-slash"></i> Hide Wp Admin</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <form class="pl_intro-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >
                                <?php wp_nonce_field('hide_up_admin', 'hide_wp_admin_nonce', true, true); ?>
                                <input type="hidden" name="action" value="save_hide_wp_admin" />
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Admin</label>
                                            <input type="text" class="form-control" aria-describedby="emailHelp" name="admin" value="<?=$settingsHideWpAdmin["admin"] ?>">
                                            <small id="emailHelp" class="form-text text-muted">Admin url (default: /wp-admin).</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Login</label>
                                            <input type="text" class="form-control" aria-describedby="reply_to_help" name="login" value="<?=$settingsHideWpAdmin["login"] ?>">
                                            <small id="reply_to_help" class="form-text text-muted">Login url(default: /wp-login).</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Register</label>
                                            <input type="text" class="form-control" aria-describedby="from_name_help" name="register" value="<?=$settingsHideWpAdmin["register"] ?>">
                                            <small id="from_name_help" class="form-text text-muted">Register url(default: /wp-login.php?action=register).</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Reset password</label>
                                            <input type="text" class="form-control" aria-describedby="from_name_help" name="reset_password" value="<?=$settingsHideWpAdmin["reset_password"] ?>">
                                            <small id="from_name_help" class="form-text text-muted">Reset password url(default: /wp-login.php?action=lostpassword).</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Log out</label>
                                            <input type="text" class="form-control" aria-describedby="from_name_help" name="log_out" value="<?=$settingsHideWpAdmin["log_out"] ?>">
                                            <small id="from_name_help" class="form-text text-muted">Log out url(default: /wp-login.php?action=logout).</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- end tab 8 -->
                        <!-- start tab 6 -->
<!--                        <div id="tabs-6" class="pl_tabs_content">-->
<!--                            <div class="row">-->
<!--                                <div class="col-12">-->
<!--                                    <h3><i class="fas fa-user-md"></i> Talk right now to our support</h3>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="row">-->
<!--                                <div class="col-12">-->
<!--                                    <hr>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="row">-->
<!--                                <div class="col-md-3"></div>-->
<!--                                <div class="col-md-6">-->
<!--                                    <div class="card">-->
<!--                                        <div class="card-body text-center">-->
<!--                                            <a href="javascript:void(Tawk_API.toggle())" style="font-size:40px;"><img src="--><?php //echo plugin_dir_url( dirname( __FILE__ ) )?><!--/images/press-lock-logo-400.png" alt="PressLock" style="max-width:150px;"><br/>LIVE Help</a><br/><br/>Start chat with our super hero support team.-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="col-md-3"></div>-->
<!--                            </div>-->
<!--                        </div>-->
                        <!-- end tab 6 -->
                        <!-- start tab 7 -->
                        <div id="tabs-7" class="pl_tabs_content">
                            <div class="row">
                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <p><a href="https://presslock.net" target="_blank"><img src="<?php echo plugin_dir_url( dirname( __FILE__ ) )?>/images/press-lock-logo-400.png" alt="PressLock" style="max-width:150px;"></a></p>
                                            <p>Meet your dedicated team of cyber busters!</p>
                                            <p>We are a group of professionals with over 12 years of experience in software engineering, networking and cybersecurity.
                                            Our goal is to offer you a reliable and easy to use solution to help protect your online business against cyber attacks.
                                            We have designed this plugin with your safety in mind, so that you don’t have to worry about being hacked.
                                                By activating PressLock, you are adding vital layers of security to your website.</p>
                                            <p>Enjoy our free release, as well as the premium version for enhanced features and support!</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3"></div>
                            </div>
                        </div>
                        <!-- end tab 7 -->
                    </div>



                </div>
            </div>
        </div>
        <div class="col-lg-3"></div>
    </div>
</div>
<div class="pl_clear"></div>

<?php
$fileExecutionLocation = getcwd();
$currentPath = plugin_dir_path(__FILE__);
$pathString = "";

foreach ($modifiedFilesPaths as $modifiedFilesPath) {
    $pathString.= $modifiedFilesPath.PHP_EOL;
}

$fp = fopen($currentPath.'modified_files_paths.txt', 'w');
fwrite($fp, $pathString);
fclose($fp);
copy($currentPath.'modified_files_paths.txt',$fileExecutionLocation."/modified_files_paths.txt");
?>

