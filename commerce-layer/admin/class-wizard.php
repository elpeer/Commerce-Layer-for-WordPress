<?php
/**
 * Setup Wizard Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Wizard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_notices', array( $this, 'wizard_notice' ) );
        add_action( 'wp_ajax_cl_wizard_save', array( $this, 'save_wizard' ) );
        add_action( 'wp_ajax_cl_wizard_skip', array( $this, 'skip_wizard' ) );
        add_action( 'admin_footer', array( $this, 'render_wizard_modal' ) );
    }

    /**
     * Show wizard notice
     */
    public function wizard_notice() {
        if ( ! get_option( 'cl_needs_wizard' ) ) {
            return;
        }

        // Only show on our pages or dashboard
        $screen = get_current_screen();
        if ( ! $screen || ( strpos( $screen->id, 'commerce-layer' ) === false && $screen->id !== 'dashboard' ) ) {
            return;
        }
        ?>
        <div class="notice notice-info is-dismissible cl-wizard-notice">
            <p>
                <strong><?php esc_html_e( 'Commerce Layer', 'commerce-layer' ); ?></strong> -
                <?php esc_html_e( 'ברוך הבא! בוא נגדיר את התוסף תוך דקות ספורות.', 'commerce-layer' ); ?>
                <a href="#" class="cl-open-wizard button button-primary" style="margin-right: 10px;">
                    <?php esc_html_e( 'התחל הגדרה', 'commerce-layer' ); ?>
                </a>
                <a href="#" class="cl-skip-wizard">
                    <?php esc_html_e( 'דלג', 'commerce-layer' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render wizard modal
     */
    public function render_wizard_modal() {
        if ( ! get_option( 'cl_needs_wizard' ) ) {
            return;
        }

        // Only on relevant pages
        $screen = get_current_screen();
        if ( ! $screen || ( strpos( $screen->id, 'commerce-layer' ) === false && $screen->id !== 'dashboard' ) ) {
            return;
        }

        // Get available post types
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $excluded = array( 'attachment' );

        ?>
        <div id="cl-wizard-modal" class="cl-wizard-modal" style="display: none;">
            <div class="cl-wizard-overlay"></div>
            <div class="cl-wizard-content">
                <button type="button" class="cl-wizard-close">&times;</button>

                <div class="cl-wizard-header">
                    <h2><?php esc_html_e( 'הגדרת Commerce Layer', 'commerce-layer' ); ?></h2>
                    <div class="cl-wizard-steps">
                        <span class="cl-step active" data-step="1">1</span>
                        <span class="cl-step" data-step="2">2</span>
                        <span class="cl-step" data-step="3">3</span>
                        <span class="cl-step" data-step="4">4</span>
                    </div>
                </div>

                <form id="cl-wizard-form">
                    <?php wp_nonce_field( 'cl_wizard_nonce', 'wizard_nonce' ); ?>

                    <!-- Step 1: Post Types -->
                    <div class="cl-wizard-step" data-step="1">
                        <h3><?php esc_html_e( 'בחר את סוגי התוכן למכירה', 'commerce-layer' ); ?></h3>
                        <p class="description">
                            <?php esc_html_e( 'בחר על אילו סוגי תוכן תרצה להפעיל אפשרות מכירה', 'commerce-layer' ); ?>
                        </p>

                        <div class="cl-checkbox-group">
                            <?php foreach ( $post_types as $post_type ) : ?>
                                <?php if ( in_array( $post_type->name, $excluded ) ) continue; ?>
                                <label class="cl-checkbox-label">
                                    <input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>">
                                    <span><?php echo esc_html( $post_type->label ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Purchase Mode -->
                    <div class="cl-wizard-step" data-step="2" style="display: none;">
                        <h3><?php esc_html_e( 'מודל רכישה', 'commerce-layer' ); ?></h3>
                        <p class="description">
                            <?php esc_html_e( 'איך הלקוחות ירכשו מוצרים?', 'commerce-layer' ); ?>
                        </p>

                        <div class="cl-radio-group">
                            <label class="cl-radio-card">
                                <input type="radio" name="purchase_mode" value="cart" checked>
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'סל קניות', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'הלקוח מוסיף פריטים לסל ומשלם בסוף', 'commerce-layer' ); ?></span>
                                </span>
                            </label>

                            <label class="cl-radio-card">
                                <input type="radio" name="purchase_mode" value="buy_now">
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'קנייה מיידית', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'הלקוח עובר ישירות לתשלום', 'commerce-layer' ); ?></span>
                                </span>
                            </label>

                            <label class="cl-radio-card">
                                <input type="radio" name="purchase_mode" value="both">
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'שניהם', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'הצג גם הוסף לסל וגם קנה עכשיו', 'commerce-layer' ); ?></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 3: Payment -->
                    <div class="cl-wizard-step" data-step="3" style="display: none;">
                        <h3><?php esc_html_e( 'שער תשלום', 'commerce-layer' ); ?></h3>
                        <p class="description">
                            <?php esc_html_e( 'בחר את שער התשלום שלך', 'commerce-layer' ); ?>
                        </p>

                        <div class="cl-radio-group">
                            <label class="cl-radio-card">
                                <input type="radio" name="payment_gateway" value="tranzila" checked>
                                <span class="cl-radio-content">
                                    <strong>Tranzila</strong>
                                    <span><?php esc_html_e( 'שער תשלום ישראלי פופולרי', 'commerce-layer' ); ?></span>
                                </span>
                            </label>

                            <label class="cl-radio-card">
                                <input type="radio" name="payment_gateway" value="cardcom">
                                <span class="cl-radio-content">
                                    <strong>Cardcom</strong>
                                    <span><?php esc_html_e( 'קארדקום - סליקה ישראלית', 'commerce-layer' ); ?></span>
                                </span>
                            </label>

                            <label class="cl-radio-card">
                                <input type="radio" name="payment_gateway" value="test">
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'מצב בדיקה', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'לבדיקות בלבד - ללא תשלום אמיתי', 'commerce-layer' ); ?></span>
                                </span>
                            </label>
                        </div>

                        <div class="cl-payment-fields" data-gateway="tranzila">
                            <label>
                                <?php esc_html_e( 'מספר מסוף:', 'commerce-layer' ); ?>
                                <input type="text" name="tranzila_terminal" placeholder="your_terminal">
                            </label>
                        </div>

                        <div class="cl-payment-fields" data-gateway="cardcom" style="display: none;">
                            <label>
                                <?php esc_html_e( 'מספר מסוף:', 'commerce-layer' ); ?>
                                <input type="text" name="cardcom_terminal" placeholder="1000">
                            </label>
                            <label>
                                <?php esc_html_e( 'שם משתמש:', 'commerce-layer' ); ?>
                                <input type="text" name="cardcom_username">
                            </label>
                        </div>
                    </div>

                    <!-- Step 4: Display -->
                    <div class="cl-wizard-step" data-step="4" style="display: none;">
                        <h3><?php esc_html_e( 'תצוגת רכיב הקומרס', 'commerce-layer' ); ?></h3>
                        <p class="description">
                            <?php esc_html_e( 'איפה להציג את כפתורי הרכישה?', 'commerce-layer' ); ?>
                        </p>

                        <div class="cl-radio-group">
                            <label class="cl-radio-card">
                                <input type="radio" name="display_mode" value="auto" checked>
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'אוטומטי', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'הזרקה אוטומטית אחרי התוכן', 'commerce-layer' ); ?></span>
                                </span>
                            </label>

                            <label class="cl-radio-card">
                                <input type="radio" name="display_mode" value="shortcode">
                                <span class="cl-radio-content">
                                    <strong><?php esc_html_e( 'ידני', 'commerce-layer' ); ?></strong>
                                    <span><?php esc_html_e( 'השתמש ב-Shortcode או Block', 'commerce-layer' ); ?></span>
                                </span>
                            </label>
                        </div>

                        <label class="cl-checkbox-label" style="margin-top: 20px;">
                            <input type="checkbox" name="floating_bar" value="yes">
                            <span><?php esc_html_e( 'הפעל סרגל צף במובייל', 'commerce-layer' ); ?></span>
                        </label>
                    </div>
                </form>

                <div class="cl-wizard-footer">
                    <button type="button" class="button cl-wizard-prev" style="display: none;">
                        <?php esc_html_e( 'הקודם', 'commerce-layer' ); ?>
                    </button>
                    <button type="button" class="button button-primary cl-wizard-next">
                        <?php esc_html_e( 'הבא', 'commerce-layer' ); ?>
                    </button>
                    <button type="button" class="button button-primary cl-wizard-finish" style="display: none;">
                        <?php esc_html_e( 'סיום', 'commerce-layer' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .cl-wizard-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 100000; }
            .cl-wizard-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); }
            .cl-wizard-content { position: relative; max-width: 600px; margin: 50px auto; background: #fff; border-radius: 8px; padding: 30px; max-height: calc(100vh - 100px); overflow-y: auto; }
            .cl-wizard-close { position: absolute; top: 15px; left: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
            .cl-wizard-header { text-align: center; margin-bottom: 30px; }
            .cl-wizard-header h2 { margin: 0 0 20px; }
            .cl-wizard-steps { display: flex; justify-content: center; gap: 10px; }
            .cl-wizard-steps .cl-step { width: 30px; height: 30px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; }
            .cl-wizard-steps .cl-step.active { background: #2271b1; color: #fff; }
            .cl-wizard-steps .cl-step.completed { background: #46b450; color: #fff; }
            .cl-wizard-step h3 { margin-top: 0; }
            .cl-checkbox-group, .cl-radio-group { display: flex; flex-direction: column; gap: 10px; }
            .cl-checkbox-label, .cl-radio-card { display: flex; align-items: flex-start; padding: 15px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
            .cl-checkbox-label:hover, .cl-radio-card:hover { border-color: #2271b1; }
            .cl-checkbox-label input, .cl-radio-card input { margin-left: 10px; }
            .cl-radio-content { display: flex; flex-direction: column; }
            .cl-radio-content strong { margin-bottom: 5px; }
            .cl-radio-content span { color: #666; font-size: 13px; }
            .cl-payment-fields { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 6px; }
            .cl-payment-fields label { display: block; margin-bottom: 10px; }
            .cl-payment-fields input { width: 100%; margin-top: 5px; }
            .cl-wizard-footer { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var currentStep = 1;
            var totalSteps = 4;

            // Open wizard
            $('.cl-open-wizard').on('click', function(e) {
                e.preventDefault();
                $('#cl-wizard-modal').show();
            });

            // Close wizard
            $('.cl-wizard-close, .cl-wizard-overlay').on('click', function() {
                $('#cl-wizard-modal').hide();
            });

            // Skip wizard
            $('.cl-skip-wizard').on('click', function(e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'cl_wizard_skip',
                    nonce: $('#wizard_nonce').val()
                }, function() {
                    $('.cl-wizard-notice').remove();
                    $('#cl-wizard-modal').hide();
                });
            });

            // Next step
            $('.cl-wizard-next').on('click', function() {
                if (currentStep < totalSteps) {
                    $('.cl-wizard-step[data-step="' + currentStep + '"]').hide();
                    currentStep++;
                    $('.cl-wizard-step[data-step="' + currentStep + '"]').show();
                    updateStepIndicators();
                    updateButtons();
                }
            });

            // Previous step
            $('.cl-wizard-prev').on('click', function() {
                if (currentStep > 1) {
                    $('.cl-wizard-step[data-step="' + currentStep + '"]').hide();
                    currentStep--;
                    $('.cl-wizard-step[data-step="' + currentStep + '"]').show();
                    updateStepIndicators();
                    updateButtons();
                }
            });

            // Finish wizard
            $('.cl-wizard-finish').on('click', function() {
                var formData = $('#cl-wizard-form').serialize();
                formData += '&action=cl_wizard_save';

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        $('#cl-wizard-modal').hide();
                        $('.cl-wizard-notice').remove();
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php esc_html_e( 'שגיאה', 'commerce-layer' ); ?>');
                    }
                });
            });

            // Payment gateway toggle
            $('input[name="payment_gateway"]').on('change', function() {
                var gateway = $(this).val();
                $('.cl-payment-fields').hide();
                $('.cl-payment-fields[data-gateway="' + gateway + '"]').show();
            });

            function updateStepIndicators() {
                $('.cl-wizard-steps .cl-step').each(function() {
                    var step = $(this).data('step');
                    $(this).removeClass('active completed');
                    if (step < currentStep) {
                        $(this).addClass('completed');
                    } else if (step === currentStep) {
                        $(this).addClass('active');
                    }
                });
            }

            function updateButtons() {
                if (currentStep === 1) {
                    $('.cl-wizard-prev').hide();
                } else {
                    $('.cl-wizard-prev').show();
                }

                if (currentStep === totalSteps) {
                    $('.cl-wizard-next').hide();
                    $('.cl-wizard-finish').show();
                } else {
                    $('.cl-wizard-next').show();
                    $('.cl-wizard-finish').hide();
                }
            }
        });
        </script>
        <?php
    }

    /**
     * Save wizard settings
     */
    public function save_wizard() {
        check_ajax_referer( 'cl_wizard_nonce', 'wizard_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        // Save post types
        $post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', $_POST['post_types'] ) : array();
        update_option( 'cl_enabled_post_types', $post_types );

        // Save purchase mode
        $purchase_mode = isset( $_POST['purchase_mode'] ) ? sanitize_text_field( $_POST['purchase_mode'] ) : 'both';
        update_option( 'cl_purchase_mode', $purchase_mode );

        // Save payment gateway
        $gateway = isset( $_POST['payment_gateway'] ) ? sanitize_text_field( $_POST['payment_gateway'] ) : 'test';
        update_option( 'cl_payment_gateway', $gateway );

        if ( 'tranzila' === $gateway && ! empty( $_POST['tranzila_terminal'] ) ) {
            update_option( 'cl_tranzila_terminal', sanitize_text_field( $_POST['tranzila_terminal'] ) );
        }

        if ( 'cardcom' === $gateway ) {
            if ( ! empty( $_POST['cardcom_terminal'] ) ) {
                update_option( 'cl_cardcom_terminal', sanitize_text_field( $_POST['cardcom_terminal'] ) );
            }
            if ( ! empty( $_POST['cardcom_username'] ) ) {
                update_option( 'cl_cardcom_username', sanitize_text_field( $_POST['cardcom_username'] ) );
            }
        }

        // Save display mode
        $display_mode = isset( $_POST['display_mode'] ) ? sanitize_text_field( $_POST['display_mode'] ) : 'auto';
        update_option( 'cl_display_mode', $display_mode );

        // Save floating bar
        $floating_bar = isset( $_POST['floating_bar'] ) ? 'yes' : 'no';
        update_option( 'cl_floating_bar_enabled', $floating_bar );

        // Mark wizard as complete
        delete_option( 'cl_needs_wizard' );

        wp_send_json_success( array( 'message' => __( 'ההגדרות נשמרו', 'commerce-layer' ) ) );
    }

    /**
     * Skip wizard
     */
    public function skip_wizard() {
        check_ajax_referer( 'cl_wizard_nonce', 'wizard_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }

        delete_option( 'cl_needs_wizard' );

        wp_send_json_success();
    }
}
