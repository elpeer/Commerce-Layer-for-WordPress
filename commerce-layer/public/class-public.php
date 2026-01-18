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
        add_action( 'wp_head', array( $this, 'output_dynamic_styles' ), 100 );
        add_filter( 'the_content', array( $this, 'auto_inject_commerce' ), 20 );
        add_action( 'wp_footer', array( $this, 'render_floating_bar' ) );
        add_action( 'wp_footer', array( $this, 'render_side_cart' ) );
        add_action( 'wp_footer', array( $this, 'render_floating_cart_icon' ) );
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
        // Check if enabled (default: yes)
        if ( 'yes' !== get_option( 'cl_floating_bar_enabled', 'yes' ) ) {
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

    /**
     * Render floating cart icon
     */
    public function render_floating_cart_icon() {
        // Check if enabled
        if ( 'yes' !== get_option( 'cl_floating_cart_icon_enabled', 'yes' ) ) {
            return;
        }

        // Skip on admin
        if ( is_admin() ) {
            return;
        }

        $cart = CL_Cart::get_instance();
        $count = $cart->get_items_count();

        // Get position settings
        $desktop_position = get_option( 'cl_cart_icon_desktop_position', 'top-left' );
        $mobile_position  = get_option( 'cl_cart_icon_mobile_position', 'bottom-right' );
        $desktop_offset   = absint( get_option( 'cl_cart_icon_desktop_offset', 20 ) );
        $mobile_offset    = absint( get_option( 'cl_cart_icon_mobile_offset', 20 ) );

        // Build inline styles for offsets
        $desktop_style = $this->get_position_style( $desktop_position, $desktop_offset );
        $mobile_style  = $this->get_position_style( $mobile_position, $mobile_offset );
        ?>
        <div class="cl-floating-cart-icon cl-pos-desktop-<?php echo esc_attr( $desktop_position ); ?> cl-pos-mobile-<?php echo esc_attr( $mobile_position ); ?>"
             data-desktop-offset="<?php echo esc_attr( $desktop_offset ); ?>"
             data-mobile-offset="<?php echo esc_attr( $mobile_offset ); ?>"
             style="--cl-desktop-offset: <?php echo esc_attr( $desktop_offset ); ?>px; --cl-mobile-offset: <?php echo esc_attr( $mobile_offset ); ?>px;">
            <button type="button" class="cl-floating-cart-btn cl-open-side-cart" aria-label="<?php esc_attr_e( 'פתח סל קניות', 'commerce-layer' ); ?>">
                <svg class="cl-floating-cart-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cl-floating-cart-count cl-cart-count"><?php echo esc_html( $count ); ?></span>
            </button>
        </div>
        <?php
    }

    /**
     * Get position style based on position string
     */
    private function get_position_style( $position, $offset ) {
        $styles = array();

        switch ( $position ) {
            case 'top-left':
                $styles = array( 'top' => $offset . 'px', 'left' => $offset . 'px' );
                break;
            case 'top-center':
                $styles = array( 'top' => $offset . 'px', 'left' => '50%', 'transform' => 'translateX(-50%)' );
                break;
            case 'top-right':
                $styles = array( 'top' => $offset . 'px', 'right' => $offset . 'px' );
                break;
            case 'bottom-left':
                $styles = array( 'bottom' => $offset . 'px', 'left' => $offset . 'px' );
                break;
            case 'bottom-center':
                $styles = array( 'bottom' => $offset . 'px', 'left' => '50%', 'transform' => 'translateX(-50%)' );
                break;
            case 'bottom-right':
                $styles = array( 'bottom' => $offset . 'px', 'right' => $offset . 'px' );
                break;
        }

        return $styles;
    }

    /**
     * Output dynamic styles based on settings
     */
    public function output_dynamic_styles() {
        $theme_preset = get_option( 'cl_theme_preset', 'modern' );

        // Get colors based on preset
        $colors = $this->get_theme_colors( $theme_preset );

        // Get button settings
        $button_corners = get_option( 'cl_button_corners', 'rounded' );
        $button_style   = get_option( 'cl_button_style', 'gradient' );

        // Get button-specific colors (only custom when theme is custom)
        if ( 'custom' === $theme_preset ) {
            $add_to_cart_bg    = get_option( 'cl_add_to_cart_bg', '#2563eb' );
            $add_to_cart_text  = get_option( 'cl_add_to_cart_text_color', '#ffffff' );
            $buy_now_bg        = get_option( 'cl_buy_now_bg', '#10b981' );
            $buy_now_text      = get_option( 'cl_buy_now_text_color', '#ffffff' );
            $cart_icon_color   = get_option( 'cl_cart_icon_color', '#2563eb' );
            $cart_icon_bg      = get_option( 'cl_cart_icon_bg', '#ffffff' );
            $cart_icon_badge_bg = get_option( 'cl_cart_icon_badge_bg', '#ef4444' );
        } else {
            // Use theme colors for buttons
            $add_to_cart_bg    = $colors['primary'];
            $add_to_cart_text  = '#ffffff';
            $buy_now_bg        = $colors['success'];
            $buy_now_text      = '#ffffff';
            $cart_icon_color   = $colors['primary'];
            $cart_icon_bg      = '#ffffff';
            $cart_icon_badge_bg = $colors['danger'];
        }

        // Get side cart settings
        $side_cart_width = absint( get_option( 'cl_side_cart_width', 420 ) );
        $side_cart_side  = get_option( 'cl_side_cart_side', 'right' );

        // Button border radius based on corners setting
        $border_radius = '8px';
        if ( 'square' === $button_corners ) {
            $border_radius = '0';
        } elseif ( 'pill' === $button_corners ) {
            $border_radius = '50px';
        }

        // Start output
        echo '<style id="cl-dynamic-styles">' . "\n";
        echo ':root {' . "\n";

        // Color variables
        echo '  --cl-color-primary: ' . esc_attr( $colors['primary'] ) . ';' . "\n";
        echo '  --cl-color-primary-dark: ' . esc_attr( $colors['primary_dark'] ) . ';' . "\n";
        echo '  --cl-color-secondary: ' . esc_attr( $colors['secondary'] ) . ';' . "\n";
        echo '  --cl-color-text: ' . esc_attr( $colors['text'] ) . ';' . "\n";
        echo '  --cl-color-background: ' . esc_attr( $colors['background'] ) . ';' . "\n";
        echo '  --cl-color-success: ' . esc_attr( $colors['success'] ) . ';' . "\n";
        echo '  --cl-color-danger: ' . esc_attr( $colors['danger'] ) . ';' . "\n";

        // Button variables
        echo '  --cl-button-radius: ' . esc_attr( $border_radius ) . ';' . "\n";

        // Button-specific colors
        echo '  --cl-add-to-cart-bg: ' . esc_attr( $add_to_cart_bg ) . ';' . "\n";
        echo '  --cl-add-to-cart-text: ' . esc_attr( $add_to_cart_text ) . ';' . "\n";
        echo '  --cl-buy-now-bg: ' . esc_attr( $buy_now_bg ) . ';' . "\n";
        echo '  --cl-buy-now-text: ' . esc_attr( $buy_now_text ) . ';' . "\n";

        // Cart icon colors
        echo '  --cl-cart-icon-color: ' . esc_attr( $cart_icon_color ) . ';' . "\n";
        echo '  --cl-cart-icon-bg: ' . esc_attr( $cart_icon_bg ) . ';' . "\n";
        echo '  --cl-cart-icon-badge-bg: ' . esc_attr( $cart_icon_badge_bg ) . ';' . "\n";

        // Side cart variables
        echo '  --cl-side-cart-width: ' . esc_attr( $side_cart_width ) . 'px;' . "\n";

        echo '}' . "\n";

        // Button style
        echo '.cl-btn {' . "\n";
        echo '  border-radius: var(--cl-button-radius);' . "\n";
        echo '}' . "\n";

        // Add to Cart button specific styles
        echo '.cl-add-to-cart-btn { background: var(--cl-add-to-cart-bg) !important; color: var(--cl-add-to-cart-text) !important; }' . "\n";
        echo '.cl-add-to-cart-btn:hover { filter: brightness(0.9); }' . "\n";

        // Buy Now button specific styles
        echo '.cl-buy-now-btn { background: var(--cl-buy-now-bg) !important; color: var(--cl-buy-now-text) !important; }' . "\n";
        echo '.cl-buy-now-btn:hover { filter: brightness(0.9); }' . "\n";

        // Cart icon styles
        echo '.cl-floating-cart-btn { background: var(--cl-cart-icon-bg); color: var(--cl-cart-icon-color); }' . "\n";
        echo '.cl-floating-cart-count { background: var(--cl-cart-icon-badge-bg); }' . "\n";
        echo '.cl-cart-icon-link { color: var(--cl-cart-icon-color); }' . "\n";
        echo '.cl-cart-icon-link .cl-cart-badge { background: var(--cl-cart-icon-badge-bg); }' . "\n";

        if ( 'filled' === $button_style ) {
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn) { background: var(--cl-color-primary); border: none; }' . "\n";
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn):hover { background: var(--cl-color-primary-dark); }' . "\n";
        } elseif ( 'outline' === $button_style ) {
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn) { background: transparent; border: 2px solid var(--cl-color-primary); color: var(--cl-color-primary); }' . "\n";
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn):hover { background: var(--cl-color-primary); color: #fff; }' . "\n";
        } elseif ( 'gradient' === $button_style ) {
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn) { background: linear-gradient(135deg, var(--cl-color-primary), var(--cl-color-primary-dark)); border: none; }' . "\n";
            echo '.cl-btn:not(.cl-add-to-cart-btn):not(.cl-buy-now-btn):hover { background: linear-gradient(135deg, var(--cl-color-primary-dark), var(--cl-color-primary)); }' . "\n";
        }

        // Side cart width
        echo '.cl-side-cart-drawer { width: var(--cl-side-cart-width); max-width: 100vw; }' . "\n";

        // Side cart position (left or right)
        if ( 'left' === $side_cart_side ) {
            echo '.cl-side-cart-drawer { right: auto !important; left: 0 !important; transform: translateX(-100%) !important; }' . "\n";
            echo '.cl-side-cart.cl-side-cart-open .cl-side-cart-drawer { transform: translateX(0) !important; }' . "\n";
        } elseif ( 'right' === $side_cart_side ) {
            echo '.cl-side-cart-drawer { left: auto !important; right: 0 !important; transform: translateX(100%) !important; }' . "\n";
            echo '.cl-side-cart.cl-side-cart-open .cl-side-cart-drawer { transform: translateX(0) !important; }' . "\n";
        }

        // Custom CSS
        $custom_css = get_option( 'cl_custom_css', '' );
        if ( ! empty( $custom_css ) ) {
            echo '/* Custom CSS */' . "\n";
            echo wp_strip_all_tags( $custom_css ) . "\n";
        }

        echo '</style>' . "\n";
    }

    /**
     * Get theme colors based on preset
     */
    private function get_theme_colors( $preset ) {
        // Default modern theme colors
        $colors = array(
            'primary'      => '#2563eb',
            'primary_dark' => '#1e40af',
            'secondary'    => '#64748b',
            'text'         => '#1e293b',
            'background'   => '#ffffff',
            'success'      => '#10b981',
            'danger'       => '#ef4444',
        );

        if ( 'classic' === $preset ) {
            // Classic theme - black/white/gray
            $colors = array(
                'primary'      => '#1a1a1a',
                'primary_dark' => '#000000',
                'secondary'    => '#6b7280',
                'text'         => '#111827',
                'background'   => '#ffffff',
                'success'      => '#059669',
                'danger'       => '#dc2626',
            );
        } elseif ( 'custom' === $preset ) {
            // Custom colors from settings
            $colors = array(
                'primary'      => get_option( 'cl_color_primary', '#2563eb' ),
                'primary_dark' => get_option( 'cl_color_primary_dark', '#1e40af' ),
                'secondary'    => get_option( 'cl_color_secondary', '#64748b' ),
                'text'         => get_option( 'cl_color_text', '#1e293b' ),
                'background'   => get_option( 'cl_color_background', '#ffffff' ),
                'success'      => get_option( 'cl_color_success', '#10b981' ),
                'danger'       => get_option( 'cl_color_danger', '#ef4444' ),
            );
        }

        return $colors;
    }
}
