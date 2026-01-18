<?php
/**
 * Public Class
 *
 * Handles frontend functionality
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Public {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'the_content', array( $this, 'auto_inject_commerce' ), 20 );
        add_action( 'wp_footer', array( $this, 'render_floating_bar' ) );
        add_action( 'wp_footer', array( $this, 'render_side_cart' ) );
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Always load assets on frontend (for side cart)
        // Styles
        wp_enqueue_style(
            'cl-frontend',
            CL_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            CL_VERSION
        );

        // Scripts
        wp_enqueue_script(
            'cl-frontend',
            CL_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            CL_VERSION,
            true
        );

        // Localize
        wp_localize_script( 'cl-frontend', 'clFrontend', array(
            'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
            'nonce'                => wp_create_nonce( 'cl_ajax_nonce' ),
            'cartUrl'              => get_permalink( get_option( 'cl_cart_page_id' ) ),
            'checkoutUrl'          => get_permalink( get_option( 'cl_checkout_page_id' ) ),
            'currency'             => get_option( 'cl_currency_symbol', '₪' ),
            'freeShippingThreshold' => get_option( 'cl_free_shipping_threshold', 200 ),
            'strings'              => array(
                'addedToCart'      => __( 'הפריט נוסף לסל', 'commerce-layer' ),
                'error'            => __( 'שגיאה', 'commerce-layer' ),
                'selectVariant'    => __( 'בחר וריאציה', 'commerce-layer' ),
                'outOfStock'       => __( 'אזל מהמלאי', 'commerce-layer' ),
                'viewCart'         => __( 'צפה בסל', 'commerce-layer' ),
                'continueShopping' => __( 'המשך בקנייה', 'commerce-layer' ),
                'addMore'          => __( 'הוסף %s לקבלת משלוח חינם!', 'commerce-layer' ),
                'freeShipping'     => __( 'זכאי למשלוח חינם!', 'commerce-layer' ),
            ),
        ) );
    }

    /**
     * Check if we should load assets
     */
    private function should_load_assets() {
        // Always load on cart/checkout/thank-you pages
        if ( is_page( array(
            get_option( 'cl_cart_page_id' ),
            get_option( 'cl_checkout_page_id' ),
            get_option( 'cl_thank_you_page_id' ),
        ) ) ) {
            return true;
        }

        // Check if current post type is commerce enabled
        $post_type = get_post_type();
        if ( $post_type && CL_Core::is_commerce_enabled( $post_type ) ) {
            return true;
        }

        // Check for shortcodes
        global $post;
        if ( $post && has_shortcode( $post->post_content, 'commerce_box' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Auto inject commerce box into content
     */
    public function auto_inject_commerce( $content ) {
        global $post;

        // Skip if no post
        if ( ! $post ) {
            return $content;
        }

        // Skip if manual mode
        $display_mode = get_option( 'cl_display_mode', 'auto' );
        if ( 'shortcode' === $display_mode ) {
            return $content;
        }

        // Skip if not single view
        if ( ! is_singular() ) {
            return $content;
        }

        // Get current post ID
        $post_id = $post->ID;
        if ( ! $post_id ) {
            return $content;
        }

        // Prevent multiple injections for the same post
        static $already_injected = array();
        if ( isset( $already_injected[ $post_id ] ) ) {
            return $content;
        }

        // Check if post type is commerce enabled
        $post_type = get_post_type( $post_id );
        $enabled_post_types = CL_Core::get_enabled_post_types();

        if ( empty( $enabled_post_types ) || ! in_array( $post_type, $enabled_post_types, true ) ) {
            return $content;
        }

        // Check if commerce is enabled for this specific post
        $product = new CL_Product( $post_id );
        if ( ! $product->is_commerce_enabled() ) {
            return $content;
        }

        // Mark as injected BEFORE generating content to prevent recursion
        $already_injected[ $post_id ] = true;

        // Generate commerce box
        $commerce_box = $this->get_purchase_card_html( $product );

        // Insert based on position setting
        $position = get_option( 'cl_display_position', 'after_content' );

        if ( 'before_content' === $position ) {
            return $commerce_box . $content;
        }

        return $content . $commerce_box;
    }

    /**
     * Get purchase card HTML
     */
    public function get_purchase_card_html( $product ) {
        ob_start();
        include CL_PLUGIN_DIR . 'templates/purchase-card.php';
        return ob_get_clean();
    }

    /**
     * Render floating bar
     */
    public function render_floating_bar() {
        // Check if enabled
        if ( 'yes' !== get_option( 'cl_floating_bar_enabled', 'no' ) ) {
            return;
        }

        // Only on single pages with commerce enabled
        if ( ! is_singular() ) {
            return;
        }

        $post_type = get_post_type();
        if ( ! CL_Core::is_commerce_enabled( $post_type ) ) {
            return;
        }

        $product = new CL_Product( get_the_ID() );
        if ( ! $product->is_commerce_enabled() || ! $product->is_purchasable() ) {
            return;
        }

        include CL_PLUGIN_DIR . 'templates/floating-bar.php';
    }

    /**
     * Render side cart drawer in footer
     */
    public function render_side_cart() {
        include CL_PLUGIN_DIR . 'templates/side-cart.php';
    }
}
