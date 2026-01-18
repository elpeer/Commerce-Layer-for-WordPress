<?php
/**
 * Core Plugin Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Core {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Admin instance
     */
    public $admin = null;

    /**
     * Public instance
     */
    public $public = null;

    /**
     * Cart instance
     */
    public $cart = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core includes
        require_once CL_PLUGIN_DIR . 'includes/class-cart.php';
        require_once CL_PLUGIN_DIR . 'includes/class-order.php';
        require_once CL_PLUGIN_DIR . 'includes/class-product.php';
        require_once CL_PLUGIN_DIR . 'includes/class-variant.php';
        require_once CL_PLUGIN_DIR . 'includes/class-ajax.php';
        require_once CL_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once CL_PLUGIN_DIR . 'includes/class-checkout.php';

        // Admin includes
        if ( is_admin() ) {
            require_once CL_PLUGIN_DIR . 'admin/class-admin.php';
            require_once CL_PLUGIN_DIR . 'admin/class-meta-box.php';
            require_once CL_PLUGIN_DIR . 'admin/class-settings.php';
            require_once CL_PLUGIN_DIR . 'admin/class-orders-list.php';
            require_once CL_PLUGIN_DIR . 'admin/class-wizard.php';
        }

        // Public includes
        require_once CL_PLUGIN_DIR . 'public/class-public.php';
        require_once CL_PLUGIN_DIR . 'public/class-purchase-card.php';
    }

    /**
     * Run the plugin
     */
    public function run() {
        // Initialize cart (always needed for session)
        $this->cart = CL_Cart::get_instance();

        // Initialize AJAX handlers
        new CL_Ajax();

        // Initialize shortcodes
        new CL_Shortcodes();

        // Initialize checkout
        new CL_Checkout();

        // Admin
        if ( is_admin() ) {
            $this->admin = new CL_Admin();
            new CL_Meta_Box();
            new CL_Settings();
            new CL_Orders_List();
            new CL_Wizard();
        }

        // Public
        if ( ! is_admin() || wp_doing_ajax() ) {
            $this->public = new CL_Public();
            new CL_Purchase_Card();
        }

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Register global hooks
     */
    private function register_hooks() {
        // Cron cleanup
        add_action( 'cl_daily_cleanup', array( $this, 'daily_cleanup' ) );

        // REST API
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    /**
     * Daily cleanup tasks
     */
    public function daily_cleanup() {
        // Clean expired sessions
        $this->cart->cleanup_expired_sessions();

        // Clean pending orders older than 7 days
        CL_Order::cleanup_old_pending_orders( 7 );
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route( 'commerce-layer/v1', '/cart', array(
            'methods'             => 'GET',
            'callback'            => array( $this->cart, 'rest_get_cart' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'commerce-layer/v1', '/cart/add', array(
            'methods'             => 'POST',
            'callback'            => array( $this->cart, 'rest_add_to_cart' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'commerce-layer/v1', '/cart/update', array(
            'methods'             => 'POST',
            'callback'            => array( $this->cart, 'rest_update_cart' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'commerce-layer/v1', '/cart/remove', array(
            'methods'             => 'POST',
            'callback'            => array( $this->cart, 'rest_remove_from_cart' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * Get enabled post types
     */
    public static function get_enabled_post_types() {
        $post_types = get_option( 'cl_enabled_post_types', array() );
        return is_array( $post_types ) ? $post_types : array();
    }

    /**
     * Check if post type is commerce enabled
     */
    public static function is_commerce_enabled( $post_type ) {
        return in_array( $post_type, self::get_enabled_post_types(), true );
    }

    /**
     * Format price
     */
    public static function format_price( $price, $args = array() ) {
        $defaults = array(
            'currency'           => get_option( 'cl_currency', 'ILS' ),
            'currency_symbol'    => get_option( 'cl_currency_symbol', '₪' ),
            'currency_position'  => get_option( 'cl_currency_position', 'right' ),
            'thousand_separator' => get_option( 'cl_thousand_separator', ',' ),
            'decimal_separator'  => get_option( 'cl_decimal_separator', '.' ),
            'decimals'           => get_option( 'cl_decimals', 2 ),
        );

        $args = wp_parse_args( $args, $defaults );

        $price = number_format(
            (float) $price,
            $args['decimals'],
            $args['decimal_separator'],
            $args['thousand_separator']
        );

        if ( 'left' === $args['currency_position'] ) {
            return $args['currency_symbol'] . $price;
        } elseif ( 'left_space' === $args['currency_position'] ) {
            return $args['currency_symbol'] . ' ' . $price;
        } elseif ( 'right_space' === $args['currency_position'] ) {
            return $price . ' ' . $args['currency_symbol'];
        }

        // Default: right
        return $price . $args['currency_symbol'];
    }
}
