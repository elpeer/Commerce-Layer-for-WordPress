<?php
/**
 * Cart Template
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
$checkout_url = get_permalink( get_option( 'cl_checkout_page_id' ) );
?>
<div class="cl-cart">
    <div class="cl-cart-items">
        <table class="cl-cart-table">
            <thead>
                <tr>
                    <th class="cl-col-product"><?php esc_html_e( 'מוצר', 'commerce-layer' ); ?></th>
                    <th class="cl-col-price"><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                    <th class="cl-col-quantity"><?php esc_html_e( 'כמות', 'commerce-layer' ); ?></th>
                    <th class="cl-col-total"><?php esc_html_e( 'סה"כ', 'commerce-layer' ); ?></th>
                    <th class="cl-col-remove"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $cart_key => $item ) : ?>
                    <tr class="cl-cart-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                        <td class="cl-col-product">
                            <div class="cl-product-info">
                                <?php
                                $thumbnail = get_the_post_thumbnail_url( $item['post_id'], 'thumbnail' );
                                if ( $thumbnail ) :
                                ?>
                                    <img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" class="cl-product-thumb">
                                <?php endif; ?>
                                <div class="cl-product-details">
                                    <a href="<?php echo esc_url( get_permalink( $item['post_id'] ) ); ?>" class="cl-product-name">
                                        <?php echo esc_html( $item['name'] ); ?>
                                    </a>
                                    <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                        <span class="cl-variant-name"><?php echo esc_html( $item['variant_name'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="cl-col-price" data-label="<?php esc_attr_e( 'מחיר', 'commerce-layer' ); ?>">
                            <?php echo CL_Core::format_price( $item['price'] ); ?>
                        </td>
                        <td class="cl-col-quantity" data-label="<?php esc_attr_e( 'כמות', 'commerce-layer' ); ?>">
                            <div class="cl-quantity-input">
                                <button type="button" class="cl-qty-btn cl-qty-minus">−</button>
                                <input type="number" value="<?php echo esc_attr( $item['quantity'] ); ?>"
                                       min="1" class="cl-cart-quantity"
                                       data-cart-key="<?php echo esc_attr( $cart_key ); ?>">
                                <button type="button" class="cl-qty-btn cl-qty-plus">+</button>
                            </div>
                        </td>
                        <td class="cl-col-total" data-label="<?php esc_attr_e( 'סה"כ', 'commerce-layer' ); ?>">
                            <span class="cl-line-total"><?php echo CL_Core::format_price( $item['price'] * $item['quantity'] ); ?></span>
                        </td>
                        <td class="cl-col-remove">
                            <button type="button" class="cl-remove-item" data-cart-key="<?php echo esc_attr( $cart_key ); ?>" title="<?php esc_attr_e( 'הסר', 'commerce-layer' ); ?>">
                                &times;
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="cl-cart-summary">
        <div class="cl-summary-row cl-subtotal">
            <span><?php esc_html_e( 'סיכום ביניים:', 'commerce-layer' ); ?></span>
            <span class="cl-subtotal-amount"><?php echo CL_Core::format_price( $cart->get_subtotal() ); ?></span>
        </div>

        <div class="cl-summary-row cl-total">
            <span><?php esc_html_e( 'סה"כ:', 'commerce-layer' ); ?></span>
            <span class="cl-total-amount"><?php echo CL_Core::format_price( $cart->get_total() ); ?></span>
        </div>

        <div class="cl-cart-actions">
            <a href="<?php echo esc_url( $checkout_url ); ?>" class="cl-btn cl-btn-checkout">
                <?php esc_html_e( 'המשך לתשלום', 'commerce-layer' ); ?>
            </a>
            <a href="<?php echo esc_url( home_url() ); ?>" class="cl-btn cl-btn-secondary cl-continue">
                <?php esc_html_e( 'המשך בקנייה', 'commerce-layer' ); ?>
            </a>
        </div>
    </div>
</div>
