<?php
/**
 * Checkout Template - Shopify Style
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
$checkout_mode = get_option( 'cl_checkout_mode', 'payment' );
$is_lead_mode = 'lead' === $checkout_mode;
$lead_button_text = get_option( 'cl_lead_button_text', __( 'שלח פנייה', 'commerce-layer' ) );
$shipping_methods = get_option( 'cl_shipping_methods', array() );
$free_shipping_threshold = floatval( get_option( 'cl_free_shipping_threshold', 0 ) );
$show_discounts = 'yes' === get_option( 'cl_show_discount_in_cart', 'yes' );
$total_savings = $totals['savings'];

// Check if eligible for free shipping
$eligible_for_free_shipping = $free_shipping_threshold > 0 && $totals['subtotal'] >= $free_shipping_threshold;

// Filter shipping methods
$enabled_shipping_methods = array();
foreach ( $shipping_methods as $method ) {
    if ( ! empty( $method['name'] ) && ( ! isset( $method['enabled'] ) || $method['enabled'] ) ) {
        $enabled_shipping_methods[] = $method;
    }
}

// Add free shipping option if eligible
if ( $eligible_for_free_shipping ) {
    array_unshift( $enabled_shipping_methods, array(
        'name'  => __( 'משלוח חינם עד הבית', 'commerce-layer' ),
        'price' => 0,
    ) );
}

// Check for applied coupon
$applied_coupon = $cart->get_applied_coupon();
$coupon_discount = $totals['discount'];

// Calculate initial total
$default_shipping = ! empty( $enabled_shipping_methods ) ? floatval( $enabled_shipping_methods[0]['price'] ) : 0;
$final_total = $totals['subtotal'] - $coupon_discount + $default_shipping;
?>
<div class="cl-checkout cl-checkout-shopify">
    <?php if ( isset( $_GET['payment_error'] ) ) : ?>
        <div class="cl-notice cl-notice-error">
            <?php esc_html_e( 'התשלום נכשל. אנא נסה שוב.', 'commerce-layer' ); ?>
        </div>
    <?php endif; ?>

    <form id="cl-checkout-form" class="cl-checkout-form">
        <?php wp_nonce_field( 'cl_checkout_nonce', 'checkout_nonce' ); ?>

        <div class="cl-checkout-layout">
            <!-- Right Side - Form (in RTL) -->
            <div class="cl-checkout-main">

                <!-- Contact Section -->
                <div class="cl-checkout-section">
                    <h2 class="cl-section-title"><?php esc_html_e( 'פרטים', 'commerce-layer' ); ?></h2>

                    <div class="cl-form-group">
                        <label for="cl-email"><?php esc_html_e( 'דוא"ל', 'commerce-layer' ); ?></label>
                        <input type="email" id="cl-email" name="email" required placeholder="<?php esc_attr_e( 'דוא"ל', 'commerce-layer' ); ?>">
                    </div>
                </div>

                <!-- Shipping Address Section -->
                <div class="cl-checkout-section">
                    <h2 class="cl-section-title"><?php esc_html_e( 'משלוח', 'commerce-layer' ); ?></h2>

                    <div class="cl-form-row-2">
                        <div class="cl-form-group">
                            <label for="cl-first-name"><?php esc_html_e( 'שם פרטי', 'commerce-layer' ); ?></label>
                            <input type="text" id="cl-first-name" name="first_name" required placeholder="<?php esc_attr_e( 'שם פרטי', 'commerce-layer' ); ?>">
                        </div>
                        <div class="cl-form-group">
                            <label for="cl-last-name"><?php esc_html_e( 'שם משפחה', 'commerce-layer' ); ?></label>
                            <input type="text" id="cl-last-name" name="last_name" required placeholder="<?php esc_attr_e( 'שם משפחה', 'commerce-layer' ); ?>">
                        </div>
                    </div>

                    <div class="cl-form-row-2">
                        <div class="cl-form-group">
                            <label for="cl-city"><?php esc_html_e( 'עיר', 'commerce-layer' ); ?></label>
                            <input type="text" id="cl-city" name="city" required placeholder="<?php esc_attr_e( 'עיר', 'commerce-layer' ); ?>">
                        </div>
                        <div class="cl-form-group">
                            <label for="cl-address"><?php esc_html_e( 'שם רחוב', 'commerce-layer' ); ?></label>
                            <input type="text" id="cl-address" name="address" required placeholder="<?php esc_attr_e( 'שם רחוב', 'commerce-layer' ); ?>">
                        </div>
                    </div>

                    <div class="cl-form-group">
                        <label for="cl-address2"><?php esc_html_e( 'מספר בית ודירה (אופציונלי)', 'commerce-layer' ); ?></label>
                        <input type="text" id="cl-address2" name="address2" placeholder="<?php esc_attr_e( 'מספר בית ודירה', 'commerce-layer' ); ?>">
                    </div>

                    <div class="cl-form-group">
                        <label for="cl-phone"><?php esc_html_e( 'טלפון', 'commerce-layer' ); ?></label>
                        <input type="tel" id="cl-phone" name="phone" required dir="ltr" placeholder="<?php esc_attr_e( 'טלפון', 'commerce-layer' ); ?>">
                    </div>

                    <div class="cl-form-group">
                        <label for="cl-notes"><?php esc_html_e( 'הערות משלוח (אופציונלי)', 'commerce-layer' ); ?></label>
                        <textarea id="cl-notes" name="notes" rows="2" placeholder="<?php esc_attr_e( 'הערות למשלוח', 'commerce-layer' ); ?>"></textarea>
                    </div>
                </div>

                <?php if ( ! empty( $enabled_shipping_methods ) ) : ?>
                <!-- Shipping Method Section -->
                <div class="cl-checkout-section">
                    <h2 class="cl-section-title"><?php esc_html_e( 'שיטת משלוח', 'commerce-layer' ); ?></h2>

                    <div class="cl-shipping-methods">
                        <?php foreach ( $enabled_shipping_methods as $index => $method ) :
                            $shipping_price = floatval( $method['price'] );
                        ?>
                            <label class="cl-shipping-method-card <?php echo $index === 0 ? 'selected' : ''; ?>">
                                <input type="radio" name="shipping_method" value="<?php echo esc_attr( $index ); ?>"
                                       data-price="<?php echo esc_attr( $shipping_price ); ?>"
                                       <?php checked( $index, 0 ); ?>>
                                <span class="cl-shipping-method-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                </span>
                                <span class="cl-shipping-method-details">
                                    <span class="cl-shipping-method-name"><?php echo esc_html( $method['name'] ); ?></span>
                                </span>
                                <span class="cl-shipping-method-price">
                                    <?php if ( $shipping_price > 0 ) : ?>
                                        <?php echo CL_Core::format_price( $shipping_price ); ?>
                                    <?php else : ?>
                                        <?php esc_html_e( 'חינם', 'commerce-layer' ); ?>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! $is_lead_mode ) : ?>
                <!-- Payment Section -->
                <div class="cl-checkout-section">
                    <h2 class="cl-section-title"><?php esc_html_e( 'תשלום', 'commerce-layer' ); ?></h2>
                    <p class="cl-section-subtitle"><?php esc_html_e( 'כל העסקאות מאובטחות ומוצפנות.', 'commerce-layer' ); ?></p>

                    <?php if ( 'test' === $gateway ) : ?>
                        <div class="cl-test-mode-banner">
                            <?php esc_html_e( '⚠️ מצב בדיקה - ללא תשלום אמיתי', 'commerce-layer' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="cl-payment-methods">
                        <label class="cl-payment-method-card selected">
                            <input type="radio" name="payment_method" value="credit_card" checked>
                            <span class="cl-payment-method-info">
                                <span class="cl-payment-method-name"><?php esc_html_e( 'כרטיס אשראי', 'commerce-layer' ); ?></span>
                                <span class="cl-payment-icons">
                                    <img src="https://cdn.shopify.com/shopifycloud/checkout-web/assets/c1.en/assets/visa.sxIq5Dot.svg" alt="Visa" height="24">
                                    <img src="https://cdn.shopify.com/shopifycloud/checkout-web/assets/c1.en/assets/mastercard.1c4_lyMp.svg" alt="Mastercard" height="24">
                                </span>
                            </span>
                            <span class="cl-payment-radio"></span>
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Submit Button (Mobile) -->
                <div class="cl-checkout-submit-mobile">
                    <button type="submit" class="cl-btn cl-btn-pay cl-btn-checkout">
                        <?php echo $is_lead_mode ? esc_html( $lead_button_text ) : esc_html__( 'לתשלום', 'commerce-layer' ); ?>
                    </button>
                    <?php if ( ! $is_lead_mode ) : ?>
                    <p class="cl-secure-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <?php esc_html_e( 'מאובטח באמצעות תקני אבטחה מתקדמים', 'commerce-layer' ); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Left Side - Order Summary (in RTL) -->
            <div class="cl-checkout-sidebar">
                <div class="cl-order-summary">
                    <div class="cl-order-summary-header">
                        <a href="<?php echo esc_url( get_permalink( get_option( 'cl_cart_page_id' ) ) ); ?>" class="cl-summary-toggle">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <?php esc_html_e( 'לעריכת הזמנה', 'commerce-layer' ); ?>
                        </a>
                    </div>

                    <!-- Order Items -->
                    <div class="cl-order-items-list">
                        <?php foreach ( $items as $cart_key => $item ) :
                            $product = new CL_Product( $item['post_id'] );
                            $thumbnail = $product->get_thumbnail( 'thumbnail' );
                            $item_price = floatval( $item['price'] );
                            $item_regular_price = isset( $item['regular_price'] ) ? floatval( $item['regular_price'] ) : $item_price;
                            $item_has_discount = $item_regular_price > $item_price;
                            $line_total = $item_price * $item['quantity'];
                            $line_regular_total = $item_regular_price * $item['quantity'];
                        ?>
                            <div class="cl-summary-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                                <div class="cl-summary-item-image">
                                    <?php if ( $thumbnail ) : ?>
                                        <img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
                                    <?php endif; ?>
                                    <span class="cl-summary-item-qty"><?php echo esc_html( $item['quantity'] ); ?></span>
                                </div>
                                <div class="cl-summary-item-details">
                                    <span class="cl-summary-item-name"><?php echo esc_html( $item['name'] ); ?></span>
                                    <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                        <span class="cl-summary-item-variant"><?php echo esc_html( $item['variant_name'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="cl-summary-item-price">
                                    <?php if ( $item_has_discount && $show_discounts ) : ?>
                                        <span class="cl-price-original"><?php echo CL_Core::format_price( $line_regular_total ); ?></span>
                                    <?php endif; ?>
                                    <span class="cl-price-current"><?php echo CL_Core::format_price( $line_total ); ?></span>
                                </div>
                                <button type="button" class="cl-checkout-remove-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>" title="<?php esc_attr_e( 'הסר מהסל', 'commerce-layer' ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Coupon Code -->
                    <div class="cl-coupon-section">
                        <?php if ( ! empty( $applied_coupon ) ) : ?>
                            <div class="cl-coupon-applied">
                                <span class="cl-coupon-tag">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                    </svg>
                                    <code><?php echo esc_html( $applied_coupon['code'] ); ?></code>
                                    <button type="button" class="cl-remove-coupon" title="<?php esc_attr_e( 'הסר קופון', 'commerce-layer' ); ?>">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </span>
                                <span class="cl-coupon-discount">-<?php echo CL_Core::format_price( $coupon_discount ); ?></span>
                            </div>
                        <?php else : ?>
                            <div class="cl-coupon-input-wrap">
                                <input type="text" name="coupon_code" placeholder="<?php esc_attr_e( 'קוד הנחה', 'commerce-layer' ); ?>" class="cl-coupon-input">
                                <button type="button" class="cl-btn cl-btn-coupon"><?php esc_html_e( 'החל', 'commerce-layer' ); ?></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Totals -->
                    <div class="cl-order-totals-summary">
                        <?php if ( $total_savings > 0 && $show_discounts ) : ?>
                        <div class="cl-summary-row cl-summary-discount">
                            <span><?php esc_html_e( 'סכום הנחת מבצע', 'commerce-layer' ); ?></span>
                            <span class="cl-discount-amount">-<?php echo CL_Core::format_price( $total_savings ); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ( $coupon_discount > 0 ) : ?>
                        <div class="cl-summary-row cl-summary-coupon">
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 4px;">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                                <?php esc_html_e( 'קופון', 'commerce-layer' ); ?>
                            </span>
                            <span class="cl-coupon-discount-amount">-<?php echo CL_Core::format_price( $coupon_discount ); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="cl-summary-row">
                            <span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 4px;">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <?php esc_html_e( 'משלוח', 'commerce-layer' ); ?>
                            </span>
                            <span id="cl-shipping-cost"><?php echo $default_shipping > 0 ? CL_Core::format_price( $default_shipping ) : esc_html__( 'חינם', 'commerce-layer' ); ?></span>
                        </div>

                        <div class="cl-summary-row cl-summary-total">
                            <span><?php esc_html_e( 'סך הכל', 'commerce-layer' ); ?></span>
                            <span class="cl-total-amount">
                                <span id="cl-final-total"><?php echo CL_Core::format_price( $final_total ); ?></span>
                                <small><?php esc_html_e( 'כולל מע"מ', 'commerce-layer' ); ?></small>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button (Desktop) -->
                    <div class="cl-checkout-submit-desktop">
                        <button type="submit" class="cl-btn cl-btn-pay cl-btn-checkout">
                            <?php if ( ! $is_lead_mode ) : ?>
                            <span class="cl-btn-icon">+</span>
                            <?php endif; ?>
                            <?php echo $is_lead_mode ? esc_html( $lead_button_text ) : esc_html__( 'לתשלום', 'commerce-layer' ); ?>
                        </button>
                        <?php if ( ! $is_lead_mode ) : ?>
                        <p class="cl-secure-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <?php esc_html_e( 'מאובטח באמצעות תקני אבטחה מתקדמים', 'commerce-layer' ); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php if ( ! $is_lead_mode ) : ?>
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
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#cl-checkout-form');
    var $paymentContainer = $('#cl-payment-container');
    var $iframe = $('#cl-payment-iframe');
    var subtotal = <?php echo floatval( $totals['subtotal'] ); ?>;
    var couponDiscount = <?php echo floatval( $coupon_discount ); ?>;
    var currencySymbol = '<?php echo esc_js( get_option( 'cl_currency_symbol', '₪' ) ); ?>';
    var currencyPosition = '<?php echo esc_js( get_option( 'cl_currency_position', 'right' ) ); ?>';
    var isLeadMode = <?php echo $is_lead_mode ? 'true' : 'false'; ?>;

    function formatPrice(amount) {
        var formatted = amount.toFixed(2);
        if (currencyPosition === 'right') {
            return formatted + ' ' + currencySymbol;
        } else {
            return currencySymbol + ' ' + formatted;
        }
    }

    // Shipping method selection
    $('.cl-shipping-method-card').on('click', function() {
        $('.cl-shipping-method-card').removeClass('selected');
        $(this).addClass('selected');
    });

    // Payment method selection
    $('.cl-payment-method-card').on('click', function() {
        $('.cl-payment-method-card').removeClass('selected');
        $(this).addClass('selected');
    });

    // Update totals when shipping changes
    $('input[name="shipping_method"]').on('change', function() {
        var shippingPrice = parseFloat($(this).data('price')) || 0;
        var total = subtotal - couponDiscount + shippingPrice;

        if (shippingPrice > 0) {
            $('#cl-shipping-cost').text(formatPrice(shippingPrice));
        } else {
            $('#cl-shipping-cost').text('<?php echo esc_js( __( 'חינם', 'commerce-layer' ) ); ?>');
        }

        $('#cl-final-total').text(formatPrice(total));
    });

    $form.on('submit', function(e) {
        e.preventDefault();

        var $btn = $form.find('.cl-btn-pay');
        $btn.prop('disabled', true).addClass('cl-loading');

        var shippingMethod = $form.find('[name="shipping_method"]:checked');
        var shippingIndex = shippingMethod.length ? shippingMethod.val() : '';
        var shippingPrice = shippingMethod.length ? parseFloat(shippingMethod.data('price')) || 0 : 0;

        // Combine first and last name
        var fullName = $form.find('[name="first_name"]').val() + ' ' + $form.find('[name="last_name"]').val();

        // Determine action based on mode
        var ajaxAction = isLeadMode ? 'cl_submit_lead' : 'cl_process_checkout';

        $.post(clFrontend.ajaxUrl, {
            action: ajaxAction,
            nonce: $form.find('[name="checkout_nonce"]').val(),
            name: fullName,
            email: $form.find('[name="email"]').val(),
            phone: $form.find('[name="phone"]').val(),
            address: $form.find('[name="address"]').val() + ' ' + $form.find('[name="address2"]').val(),
            city: $form.find('[name="city"]').val(),
            postcode: '',
            notes: $form.find('[name="notes"]').val(),
            shipping_method: shippingIndex,
            shipping_price: shippingPrice
        }, function(response) {
            if (response.success) {
                if (response.data.redirect_url) {
                    window.location.href = response.data.redirect_url;
                } else if (response.data.redirect) {
                    window.location.href = response.data.redirect_url;
                } else if (response.data.iframe) {
                    $iframe.attr('src', response.data.iframe_url);
                    $paymentContainer.show();
                }
            } else {
                alert(response.data.message || '<?php esc_html_e( 'שגיאה בעיבוד ההזמנה', 'commerce-layer' ); ?>');
                $btn.prop('disabled', false).removeClass('cl-loading');
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            $btn.prop('disabled', false).removeClass('cl-loading');
        });
    });

    // Close payment modal
    $('.cl-close-payment, .cl-payment-overlay').on('click', function() {
        $paymentContainer.hide();
        $iframe.attr('src', '');
        $form.find('.cl-btn-pay').prop('disabled', false).removeClass('cl-loading');
    });

    // Remove item from cart
    $('.cl-checkout-remove-item').on('click', function() {
        var $btn = $(this);
        var cartKey = $btn.data('cart-key');
        var $item = $btn.closest('.cl-summary-item');

        $btn.prop('disabled', true);

        $.post(clFrontend.ajaxUrl, {
            action: 'cl_remove_from_cart',
            nonce: clFrontend.nonce,
            cart_key: cartKey
        }, function(response) {
            if (response.success) {
                // Reload the page to update totals
                window.location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'שגיאה בהסרת המוצר', 'commerce-layer' ); ?>');
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            $btn.prop('disabled', false);
        });
    });

    // Apply coupon
    $('.cl-btn-coupon').on('click', function() {
        var $btn = $(this);
        var couponCode = $form.find('[name="coupon_code"]').val().trim();

        if (!couponCode) {
            alert('<?php esc_html_e( 'נא להזין קוד קופון', 'commerce-layer' ); ?>');
            return;
        }

        $btn.prop('disabled', true).text('<?php esc_html_e( 'בודק...', 'commerce-layer' ); ?>');

        $.post(clFrontend.ajaxUrl, {
            action: 'cl_apply_coupon',
            nonce: clFrontend.nonce,
            coupon_code: couponCode
        }, function(response) {
            if (response.success) {
                window.location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'קופון לא תקין', 'commerce-layer' ); ?>');
                $btn.prop('disabled', false).text('<?php esc_html_e( 'החל', 'commerce-layer' ); ?>');
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            $btn.prop('disabled', false).text('<?php esc_html_e( 'החל', 'commerce-layer' ); ?>');
        });
    });

    // Allow pressing Enter to apply coupon
    $form.find('[name="coupon_code"]').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('.cl-btn-coupon').click();
        }
    });

    // Remove coupon
    $('.cl-remove-coupon').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.post(clFrontend.ajaxUrl, {
            action: 'cl_remove_coupon',
            nonce: clFrontend.nonce
        }, function(response) {
            if (response.success) {
                window.location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'שגיאה בהסרת הקופון', 'commerce-layer' ); ?>');
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'שגיאת תקשורת', 'commerce-layer' ); ?>');
            $btn.prop('disabled', false);
        });
    });
});
</script>
