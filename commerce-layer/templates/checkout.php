<?php
/**
 * Checkout Template
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $cart should be available from calling context
if ( ! isset( $cart ) ) {
    $cart = CL_Cart::get_instance();
}

$items = $cart->get_contents();
$gateway = get_option( 'cl_payment_gateway', 'tranzila' );
?>
<div class="cl-checkout">
    <?php if ( isset( $_GET['payment_error'] ) ) : ?>
        <div class="cl-notice cl-notice-error">
            <?php esc_html_e( 'התשלום נכשל. אנא נסה שוב.', 'commerce-layer' ); ?>
        </div>
    <?php endif; ?>

    <form id="cl-checkout-form" class="cl-checkout-form">
        <?php wp_nonce_field( 'cl_checkout_nonce', 'checkout_nonce' ); ?>

        <div class="cl-checkout-grid">
            <!-- Customer Details -->
            <div class="cl-checkout-details">
                <h3><?php esc_html_e( 'פרטי לקוח', 'commerce-layer' ); ?></h3>

                <div class="cl-form-row">
                    <label for="cl-name"><?php esc_html_e( 'שם מלא', 'commerce-layer' ); ?> <span class="required">*</span></label>
                    <input type="text" id="cl-name" name="name" required>
                </div>

                <div class="cl-form-row">
                    <label for="cl-email"><?php esc_html_e( 'אימייל', 'commerce-layer' ); ?> <span class="required">*</span></label>
                    <input type="email" id="cl-email" name="email" required>
                </div>

                <div class="cl-form-row">
                    <label for="cl-phone"><?php esc_html_e( 'טלפון', 'commerce-layer' ); ?> <span class="required">*</span></label>
                    <input type="tel" id="cl-phone" name="phone" required dir="ltr">
                </div>

                <div class="cl-form-row">
                    <label for="cl-address"><?php esc_html_e( 'כתובת', 'commerce-layer' ); ?></label>
                    <input type="text" id="cl-address" name="address">
                </div>

                <div class="cl-form-row cl-form-row-half">
                    <div>
                        <label for="cl-city"><?php esc_html_e( 'עיר', 'commerce-layer' ); ?></label>
                        <input type="text" id="cl-city" name="city">
                    </div>
                    <div>
                        <label for="cl-postcode"><?php esc_html_e( 'מיקוד', 'commerce-layer' ); ?></label>
                        <input type="text" id="cl-postcode" name="postcode" dir="ltr">
                    </div>
                </div>

                <div class="cl-form-row">
                    <label for="cl-notes"><?php esc_html_e( 'הערות להזמנה', 'commerce-layer' ); ?></label>
                    <textarea id="cl-notes" name="notes" rows="3"></textarea>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cl-checkout-summary">
                <h3><?php esc_html_e( 'סיכום הזמנה', 'commerce-layer' ); ?></h3>

                <div class="cl-order-items">
                    <?php foreach ( $items as $item ) : ?>
                        <div class="cl-order-item">
                            <div class="cl-item-info">
                                <span class="cl-item-name"><?php echo esc_html( $item['name'] ); ?></span>
                                <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                    <span class="cl-item-variant"><?php echo esc_html( $item['variant_name'] ); ?></span>
                                <?php endif; ?>
                                <span class="cl-item-qty">x<?php echo esc_html( $item['quantity'] ); ?></span>
                            </div>
                            <span class="cl-item-price"><?php echo CL_Core::format_price( $item['price'] * $item['quantity'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cl-order-totals">
                    <div class="cl-total-row">
                        <span><?php esc_html_e( 'סיכום ביניים:', 'commerce-layer' ); ?></span>
                        <span><?php echo CL_Core::format_price( $cart->get_subtotal() ); ?></span>
                    </div>
                    <div class="cl-total-row cl-total-final">
                        <span><?php esc_html_e( 'סה"כ לתשלום:', 'commerce-layer' ); ?></span>
                        <span class="cl-final-amount"><?php echo CL_Core::format_price( $cart->get_total() ); ?></span>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="cl-payment-section">
                    <h4><?php esc_html_e( 'תשלום', 'commerce-layer' ); ?></h4>

                    <?php if ( 'test' === $gateway ) : ?>
                        <p class="cl-test-mode"><?php esc_html_e( '⚠️ מצב בדיקה - ללא תשלום אמיתי', 'commerce-layer' ); ?></p>
                    <?php endif; ?>

                    <button type="submit" class="cl-btn cl-btn-pay">
                        <?php
                        printf(
                            esc_html__( 'שלם %s', 'commerce-layer' ),
                            CL_Core::format_price( $cart->get_total() )
                        );
                        ?>
                    </button>

                    <p class="cl-secure-notice">
                        🔒 <?php esc_html_e( 'התשלום מאובטח', 'commerce-layer' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </form>

    <!-- Payment iframe container -->
    <div id="cl-payment-container" class="cl-payment-container" style="display: none;">
        <div class="cl-payment-overlay"></div>
        <div class="cl-payment-modal">
            <button type="button" class="cl-close-payment">&times;</button>
            <div class="cl-payment-iframe-wrap">
                <iframe id="cl-payment-iframe" class="cl-payment-iframe"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#cl-checkout-form');
    var $paymentContainer = $('#cl-payment-container');
    var $iframe = $('#cl-payment-iframe');

    $form.on('submit', function(e) {
        e.preventDefault();

        var $btn = $form.find('.cl-btn-pay');
        $btn.prop('disabled', true).text('<?php esc_html_e( 'מעבד...', 'commerce-layer' ); ?>');

        $.post(clFrontend.ajaxUrl, {
            action: 'cl_process_checkout',
            nonce: $form.find('[name="checkout_nonce"]').val(),
            name: $form.find('[name="name"]').val(),
            email: $form.find('[name="email"]').val(),
            phone: $form.find('[name="phone"]').val(),
            address: $form.find('[name="address"]').val(),
            city: $form.find('[name="city"]').val(),
            postcode: $form.find('[name="postcode"]').val(),
            notes: $form.find('[name="notes"]').val()
        }, function(response) {
            if (response.success) {
                if (response.data.redirect) {
                    window.location.href = response.data.redirect_url;
                } else if (response.data.iframe) {
                    $iframe.attr('src', response.data.iframe_url);
                    $paymentContainer.show();
                } else if (response.data.paypal) {
                    // Handle PayPal
                    // This would require PayPal JS SDK integration
                }
            } else {
                alert(response.data.message || '<?php esc_html_e( 'שגיאה בעיבוד ההזמנה', 'commerce-layer' ); ?>');
                $btn.prop('disabled', false).text('<?php echo esc_js( sprintf( __( 'שלם %s', 'commerce-layer' ), CL_Core::format_price( $cart->get_total() ) ) ); ?>');
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            $btn.prop('disabled', false).text('<?php echo esc_js( sprintf( __( 'שלם %s', 'commerce-layer' ), CL_Core::format_price( $cart->get_total() ) ) ); ?>');
        });
    });

    // Close payment modal
    $('.cl-close-payment, .cl-payment-overlay').on('click', function() {
        $paymentContainer.hide();
        $iframe.attr('src', '');
        $form.find('.cl-btn-pay').prop('disabled', false);
    });
});
</script>
