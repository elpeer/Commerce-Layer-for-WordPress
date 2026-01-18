<?php
/**
 * Admin Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'plugin_action_links_' . CL_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'Commerce Layer', 'commerce-layer' ),
            __( 'Commerce Layer', 'commerce-layer' ),
            'manage_options',
            'commerce-layer',
            array( $this, 'render_dashboard' ),
            'dashicons-cart',
            30
        );

        // Dashboard submenu (same as main)
        add_submenu_page(
            'commerce-layer',
            __( 'לוח בקרה', 'commerce-layer' ),
            __( 'לוח בקרה', 'commerce-layer' ),
            'manage_options',
            'commerce-layer',
            array( $this, 'render_dashboard' )
        );

        // Orders
        add_submenu_page(
            'commerce-layer',
            __( 'הזמנות', 'commerce-layer' ),
            __( 'הזמנות', 'commerce-layer' ),
            'manage_options',
            'cl-orders',
            array( $this, 'render_orders' )
        );

        // Attributes
        add_submenu_page(
            'commerce-layer',
            __( 'מאפיינים', 'commerce-layer' ),
            __( 'מאפיינים', 'commerce-layer' ),
            'manage_options',
            'cl-attributes',
            array( $this, 'render_attributes' )
        );

        // Coupons
        add_submenu_page(
            'commerce-layer',
            __( 'קופונים', 'commerce-layer' ),
            __( 'קופונים', 'commerce-layer' ),
            'manage_options',
            'cl-coupons',
            array( $this, 'render_coupons' )
        );

        // Settings
        add_submenu_page(
            'commerce-layer',
            __( 'הגדרות', 'commerce-layer' ),
            __( 'הגדרות', 'commerce-layer' ),
            'manage_options',
            'cl-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts( $hook ) {
        // Global admin styles (minimal)
        wp_enqueue_style(
            'cl-admin-global',
            CL_PLUGIN_URL . 'assets/css/admin-global.css',
            array(),
            CL_VERSION
        );

        // Only on our pages or post edit screens
        $screen = get_current_screen();
        $is_our_page = strpos( $hook, 'commerce-layer' ) !== false || strpos( $hook, 'cl-' ) !== false;
        $is_commerce_post = $screen && $screen->base === 'post' && CL_Core::is_commerce_enabled( $screen->post_type );

        if ( ! $is_our_page && ! $is_commerce_post ) {
            return;
        }

        // Admin styles
        wp_enqueue_style(
            'cl-admin',
            CL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CL_VERSION
        );

        // Admin scripts
        wp_enqueue_script(
            'cl-admin',
            CL_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-util' ),
            CL_VERSION,
            true
        );

        // Media uploader for variant images
        wp_enqueue_media();

        // Localize
        wp_localize_script( 'cl-admin', 'clAdmin', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'cl_admin_nonce' ),
            'strings'   => array(
                'confirmDelete' => __( 'האם למחוק?', 'commerce-layer' ),
                'saved'         => __( 'נשמר', 'commerce-layer' ),
                'error'         => __( 'שגיאה', 'commerce-layer' ),
                'selectImage'   => __( 'בחר תמונה', 'commerce-layer' ),
                'useImage'      => __( 'השתמש בתמונה', 'commerce-layer' ),
            ),
        ) );
    }

    /**
     * Add plugin action links
     */
    public function plugin_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=cl-settings' ),
            __( 'הגדרות', 'commerce-layer' )
        );

        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        // Get stats
        $total_orders = CL_Order::get_count();
        $pending_orders = CL_Order::get_count( array( 'status' => 'pending' ) );
        $paid_orders = CL_Order::get_count( array( 'status' => 'paid' ) );

        // Recent orders
        $recent_orders = CL_Order::get_orders( array( 'per_page' => 5 ) );

        include CL_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render orders page
     */
    public function render_orders() {
        // Check for single order view
        if ( isset( $_GET['order_id'] ) ) {
            $order = new CL_Order( absint( $_GET['order_id'] ) );
            if ( $order->exists() ) {
                include CL_PLUGIN_DIR . 'admin/views/order-view.php';
                return;
            }
        }

        // Load WP_List_Table class if not loaded
        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        // Load orders list class
        require_once CL_PLUGIN_DIR . 'admin/class-orders-list.php';

        // List view
        $orders_list = new CL_Orders_List();
        $orders_list->prepare_items();

        include CL_PLUGIN_DIR . 'admin/views/orders-list.php';
    }

    /**
     * Render attributes page
     */
    public function render_attributes() {
        include CL_PLUGIN_DIR . 'admin/views/attributes.php';
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        $settings = new CL_Settings();
        $settings->render();
    }

    /**
     * Render coupons page
     */
    public function render_coupons() {
        include CL_PLUGIN_DIR . 'admin/views/coupons.php';
    }
}
