<?php
/**
 * Shortcodes Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_shortcodes();
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode( 'commerce_box', array( $this, 'commerce_box' ) );
        add_shortcode( 'commerce_price', array( $this, 'commerce_price' ) );
        add_shortcode( 'commerce_add_to_cart', array( $this, 'commerce_add_to_cart' ) );
        add_shortcode( 'commerce_buy_now', array( $this, 'commerce_buy_now' ) );
        add_shortcode( 'cl_cart', array( $this, 'cart_page' ) );
        add_shortcode( 'cl_checkout', array( $this, 'checkout_page' ) );
        add_shortcode( 'cl_thank_you', array( $this, 'thank_you_page' ) );
        add_shortcode( 'cl_mini_cart', array( $this, 'mini_cart' ) );
    }

    /**
     * Commerce box shortcode - full purchase card
     */
    public function commerce_box( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts );

        $post_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() ) {
            return '';
        }

        ob_start();
        $this->render_purchase_card( $product );
        return ob_get_clean();
    }

    /**
     * Commerce price shortcode
     */
    public function commerce_price( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts );

        $post_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() ) {
            return '';
        }

        if ( $product->has_variants() ) {
            $range = $product->get_price_range();
            if ( $range ) {
                return '<span class="cl-price-range">' . $range['html'] . '</span>';
            }
        }

        return $product->get_price_html();
    }

    /**
     * Add to cart button shortcode
     */
    public function commerce_add_to_cart( $atts ) {
        $atts = shortcode_atts( array(
            'id'    => 0,
            'text'  => '',
            'class' => '',
        ), $atts );

        $post_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() || ! $product->is_purchasable() ) {
            return '';
        }

        $mode = $product->get_purchase_mode();
        if ( 'buy_now' === $mode ) {
            return ''; // Only buy now available
        }

        $text = $atts['text'] ?: get_option( 'cl_add_to_cart_text', __( 'הוסף לסל', 'commerce-layer' ) );
        $class = 'cl-btn cl-add-to-cart-btn ' . sanitize_html_class( $atts['class'] );

        return sprintf(
            '<div class="cl-shortcode-btn-wrap" data-post-id="%d"><button type="button" class="%s" data-post-id="%d">%s</button></div>',
            $post_id,
            esc_attr( $class ),
            $post_id,
            esc_html( $text )
        );
    }

    /**
     * Buy now button shortcode
     */
    public function commerce_buy_now( $atts ) {
        $atts = shortcode_atts( array(
            'id'    => 0,
            'text'  => '',
            'class' => '',
        ), $atts );

        $post_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $product = new CL_Product( $post_id );

        if ( ! $product->is_commerce_enabled() || ! $product->is_purchasable() ) {
            return '';
        }

        $mode = $product->get_purchase_mode();
        if ( 'cart' === $mode ) {
            return ''; // Only cart available
        }

        $text = $atts['text'] ?: get_option( 'cl_buy_now_text', __( 'קנה עכשיו', 'commerce-layer' ) );
        $class = 'cl-btn cl-btn-secondary cl-buy-now-btn ' . sanitize_html_class( $atts['class'] );

        return sprintf(
            '<div class="cl-shortcode-btn-wrap" data-post-id="%d"><button type="button" class="%s" data-post-id="%d">%s</button></div>',
            $post_id,
            esc_attr( $class ),
            $post_id,
            esc_html( $text )
        );
    }

    /**
     * Cart page shortcode
     */
    public function cart_page( $atts ) {
        $cart = CL_Cart::get_instance();

        ob_start();

        if ( $cart->is_empty() ) {
            $this->render_empty_cart();
        } else {
            $this->render_cart( $cart );
        }

        return ob_get_clean();
    }

    /**
     * Checkout page shortcode
     */
    public function checkout_page( $atts ) {
        $cart = CL_Cart::get_instance();

        ob_start();

        if ( $cart->is_empty() ) {
            $this->render_empty_cart();
        } else {
            $this->render_checkout( $cart );
        }

        return ob_get_clean();
    }

    /**
     * Thank you page shortcode
     */
    public function thank_you_page( $atts ) {
        $order_number = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : '';

        ob_start();

        if ( empty( $order_number ) ) {
            echo '<div class="cl-thank-you cl-no-order">';
            echo '<p>' . esc_html__( 'לא נמצאה הזמנה', 'commerce-layer' ) . '</p>';
            echo '</div>';
        } else {
            $order = CL_Order::get_by_order_number( $order_number );

            if ( ! $order ) {
                echo '<div class="cl-thank-you cl-no-order">';
                echo '<p>' . esc_html__( 'הזמנה לא נמצאה', 'commerce-layer' ) . '</p>';
                echo '</div>';
            } else {
                $this->render_thank_you( $order );
            }
        }

        return ob_get_clean();
    }

    /**
     * Mini cart shortcode
     */
    public function mini_cart( $atts ) {
        $atts = shortcode_atts( array(
            'icon' => 'true',
        ), $atts );

        $cart = CL_Cart::get_instance();
        $cart_url = get_permalink( get_option( 'cl_cart_page_id' ) );

        ob_start();
        ?>
        <div class="cl-mini-cart">
            <a href="<?php echo esc_url( $cart_url ); ?>" class="cl-mini-cart-link">
                <?php if ( 'true' === $atts['icon'] ) : ?>
                    <span class="cl-cart-icon">🛒</span>
                <?php endif; ?>
                <span class="cl-cart-count"><?php echo $cart->get_items_count(); ?></span>
                <span class="cl-cart-total"><?php echo CL_Core::format_price( $cart->get_total() ); ?></span>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render purchase card
     */
    private function render_purchase_card( $product ) {
        $template = CL_PLUGIN_DIR . 'templates/purchase-card.php';

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render empty cart
     */
    private function render_empty_cart() {
        ?>
        <div class="cl-cart cl-cart-empty">
            <div class="cl-empty-cart-message">
                <span class="cl-empty-icon">🛒</span>
                <h3><?php esc_html_e( 'הסל שלך ריק', 'commerce-layer' ); ?></h3>
                <p><?php esc_html_e( 'לא נמצאו פריטים בסל הקניות שלך.', 'commerce-layer' ); ?></p>
                <a href="<?php echo esc_url( home_url() ); ?>" class="cl-btn cl-btn-primary">
                    <?php esc_html_e( 'חזרה לאתר', 'commerce-layer' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render cart
     */
    private function render_cart( $cart ) {
        $template = CL_PLUGIN_DIR . 'templates/cart.php';

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render checkout
     */
    private function render_checkout( $cart ) {
        $template = CL_PLUGIN_DIR . 'templates/checkout.php';

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render thank you page
     */
    private function render_thank_you( $order ) {
        $template = CL_PLUGIN_DIR . 'templates/thank-you.php';

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}
