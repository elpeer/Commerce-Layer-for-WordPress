<?php
/**
 * Side Cart Template
 * Slide-out cart drawer
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cart = CL_Cart::get_instance();
$items = $cart->get_items();
$totals = $cart->get_totals();
$items_count = $cart->get_items_count();
$free_shipping_threshold = floatval( get_option( 'cl_free_shipping_threshold', 200 ) );
$currency_symbol = get_option( 'cl_currency_symbol', '₪' );
?>

<div class="cl-side-cart" id="cl-side-cart">
    <div class="cl-side-cart-overlay"></div>
    <div class="cl-side-cart-drawer">
        <!-- Header -->
        <div class="cl-side-cart-header">
            <h3 class="cl-side-cart-title">
                <?php esc_html_e( 'סל קניות', 'commerce-layer' ); ?>
                <span class="cl-side-cart-count">(<?php echo esc_html( $items_count ); ?>)</span>
            </h3>
            <button type="button" class="cl-side-cart-close" aria-label="<?php esc_attr_e( 'סגור', 'commerce-layer' ); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <?php if ( $free_shipping_threshold > 0 ) :
            $remaining = max( 0, $free_shipping_threshold - $totals['subtotal'] );
            $progress = min( 100, ( $totals['subtotal'] / $free_shipping_threshold ) * 100 );
        ?>
        <!-- Free Shipping Progress -->
        <div class="cl-shipping-progress">
            <div class="cl-shipping-bar">
                <div class="cl-shipping-bar-fill" style="width: <?php echo esc_attr( $progress ); ?>%"></div>
                <div class="cl-shipping-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
            </div>
            <?php if ( $remaining > 0 ) : ?>
                <p class="cl-shipping-text">
                    <?php printf(
                        esc_html__( 'הוסף %s לקבלת משלוח חינם!', 'commerce-layer' ),
                        '<strong>' . CL_Core::format_price( $remaining ) . '</strong>'
                    ); ?>
                </p>
            <?php else : ?>
                <p class="cl-shipping-text cl-shipping-free">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php esc_html_e( 'זכאי למשלוח חינם!', 'commerce-layer' ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Urgency Timer (optional) -->
        <?php if ( 'yes' === get_option( 'cl_cart_urgency_timer', 'no' ) ) : ?>
        <div class="cl-urgency-notice">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L12 6M12 18L12 22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12L6 12M18 12L22 12M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93"></path>
            </svg>
            <span><?php esc_html_e( 'המוצרים מוגבלים, השלם רכישה תוך', 'commerce-layer' ); ?></span>
            <span class="cl-urgency-timer" data-minutes="15">15:00</span>
        </div>
        <?php endif; ?>

        <!-- Cart Items -->
        <div class="cl-side-cart-items" id="cl-side-cart-items">
            <?php if ( empty( $items ) ) : ?>
                <div class="cl-side-cart-empty">
                    <div class="cl-empty-cart-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <p><?php esc_html_e( 'הסל שלך ריק', 'commerce-layer' ); ?></p>
                    <button type="button" class="cl-btn cl-btn-secondary cl-side-cart-close">
                        <?php esc_html_e( 'המשך בקנייה', 'commerce-layer' ); ?>
                    </button>
                </div>
            <?php else : ?>
                <?php foreach ( $items as $cart_key => $item ) :
                    $product = new CL_Product( $item['post_id'] );
                    $variant = $item['variant_id'] ? new CL_Variant( $item['variant_id'] ) : null;
                    $price = $variant ? $variant->get_price() : $product->get_price();
                    $line_total = $price * $item['quantity'];
                    $thumbnail = $product->get_thumbnail( 'thumbnail' );
                ?>
                    <div class="cl-side-cart-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                        <div class="cl-side-cart-item-image">
                            <?php if ( $thumbnail ) : ?>
                                <img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $product->get_title() ); ?>">
                            <?php else : ?>
                                <div class="cl-no-image">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="cl-side-cart-item-details">
                            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="cl-side-cart-item-title">
                                <?php echo esc_html( $product->get_title() ); ?>
                            </a>
                            <?php if ( $variant && ! empty( $item['variant_name'] ) ) : ?>
                                <span class="cl-side-cart-item-variant"><?php echo esc_html( $item['variant_name'] ); ?></span>
                            <?php endif; ?>
                            <div class="cl-side-cart-item-price">
                                <?php echo CL_Core::format_price( $price ); ?>
                            </div>
                            <div class="cl-side-cart-item-qty">
                                <button type="button" class="cl-qty-btn cl-qty-minus" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">−</button>
                                <input type="number" class="cl-cart-quantity" value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1" max="99" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                                <button type="button" class="cl-qty-btn cl-qty-plus" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">+</button>
                            </div>
                        </div>
                        <div class="cl-side-cart-item-actions">
                            <button type="button" class="cl-side-cart-remove" data-cart-key="<?php echo esc_attr( $cart_key ); ?>" aria-label="<?php esc_attr_e( 'הסר', 'commerce-layer' ); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <?php if ( ! empty( $items ) ) : ?>
        <div class="cl-side-cart-footer">
            <?php
            $discount = $totals['discount'] ?? 0;
            if ( $discount > 0 ) :
            ?>
            <div class="cl-side-cart-discount">
                <span><?php esc_html_e( 'הנחה', 'commerce-layer' ); ?></span>
                <span class="cl-discount-amount">-<?php echo CL_Core::format_price( $discount ); ?></span>
            </div>
            <?php endif; ?>

            <div class="cl-side-cart-subtotal">
                <span><?php esc_html_e( 'סה"כ', 'commerce-layer' ); ?></span>
                <span class="cl-subtotal-amount"><?php echo CL_Core::format_price( $totals['total'] ); ?></span>
            </div>

            <div class="cl-side-cart-buttons">
                <a href="<?php echo esc_url( get_permalink( get_option( 'cl_cart_page_id' ) ) ); ?>" class="cl-btn cl-btn-secondary cl-view-cart-btn">
                    <?php esc_html_e( 'צפה בסל', 'commerce-layer' ); ?>
                </a>
                <a href="<?php echo esc_url( get_permalink( get_option( 'cl_checkout_page_id' ) ) ); ?>" class="cl-btn cl-checkout-btn">
                    <?php esc_html_e( 'לתשלום', 'commerce-layer' ); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
