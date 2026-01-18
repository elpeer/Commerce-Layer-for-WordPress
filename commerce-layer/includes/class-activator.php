<?php
/**
 * Plugin Activator
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Activator {

    /**
     * Run activation tasks
     */
    public static function activate() {
        self::create_tables();
        self::create_pages();
        self::set_default_options();
        self::schedule_events();

        // Set flag for wizard
        update_option( 'cl_needs_wizard', true );

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Orders table
        $orders_table = $wpdb->prefix . 'cl_orders';
        $sql_orders = "CREATE TABLE $orders_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            customer_email varchar(100) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_phone varchar(30) DEFAULT NULL,
            billing_address text DEFAULT NULL,
            subtotal decimal(10,2) NOT NULL DEFAULT 0,
            total decimal(10,2) NOT NULL DEFAULT 0,
            currency varchar(3) NOT NULL DEFAULT 'ILS',
            payment_method varchar(50) DEFAULT NULL,
            payment_transaction_id varchar(100) DEFAULT NULL,
            notes text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY status (status),
            KEY customer_email (customer_email),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Order items table
        $order_items_table = $wpdb->prefix . 'cl_order_items';
        $sql_order_items = "CREATE TABLE $order_items_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            variant_id bigint(20) unsigned DEFAULT NULL,
            name varchar(255) NOT NULL,
            variant_name varchar(255) DEFAULT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            price decimal(10,2) NOT NULL,
            total decimal(10,2) NOT NULL,
            meta longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY post_id (post_id)
        ) $charset_collate;";

        // Variants table
        $variants_table = $wpdb->prefix . 'cl_variants';
        $sql_variants = "CREATE TABLE $variants_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            sku varchar(100) DEFAULT NULL,
            attributes longtext NOT NULL,
            price decimal(10,2) NOT NULL,
            sale_price decimal(10,2) DEFAULT NULL,
            image_id bigint(20) unsigned DEFAULT NULL,
            stock_status varchar(20) NOT NULL DEFAULT 'instock',
            stock_quantity int(11) DEFAULT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY sku (sku)
        ) $charset_collate;";

        // Attributes table
        $attributes_table = $wpdb->prefix . 'cl_attributes';
        $sql_attributes = "CREATE TABLE $attributes_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'select',
            sort_order int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        // Attribute values table
        $attribute_values_table = $wpdb->prefix . 'cl_attribute_values';
        $sql_attribute_values = "CREATE TABLE $attribute_values_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            attribute_id bigint(20) unsigned NOT NULL,
            value varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            sort_order int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY attribute_id (attribute_id)
        ) $charset_collate;";

        // Coupons table
        $coupons_table = $wpdb->prefix . 'cl_coupons';
        $sql_coupons = "CREATE TABLE $coupons_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            description varchar(255) DEFAULT NULL,
            discount_type varchar(20) NOT NULL DEFAULT 'percent',
            discount_value decimal(10,2) NOT NULL DEFAULT 0,
            min_order_amount decimal(10,2) DEFAULT NULL,
            max_discount decimal(10,2) DEFAULT NULL,
            usage_limit int(11) DEFAULT NULL,
            usage_count int(11) NOT NULL DEFAULT 0,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql_orders );
        dbDelta( $sql_order_items );
        dbDelta( $sql_variants );
        dbDelta( $sql_attributes );
        dbDelta( $sql_attribute_values );
        dbDelta( $sql_coupons );

        // Store DB version
        update_option( 'cl_db_version', CL_VERSION );
    }

    /**
     * Create default pages
     */
    private static function create_pages() {
        $pages = array(
            'cart' => array(
                'title'   => __( 'סל קניות', 'commerce-layer' ),
                'content' => '[cl_cart]',
                'option'  => 'cl_cart_page_id',
            ),
            'checkout' => array(
                'title'   => __( 'תשלום', 'commerce-layer' ),
                'content' => '[cl_checkout]',
                'option'  => 'cl_checkout_page_id',
            ),
            'thank_you' => array(
                'title'   => __( 'תודה על הזמנתך', 'commerce-layer' ),
                'content' => '[cl_thank_you]',
                'option'  => 'cl_thank_you_page_id',
            ),
        );

        foreach ( $pages as $slug => $page_data ) {
            $existing_page_id = get_option( $page_data['option'] );

            // Skip if page already exists
            if ( $existing_page_id && get_post( $existing_page_id ) ) {
                continue;
            }

            $page_id = wp_insert_post( array(
                'post_title'     => $page_data['title'],
                'post_content'   => $page_data['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_name'      => $slug,
                'comment_status' => 'closed',
            ) );

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_option( $page_data['option'], $page_id );
            }
        }
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $defaults = array(
            'cl_currency'             => 'ILS',
            'cl_currency_symbol'      => '₪',
            'cl_currency_position'    => 'right',
            'cl_thousand_separator'   => ',',
            'cl_decimal_separator'    => '.',
            'cl_decimals'             => 2,
            'cl_enabled_post_types'   => array( 'post' ),
            'cl_purchase_mode'        => 'both',
            'cl_display_mode'         => 'auto',
            'cl_display_position'     => 'after_content',
            'cl_add_to_cart_text'     => __( 'הוסף לסל', 'commerce-layer' ),
            'cl_buy_now_text'         => __( 'קנה עכשיו', 'commerce-layer' ),
            'cl_payment_gateway'      => 'tranzila',
            'cl_floating_bar_enabled' => 'yes',
        );

        foreach ( $defaults as $key => $value ) {
            if ( get_option( $key ) === false ) {
                update_option( $key, $value );
            }
        }
    }

    /**
     * Schedule cron events
     */
    private static function schedule_events() {
        if ( ! wp_next_scheduled( 'cl_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'cl_daily_cleanup' );
        }
    }
}
