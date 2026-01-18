<?php
/**
 * Floating Action Bar Template
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

$mode = $product->get_purchase_mode();
$has_variants = $product->has_variants();
?>
<div class="cl-floating-bar" data-post-id="<?php echo esc_attr( $product->get_id() ); ?>">
    <div class="cl-floating-bar-inner">
        <div class="cl-floating-price">
            <?php if ( $has_variants ) : ?>
                <?php
                $range = $product->get_price_range();
                if ( $range ) :
                ?>
                    <span class="cl-price"><?php echo $range['html']; ?></span>
                <?php endif; ?>
            <?php else : ?>
                <?php echo $product->get_price_html(); ?>
            <?php endif; ?>
        </div>

        <div class="cl-floating-actions">
            <?php if ( $has_variants ) : ?>
                <button type="button" class="cl-btn cl-btn-floating cl-open-variants">
                    <?php esc_html_e( 'בחר אפשרויות', 'commerce-layer' ); ?>
                </button>
            <?php else : ?>
                <?php if ( in_array( $mode, array( 'cart', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-btn-floating cl-floating-add-to-cart">
                        <?php echo esc_html( get_option( 'cl_add_to_cart_text', __( 'הוסף לסל', 'commerce-layer' ) ) ); ?>
                    </button>
                <?php endif; ?>

                <?php if ( in_array( $mode, array( 'buy_now', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-btn-floating cl-btn-secondary cl-floating-buy-now">
                        <?php echo esc_html( get_option( 'cl_buy_now_text', __( 'קנה עכשיו', 'commerce-layer' ) ) ); ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Variants Modal -->
    <?php if ( $has_variants ) : ?>
        <div class="cl-variants-modal" style="display: none;">
            <div class="cl-modal-overlay"></div>
            <div class="cl-modal-content">
                <button type="button" class="cl-modal-close">&times;</button>
                <h4><?php echo esc_html( $product->get_title() ); ?></h4>
                <?php echo CL_Purchase_Card::get_buttons_html( $product ); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
