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
$totals = $cart->get_totals();
$gateway = get_option( 'cl_payment_gateway', 'tranzila' );
$shipping_methods = get_option( 'cl_shipping_methods', array() );
$free_shipping_threshold = floatval( get_option( 'cl_free_shipping_threshold', 0 ) );
$show_discounts = 'yes' === get_option( 'cl_show_discount_in_cart', 'yes' );
$total_savings = $totals['savings'];

// Check if eligible for free shipping
$eligible_for_free_shipping = $free_shipping_threshold > 0 && $totals['subtotal'] >= $free_shipping_threshold;

// Filter shipping methods (show all methods that have a name, enabled or not)
$enabled_shipping_methods = array();
foreach ( $shipping_methods as $method ) {
    if ( ! empty( $method['name'] ) && ( ! isset( $method['enabled'] ) || $method['enabled'] ) ) {
        $enabled_shipping_methods[] = $method;
    }
}

// Add free shipping option if eligible
if ( $eligible_for_free_shipping ) {
    array_unshift( $enabled_shipping_methods, array(
        'name'  => __( 'משלוח חינם', 'commerce-layer' ),
        'price' => 0,
    ) );
}
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

                <?php if ( ! empty( $enabled_shipping_methods ) ) : ?>
                <!-- Shipping Options -->
                <div class="cl-shipping-section">
                    <h4><?php esc_html_e( 'אופציות משלוח', 'commerce-layer' ); ?></h4>
                    <div class="cl-shipping-options">
                        <?php foreach ( $enabled_shipping_methods as $index => $method ) :
                            $method_id = sanitize_title( $method['name'] );
                            $shipping_price = floatval( $method['price'] );
                        ?>
                            <label class="cl-shipping-option">
                                <input type="radio" name="shipping_method" value="<?php echo esc_attr( $index ); ?>"
                                       data-price="<?php echo esc_attr( $shipping_price ); ?>"
                                       <?php checked( $index, 0 ); ?>>
                                <span class="cl-shipping-option-content">
                                    <span class="cl-shipping-name"><?php echo esc_html( $method['name'] ); ?></span>
                                    <span class="cl-shipping-price">
                                        <?php if ( $shipping_price > 0 ) : ?>
                                            <?php echo CL_Core::format_price( $shipping_price ); ?>
                                        <?php else : ?>
                                            <?php esc_html_e( 'חינם', 'commerce-layer' ); ?>
                                        <?php endif; ?>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Summary -->
            <div class="cl-checkout-summary">
                <h3><?php esc_html_e( 'סיכום הזמנה', 'commerce-layer' ); ?></h3>

                <div class="cl-order-items">
                    <?php foreach ( $items as $item ) :
                        $item_price = floatval( $item['price'] );
                        $item_regular_price = isset( $item['regular_price'] ) ? floatval( $item['regular_price'] ) : $item_price;
                        $item_has_discount = $item_regular_price > $item_price;
                        $item_savings = $item_has_discount ? ( $item_regular_price - $item_price ) * $item['quantity'] : 0;
                        $line_total = $item_price * $item['quantity'];
                        $line_regular_total = $item_regular_price * $item['quantity'];
                    ?>
                        <div class="cl-order-item">
                            <div class="cl-item-info">
                                <span class="cl-item-name"><?php echo esc_html( $item['name'] ); ?></span>
                                <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                    <span class="cl-item-variant"><?php echo esc_html( $item['variant_name'] ); ?></span>
                                <?php endif; ?>
                                <span class="cl-item-qty">x<?php echo esc_html( $item['quantity'] ); ?></span>
                            </div>
                            <div class="cl-item-price-wrap">
                                <?php if ( $item_has_discount && $show_discounts ) : ?>
                                    <span class="cl-item-original-price"><?php echo CL_Core::format_price( $line_regular_total ); ?></span>
                                    <span class="cl-item-price cl-sale-price"><?php echo CL_Core::format_price( $line_total ); ?></span>
                                <?php else : ?>
                                    <span class="cl-item-price"><?php echo CL_Core::format_price( $line_total ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cl-order-totals">
                    <?php if ( $total_savings > 0 && $show_discounts ) : ?>
                    <div class="cl-total-row cl-original-subtotal">
                        <span><?php esc_html_e( 'מחיר מקורי:', 'commerce-layer' ); ?></span>
                        <span class="cl-strikethrough"><?php echo CL_Core::format_price( $totals['subtotal_before_discounts'] ); ?></span>
                    </div>
                    <div class="cl-total-row cl-savings-row">
                        <span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 4px;">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                            <?php esc_html_e( 'חסכת:', 'commerce-layer' ); ?>
                        </span>
                        <span class="cl-savings-amount">-<?php echo CL_Core::format_price( $total_savings ); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="cl-total-row">
                        <span><?php esc_html_e( 'סיכום ביניים:', 'commerce-layer' ); ?></span>
                        <span id="cl-subtotal"><?php echo CL_Core::format_price( $totals['subtotal'] ); ?></span>
                    </div>

                    <?php if ( ! empty( $enabled_shipping_methods ) ) :
                        $default_shipping = isset( $enabled_shipping_methods[0] ) ? floatval( $enabled_shipping_methods[0]['price'] ) : 0;
                    ?>
                    <div class="cl-total-row cl-shipping-row">
                        <span><?php esc_html_e( 'משלוח:', 'commerce-layer' ); ?></span>
                        <span id="cl-shipping-cost"><?php echo $default_shipping > 0 ? CL_Core::format_price( $default_shipping ) : esc_html__( 'חינם', 'commerce-layer' ); ?></span>
                    </div>
                    <?php $final_total = $totals['subtotal'] + $default_shipping; ?>
                    <?php else : ?>
                    <?php $final_total = $totals['subtotal']; ?>
                    <?php endif; ?>

                    <div class="cl-total-row cl-total-final">
                        <span><?php esc_html_e( 'סה"כ לתשלום:', 'commerce-layer' ); ?></span>
                        <span class="cl-final-amount" id="cl-final-total"><?php echo CL_Core::format_price( $final_total ); ?></span>
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
                            CL_Core::format_price( $final_total )
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
    var subtotal = <?php echo floatval( $totals['subtotal'] ); ?>;
    var currencySymbol = '<?php echo esc_js( get_option( 'cl_currency_symbol', '₪' ) ); ?>';
    var currencyPosition = '<?php echo esc_js( get_option( 'cl_currency_position', 'right' ) ); ?>';

    // Format price helper
    function formatPrice(amount) {
        var formatted = amount.toFixed(2);
        if (currencyPosition === 'right') {
            return formatted + ' ' + currencySymbol;
        } else {
            return currencySymbol + ' ' + formatted;
        }
    }

    // Update totals when shipping changes
    $('input[name="shipping_method"]').on('change', function() {
        var shippingPrice = parseFloat($(this).data('price')) || 0;
        var total = subtotal + shippingPrice;

        if (shippingPrice > 0) {
            $('#cl-shipping-cost').text(formatPrice(shippingPrice));
        } else {
            $('#cl-shipping-cost').text('<?php echo esc_js( __( 'חינם', 'commerce-layer' ) ); ?>');
        }

        $('#cl-final-total').text(formatPrice(total));
        $('.cl-btn-pay').text('<?php echo esc_js( __( 'שלם', 'commerce-layer' ) ); ?> ' + formatPrice(total));
    });

    $form.on('submit', function(e) {
        e.preventDefault();

        var $btn = $form.find('.cl-btn-pay');
        $btn.prop('disabled', true).text('<?php esc_html_e( 'מעבד...', 'commerce-layer' ); ?>');

        var shippingMethod = $form.find('[name="shipping_method"]:checked');
        var shippingIndex = shippingMethod.length ? shippingMethod.val() : '';
        var shippingPrice = shippingMethod.length ? parseFloat(shippingMethod.data('price')) || 0 : 0;

        $.post(clFrontend.ajaxUrl, {
            action: 'cl_process_checkout',
            nonce: $form.find('[name="checkout_nonce"]').val(),
            name: $form.find('[name="name"]').val(),
            email: $form.find('[name="email"]').val(),
            phone: $form.find('[name="phone"]').val(),
            address: $form.find('[name="address"]').val(),
            city: $form.find('[name="city"]').val(),
            postcode: $form.find('[name="postcode"]').val(),
            notes: $form.find('[name="notes"]').val(),
            shipping_method: shippingIndex,
            shipping_price: shippingPrice
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
                var currentTotal = subtotal + (parseFloat($form.find('[name="shipping_method"]:checked').data('price')) || 0);
                $btn.prop('disabled', false).text('<?php echo esc_js( __( 'שלם', 'commerce-layer' ) ); ?> ' + formatPrice(currentTotal));
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            var currentTotal = subtotal + (parseFloat($form.find('[name="shipping_method"]:checked').data('price')) || 0);
            $btn.prop('disabled', false).text('<?php echo esc_js( __( 'שלם', 'commerce-layer' ) ); ?> ' + formatPrice(currentTotal));
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
