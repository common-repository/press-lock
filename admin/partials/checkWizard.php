<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="container-fluid pl_master-container">
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6 text-center">
                    <!-- start email confirmation card -->
                    <div class="card" style="margin-top:20px; margin-bottom: 20px;">
                        <div class="card-body">
                            <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) )?>/images/press-lock-logo-400.png" alt="PressLock" class="pl_intro-form-logo">
                            <div class="pl_intro-form-info">In order to enhance your website security and have access the entire features list of PressLock features you need to provide your email address.</div>
                            <div class="break"></div>
                            <form class="pl_intro-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align: center;">
                                <?php wp_nonce_field('wizard_info', 'wizard_nonce', true, true); ?>
                                <input type="hidden" name="action" value="save_wizard_email" />
                                <div class="form-group">
                                    <input type="email" class="form-control pl_intro-form-email" name="wizard-email" aria-describedby="emailHelp" placeholder="Enter email here" required>
                                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
                                    <label class="form-check-label" for="exampleCheck1">I agree to the <br>
                                        <a href="https://presslock.net/terms-of-use/">Terms & Conditions</a> and
                                        <br><a href="https://presslock.net/privacy-policy/">Privacy Policy</a>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">Keep me Secured</button>
                            </form>
                        </div>
                    </div>
                    <!-- end email confirmation card -->
                </div>
                <div class="col-md-3"></div>
            </div>

        </div>

    </div>
</div>
