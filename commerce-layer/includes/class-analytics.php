<?php
/**
 * Analytics Class
 *
 * Tracks user events and provides analytics dashboard data
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Analytics {

    /**
     * Table name
     */
    private static $table_name;

    /**
     * Table checked flag
     */
    private static $table_checked = false;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'cl_analytics';

        // Hook into events to track them
        add_action( 'cl_add_to_cart', array( $this, 'track_add_to_cart' ), 10, 4 );
        add_action( 'cl_remove_from_cart', array( $this, 'track_remove_from_cart' ), 10, 2 );
        add_action( 'cl_begin_checkout', array( $this, 'track_begin_checkout' ), 10, 2 );
        add_action( 'cl_order_created', array( $this, 'track_order_created' ), 10, 1 );
        add_action( 'cl_payment_complete', array( $this, 'track_purchase' ), 10, 1 );
        add_action( 'cl_lead_submitted', array( $this, 'track_lead' ), 10, 1 );

        // Track page views on product pages
        add_action( 'wp', array( $this, 'track_product_view' ) );

        // AJAX handler for tracking events from frontend
        add_action( 'wp_ajax_cl_track_event', array( $this, 'ajax_track_event' ) );
        add_action( 'wp_ajax_nopriv_cl_track_event', array( $this, 'ajax_track_event' ) );
    }

    /**
     * Maybe create the analytics table
     */
    public static function maybe_create_table() {
        if ( self::$table_checked ) {
            return;
        }

        global $wpdb;
        $table = self::$table_name ?: $wpdb->prefix . 'cl_analytics';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
            self::$table_checked = true;
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            post_id bigint(20) unsigned DEFAULT 0,
            product_name varchar(255) DEFAULT '',
            order_number varchar(50) DEFAULT '',
            page_url varchar(500) DEFAULT '',
            page_title varchar(255) DEFAULT '',
            referrer varchar(500) DEFAULT '',
            user_id bigint(20) unsigned DEFAULT 0,
            session_id varchar(100) DEFAULT '',
            ip_address varchar(45) DEFAULT '',
            user_agent varchar(500) DEFAULT '',
            device_type varchar(20) DEFAULT '',
            value decimal(10,2) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY post_id (post_id),
            KEY created_at (created_at),
            KEY session_id (session_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        self::$table_checked = true;
    }

    /**
     * Get or create session ID
     */
    private function get_session_id() {
        if ( ! isset( $_COOKIE['cl_session'] ) ) {
            $session_id = wp_generate_uuid4();
            setcookie( 'cl_session', $session_id, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
            return $session_id;
        }
        return sanitize_text_field( $_COOKIE['cl_session'] );
    }

    /**
     * Get device type from user agent
     */
    private function get_device_type() {
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if ( preg_match( '/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent ) ) {
            return 'tablet';
        }

        if ( preg_match( '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent ) ) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Get client IP
     */
    private function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field( $ip );
    }

    /**
     * Record an event
     */
    public function record_event( $event_type, $data = array() ) {
        self::maybe_create_table();

        global $wpdb;

        $defaults = array(
            'post_id'      => 0,
            'product_name' => '',
            'order_number' => '',
            'page_url'     => isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : '',
            'page_title'   => '',
            'referrer'     => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
            'value'        => 0,
        );

        $data = wp_parse_args( $data, $defaults );

        $wpdb->insert(
            self::$table_name,
            array(
                'event_type'   => sanitize_text_field( $event_type ),
                'event_data'   => json_encode( $data ),
                'post_id'      => absint( $data['post_id'] ),
                'product_name' => sanitize_text_field( $data['product_name'] ),
                'order_number' => sanitize_text_field( $data['order_number'] ),
                'page_url'     => esc_url_raw( $data['page_url'] ),
                'page_title'   => sanitize_text_field( $data['page_title'] ),
                'referrer'     => esc_url_raw( $data['referrer'] ),
                'user_id'      => get_current_user_id(),
                'session_id'   => $this->get_session_id(),
                'ip_address'   => $this->get_client_ip(),
                'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) ) : '',
                'device_type'  => $this->get_device_type(),
                'value'        => floatval( $data['value'] ),
            ),
            array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f' )
        );

        return $wpdb->insert_id;
    }

    /**
     * Track product view
     */
    public function track_product_view() {
        if ( ! is_singular() ) {
            return;
        }

        global $post;
        $product = new CL_Product( $post->ID );

        if ( ! $product->is_commerce_enabled() ) {
            return;
        }

        $this->record_event( 'view_product', array(
            'post_id'      => $post->ID,
            'product_name' => $post->post_title,
            'page_title'   => $post->post_title,
            'value'        => $product->get_price(),
        ) );
    }

    /**
     * Track add to cart
     */
    public function track_add_to_cart( $post_id, $quantity, $variant_id, $cart_item ) {
        $post = get_post( $post_id );
        $this->record_event( 'add_to_cart', array(
            'post_id'      => $post_id,
            'product_name' => $post ? $post->post_title : '',
            'quantity'     => $quantity,
            'variant_id'   => $variant_id,
            'variant_name' => $cart_item ? $cart_item['variant_name'] : '',
            'value'        => $cart_item ? floatval( $cart_item['price'] ) * $quantity : 0,
        ) );
    }

    /**
     * Track remove from cart
     */
    public function track_remove_from_cart( $cart_key, $cart_item ) {
        if ( ! $cart_item ) {
            return;
        }

        $this->record_event( 'remove_from_cart', array(
            'post_id'      => $cart_item['post_id'],
            'product_name' => $cart_item['name'],
            'quantity'     => $cart_item['quantity'],
            'value'        => floatval( $cart_item['price'] ) * $cart_item['quantity'],
        ) );
    }

    /**
     * Track begin checkout
     */
    public function track_begin_checkout( $cart, $totals ) {
        $this->record_event( 'begin_checkout', array(
            'value'      => $totals['total'],
            'items_count' => $cart->get_items_count(),
        ) );
    }

    /**
     * Track order created
     */
    public function track_order_created( $order ) {
        $this->record_event( 'order_created', array(
            'order_number' => $order->get_order_number(),
            'value'        => $order->get_total(),
            'items_count'  => $order->get_items_count(),
        ) );
    }

    /**
     * Track purchase
     */
    public function track_purchase( $order ) {
        $this->record_event( 'purchase', array(
            'order_number' => $order->get_order_number(),
            'value'        => $order->get_total(),
            'items_count'  => $order->get_items_count(),
        ) );
    }

    /**
     * Track lead
     */
    public function track_lead( $order ) {
        $this->record_event( 'lead_submitted', array(
            'order_number' => $order->get_order_number(),
            'value'        => $order->get_total(),
            'items_count'  => $order->get_items_count(),
        ) );
    }

    /**
     * AJAX track event
     */
    public function ajax_track_event() {
        check_ajax_referer( 'cl_ajax_nonce', 'nonce' );

        $event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
        $data = isset( $_POST['data'] ) ? json_decode( stripslashes( $_POST['data'] ), true ) : array();

        if ( empty( $event_type ) ) {
            wp_send_json_error();
        }

        $this->record_event( $event_type, $data );
        wp_send_json_success();
    }

    /**
     * Get dashboard statistics
     */
    public static function get_dashboard_stats( $days = 30 ) {
        self::maybe_create_table();

        global $wpdb;
        $table = self::$table_name ?: $wpdb->prefix . 'cl_analytics';

        $date_from = date( 'Y-m-d 00:00:00', strtotime( "-{$days} days" ) );

        $stats = array();

        // Total events by type
        $stats['events_by_type'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT event_type, COUNT(*) as count, SUM(value) as total_value
                 FROM $table
                 WHERE created_at >= %s
                 GROUP BY event_type
                 ORDER BY count DESC",
                $date_from
            ),
            ARRAY_A
        );

        // Unique visitors (sessions)
        $stats['unique_visitors'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT session_id) FROM $table WHERE created_at >= %s",
                $date_from
            )
        );

        // Product views
        $stats['product_views'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE event_type = 'view_product' AND created_at >= %s",
                $date_from
            )
        );

        // Add to cart
        $stats['add_to_cart'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE event_type = 'add_to_cart' AND created_at >= %s",
                $date_from
            )
        );

        // Checkouts started
        $stats['checkouts_started'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE event_type = 'begin_checkout' AND created_at >= %s",
                $date_from
            )
        );

        // Purchases
        $stats['purchases'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE event_type = 'purchase' AND created_at >= %s",
                $date_from
            )
        );

        // Leads
        $stats['leads'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE event_type = 'lead_submitted' AND created_at >= %s",
                $date_from
            )
        );

        // Revenue
        $stats['revenue'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(value) FROM $table WHERE event_type = 'purchase' AND created_at >= %s",
                $date_from
            )
        ) ?: 0;

        // Lead value
        $stats['lead_value'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(value) FROM $table WHERE event_type = 'lead_submitted' AND created_at >= %s",
                $date_from
            )
        ) ?: 0;

        // Conversion rate (add_to_cart to purchase)
        if ( $stats['add_to_cart'] > 0 ) {
            $stats['conversion_rate'] = round( ( $stats['purchases'] / $stats['add_to_cart'] ) * 100, 2 );
        } else {
            $stats['conversion_rate'] = 0;
        }

        // Cart abandonment rate
        if ( $stats['checkouts_started'] > 0 ) {
            $completed = $stats['purchases'] + $stats['leads'];
            $stats['abandonment_rate'] = round( ( ( $stats['checkouts_started'] - $completed ) / $stats['checkouts_started'] ) * 100, 2 );
        } else {
            $stats['abandonment_rate'] = 0;
        }

        // Top products by views
        $stats['top_products_views'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, product_name, COUNT(*) as views
                 FROM $table
                 WHERE event_type = 'view_product' AND created_at >= %s AND post_id > 0
                 GROUP BY post_id, product_name
                 ORDER BY views DESC
                 LIMIT 10",
                $date_from
            ),
            ARRAY_A
        );

        // Top products by add to cart
        $stats['top_products_cart'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, product_name, COUNT(*) as cart_adds, SUM(value) as total_value
                 FROM $table
                 WHERE event_type = 'add_to_cart' AND created_at >= %s AND post_id > 0
                 GROUP BY post_id, product_name
                 ORDER BY cart_adds DESC
                 LIMIT 10",
                $date_from
            ),
            ARRAY_A
        );

        // Device breakdown
        $stats['device_breakdown'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT device_type, COUNT(*) as count
                 FROM $table
                 WHERE created_at >= %s AND device_type != ''
                 GROUP BY device_type
                 ORDER BY count DESC",
                $date_from
            ),
            ARRAY_A
        );

        // Daily events for chart
        $stats['daily_events'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) as date,
                        SUM(CASE WHEN event_type = 'view_product' THEN 1 ELSE 0 END) as views,
                        SUM(CASE WHEN event_type = 'add_to_cart' THEN 1 ELSE 0 END) as carts,
                        SUM(CASE WHEN event_type = 'purchase' THEN 1 ELSE 0 END) as purchases,
                        SUM(CASE WHEN event_type = 'purchase' THEN value ELSE 0 END) as revenue
                 FROM $table
                 WHERE created_at >= %s
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                $date_from
            ),
            ARRAY_A
        );

        // Top referrers
        $stats['top_referrers'] = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    CASE
                        WHEN referrer = '' THEN 'ישיר'
                        ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '/', 3), '://', -1)
                    END as source,
                    COUNT(*) as count
                 FROM $table
                 WHERE created_at >= %s
                 GROUP BY source
                 ORDER BY count DESC
                 LIMIT 10",
                $date_from
            ),
            ARRAY_A
        );

        return $stats;
    }

    /**
     * Get recent events
     */
    public static function get_recent_events( $limit = 50 ) {
        self::maybe_create_table();

        global $wpdb;
        $table = self::$table_name ?: $wpdb->prefix . 'cl_analytics';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }
}

// Initialize analytics
new CL_Analytics();
