<?php
/**
 * Purchase Card Class
 *
 * Handles the frontend purchase card rendering
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Purchase_Card {

    /**
     * Constructor
     */
    public function __construct() {
        // Register Gutenberg block
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( 'commerce-layer/purchase-card', array(
            'editor_script'   => 'cl-block-editor',
            'editor_style'    => 'cl-block-editor-style',
            'render_callback' => array( $this, 'render_block' ),
            'attributes'      => array(
                'postId' => array(
                    'type'    => 'number',
                    'default' => 0,
                ),
            ),
        ) );

        // Block editor assets
        wp_register_script(
            'cl-block-editor',
            CL_PLUGIN_URL . 'blocks/commerce-block/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
            CL_VERSION
        );

        wp_register_style(
            'cl-block-editor-style',
            CL_PLUGIN_URL . 'assets/css/block-editor.css',
            array(),
            CL_VERSION
        );
    }

    /**
     * Render block
     */
    public function render_block( $attributes ) {
        $post_id = isset( $attributes['postId'] ) && $attributes['postId'] ? $attributes['postId'] : get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() ) {
            return '';
        }

        ob_start();
        include CL_PLUGIN_DIR . 'templates/purchase-card.php';
        return ob_get_clean();
    }

    /**
     * Get purchase buttons HTML
     */
    public static function get_buttons_html( $product, $show_quantity = true ) {
        $mode = $product->get_purchase_mode();
        $min = $product->get_min_quantity();
        $max = $product->get_max_quantity();
        $post_id = $product->get_id();

        $has_variants = $product->has_variants();
        $variants = $has_variants ? $product->get_variants() : array();

        ob_start();
        ?>
        <div class="cl-purchase-form" data-post-id="<?php echo esc_attr( $post_id ); ?>">
            <?php if ( $has_variants && ! empty( $variants ) ) : ?>
                <!-- Variant Selection -->
                <div class="cl-variants-selection">
                    <?php
                    // Group variants by attributes
                    $attribute_options = array();
                    foreach ( $variants as $variant ) {
                        $attrs = $variant->get_attributes();
                        foreach ( $attrs as $attr_slug => $value ) {
                            if ( ! isset( $attribute_options[ $attr_slug ] ) ) {
                                $attribute_options[ $attr_slug ] = array();
                            }
                            if ( ! in_array( $value, $attribute_options[ $attr_slug ] ) ) {
                                $attribute_options[ $attr_slug ][] = $value;
                            }
                        }
                    }

                    foreach ( $attribute_options as $attr_slug => $options ) :
                        // Get attribute name from global attributes
                        global $wpdb;
                        $attr_name = $wpdb->get_var( $wpdb->prepare(
                            "SELECT name FROM {$wpdb->prefix}cl_attributes WHERE slug = %s",
                            $attr_slug
                        ) );
                    ?>
                        <div class="cl-variant-select" data-attribute="<?php echo esc_attr( $attr_slug ); ?>">
                            <label><?php echo esc_html( $attr_name ?: $attr_slug ); ?></label>
                            <select name="variant_<?php echo esc_attr( $attr_slug ); ?>" class="cl-variant-dropdown" required>
                                <option value=""><?php esc_html_e( 'בחר', 'commerce-layer' ); ?></option>
                                <?php foreach ( $options as $option ) : ?>
                                    <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <input type="hidden" name="variant_id" class="cl-variant-id" value="">

                    <!-- Variants data for JS -->
                    <script type="application/json" class="cl-variants-data">
                        <?php
                        $variants_data = array();
                        foreach ( $variants as $variant ) {
                            $variants_data[] = $variant->get_data();
                        }
                        echo json_encode( $variants_data );
                        ?>
                    </script>
                </div>
            <?php endif; ?>

            <?php if ( $show_quantity ) : ?>
                <!-- Quantity -->
                <div class="cl-quantity-field">
                    <label><?php esc_html_e( 'כמות', 'commerce-layer' ); ?></label>
                    <div class="cl-quantity-input">
                        <button type="button" class="cl-qty-btn cl-qty-minus">−</button>
                        <input type="number" name="quantity" value="<?php echo esc_attr( $min ); ?>"
                               min="<?php echo esc_attr( $min ); ?>"
                               <?php echo $max > 0 ? 'max="' . esc_attr( $max ) . '"' : ''; ?>
                               class="cl-quantity">
                        <button type="button" class="cl-qty-btn cl-qty-plus">+</button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Buttons -->
            <div class="cl-buttons">
                <?php if ( in_array( $mode, array( 'cart', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-add-to-cart-btn">
                        <?php echo esc_html( CL_Core::get_translatable_option( 'cl_add_to_cart_text', 'הוסף לסל' ) ); ?>
                    </button>
                <?php endif; ?>

                <?php if ( in_array( $mode, array( 'buy_now', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-btn-secondary cl-buy-now-btn">
                        <?php echo esc_html( CL_Core::get_translatable_option( 'cl_buy_now_text', 'קנה עכשיו' ) ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render only the buttons (for custom injection)
     */
    public function render_buttons_only( $post_id, $show_price = false ) {
        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() ) {
            return;
        }

        if ( $show_price ) {
            echo self::get_price_and_buttons_html( $product );
        } else {
            echo self::get_buttons_html( $product, true );
        }
    }

    /**
     * Get price and buttons HTML (for custom injection with prices)
     */
    public static function get_price_and_buttons_html( $product ) {
        $mode = $product->get_purchase_mode();
        $min = $product->get_min_quantity();
        $max = $product->get_max_quantity();
        $post_id = $product->get_id();

        $has_variants = $product->has_variants();
        $variants = $has_variants ? $product->get_variants() : array();

        ob_start();
        ?>
        <div class="cl-purchase-form cl-injected-purchase-form" data-post-id="<?php echo esc_attr( $post_id ); ?>">
            <!-- Price Display -->
            <div class="cl-injected-price">
                <?php
                if ( $has_variants ) {
                    $range = $product->get_price_range();
                    if ( $range ) {
                        echo '<span class="cl-price-range">' . $range['html'] . '</span>';
                    }
                } else {
                    $price = $product->get_price();
                    $sale_price = $product->get_sale_price();
                    $regular_price = $product->get_regular_price();

                    if ( $sale_price && $sale_price < $regular_price ) {
                        ?>
                        <span class="cl-price-wrapper cl-has-sale">
                            <span class="cl-original-price"><?php echo CL_Core::format_price( $regular_price ); ?></span>
                            <span class="cl-sale-price"><?php echo CL_Core::format_price( $sale_price ); ?></span>
                        </span>
                        <?php
                    } else {
                        ?>
                        <span class="cl-price-wrapper">
                            <span class="cl-current-price"><?php echo CL_Core::format_price( $price ); ?></span>
                        </span>
                        <?php
                    }
                }
                ?>
            </div>

            <?php if ( $has_variants && ! empty( $variants ) ) : ?>
                <!-- Variant Selection -->
                <div class="cl-variants-selection">
                    <?php
                    // Group variants by attributes
                    $attribute_options = array();
                    foreach ( $variants as $variant ) {
                        $attrs = $variant->get_attributes();
                        foreach ( $attrs as $attr_slug => $value ) {
                            if ( ! isset( $attribute_options[ $attr_slug ] ) ) {
                                $attribute_options[ $attr_slug ] = array();
                            }
                            if ( ! in_array( $value, $attribute_options[ $attr_slug ] ) ) {
                                $attribute_options[ $attr_slug ][] = $value;
                            }
                        }
                    }

                    foreach ( $attribute_options as $attr_slug => $options ) :
                        // Get attribute name from global attributes
                        global $wpdb;
                        $attr_name = $wpdb->get_var( $wpdb->prepare(
                            "SELECT name FROM {$wpdb->prefix}cl_attributes WHERE slug = %s",
                            $attr_slug
                        ) );
                    ?>
                        <div class="cl-variant-select" data-attribute="<?php echo esc_attr( $attr_slug ); ?>">
                            <label><?php echo esc_html( $attr_name ?: $attr_slug ); ?></label>
                            <select name="variant_<?php echo esc_attr( $attr_slug ); ?>" class="cl-variant-dropdown" required>
                                <option value=""><?php esc_html_e( 'בחר', 'commerce-layer' ); ?></option>
                                <?php foreach ( $options as $option ) : ?>
                                    <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <input type="hidden" name="variant_id" class="cl-variant-id" value="">

                    <!-- Variants data for JS -->
                    <script type="application/json" class="cl-variants-data">
                        <?php
                        $variants_data = array();
                        foreach ( $variants as $variant ) {
                            $variants_data[] = $variant->get_data();
                        }
                        echo json_encode( $variants_data );
                        ?>
                    </script>
                </div>
            <?php endif; ?>

            <!-- Quantity -->
            <div class="cl-quantity-field">
                <label><?php esc_html_e( 'כמות', 'commerce-layer' ); ?></label>
                <div class="cl-quantity-input">
                    <button type="button" class="cl-qty-btn cl-qty-minus">−</button>
                    <input type="number" name="quantity" value="<?php echo esc_attr( $min ); ?>"
                           min="<?php echo esc_attr( $min ); ?>"
                           <?php echo $max > 0 ? 'max="' . esc_attr( $max ) . '"' : ''; ?>
                           class="cl-quantity">
                    <button type="button" class="cl-qty-btn cl-qty-plus">+</button>
                </div>
            </div>

            <!-- Buttons -->
            <div class="cl-buttons">
                <?php if ( in_array( $mode, array( 'cart', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-add-to-cart-btn">
                        <?php echo esc_html( CL_Core::get_translatable_option( 'cl_add_to_cart_text', 'הוסף לסל' ) ); ?>
                    </button>
                <?php endif; ?>

                <?php if ( in_array( $mode, array( 'buy_now', 'both' ) ) ) : ?>
                    <button type="button" class="cl-btn cl-btn-secondary cl-buy-now-btn">
                        <?php echo esc_html( CL_Core::get_translatable_option( 'cl_buy_now_text', 'קנה עכשיו' ) ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
