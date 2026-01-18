<?php
/**
 * Purchase Card Template
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $product should be available from calling context
if ( ! isset( $product ) || ! $product instanceof CL_Product ) {
    return;
}

$has_variants = $product->has_variants();
$is_purchasable = $product->is_purchasable();
?>
<div class="cl-purchase-card" data-post-id="<?php echo esc_attr( $product->get_id() ); ?>">
    <!-- Price Display -->
    <div class="cl-price-section">
        <?php if ( $has_variants ) : ?>
            <?php
            $range = $product->get_price_range();
            if ( $range ) :
            ?>
                <div class="cl-price cl-price-range"><?php echo $range['html']; ?></div>
            <?php endif; ?>
        <?php elseif ( $product->get_price() !== false ) : ?>
            <?php echo $product->get_price_html(); ?>
        <?php else : ?>
            <div class="cl-price cl-no-price"><?php esc_html_e( 'מחיר לא הוגדר', 'commerce-layer' ); ?></div>
        <?php endif; ?>
    </div>

    <?php if ( $is_purchasable ) : ?>
        <!-- Stock Status -->
        <div class="cl-stock-status cl-in-stock">
            <span class="cl-stock-icon">✓</span>
            <?php esc_html_e( 'במלאי', 'commerce-layer' ); ?>
        </div>

        <!-- Purchase Form -->
        <?php echo CL_Purchase_Card::get_buttons_html( $product ); ?>
    <?php else : ?>
        <p class="cl-not-purchasable"><?php esc_html_e( 'הגדר מחיר כדי להפעיל רכישה', 'commerce-layer' ); ?></p>
    <?php endif; ?>

    <!-- Added to Cart Message -->
    <div class="cl-added-message" style="display: none;">
        <span class="cl-success-icon">✓</span>
        <span class="cl-message-text"><?php esc_html_e( 'הפריט נוסף לסל', 'commerce-layer' ); ?></span>
        <div class="cl-message-actions">
            <a href="<?php echo esc_url( get_permalink( get_option( 'cl_cart_page_id' ) ) ); ?>" class="cl-btn cl-btn-small">
                <?php esc_html_e( 'צפה בסל', 'commerce-layer' ); ?>
            </a>
            <button type="button" class="cl-btn cl-btn-small cl-btn-secondary cl-continue-shopping">
                <?php esc_html_e( 'המשך בקנייה', 'commerce-layer' ); ?>
            </button>
        </div>
    </div>
</div>
