<?php
/**
 * Coupon Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Coupon {

    /**
     * Coupon ID
     */
    private $id = 0;

    /**
     * Coupon data
     */
    private $data = array();

    /**
     * Ensure table exists
     */
    private static $table_checked = false;

    /**
     * Constructor
     */
    public function __construct( $coupon = 0 ) {
        self::maybe_create_table();

        if ( is_numeric( $coupon ) && $coupon > 0 ) {
            $this->id = absint( $coupon );
            $this->load();
        } elseif ( is_string( $coupon ) && ! empty( $coupon ) ) {
            $this->load_by_code( $coupon );
        }
    }

    /**
     * Create table if it doesn't exist
     */
    public static function maybe_create_table() {
        if ( self::$table_checked ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cl_coupons';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
            self::$table_checked = true;
            return;
        }

        // Create table
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
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
        dbDelta( $sql );

        self::$table_checked = true;
    }

    /**
     * Load coupon by ID
     */
    private function load() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_coupons';
        $coupon = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $this->id
        ), ARRAY_A );

        if ( $coupon ) {
            $this->data = $coupon;
        }
    }

    /**
     * Load coupon by code
     */
    private function load_by_code( $code ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_coupons';
        $coupon = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            strtoupper( $code )
        ), ARRAY_A );

        if ( $coupon ) {
            $this->id = absint( $coupon['id'] );
            $this->data = $coupon;
        }
    }

    /**
     * Check if coupon exists
     */
    public function exists() {
        return ! empty( $this->data );
    }

    /**
     * Get coupon ID
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get coupon code
     */
    public function get_code() {
        return isset( $this->data['code'] ) ? $this->data['code'] : '';
    }

    /**
     * Get description
     */
    public function get_description() {
        return isset( $this->data['description'] ) ? $this->data['description'] : '';
    }

    /**
     * Get discount type (percent or fixed)
     */
    public function get_discount_type() {
        return isset( $this->data['discount_type'] ) ? $this->data['discount_type'] : 'percent';
    }

    /**
     * Get discount value
     */
    public function get_discount_value() {
        return isset( $this->data['discount_value'] ) ? floatval( $this->data['discount_value'] ) : 0;
    }

    /**
     * Get minimum order amount
     */
    public function get_min_order_amount() {
        return isset( $this->data['min_order_amount'] ) ? floatval( $this->data['min_order_amount'] ) : 0;
    }

    /**
     * Get maximum discount amount
     */
    public function get_max_discount() {
        return isset( $this->data['max_discount'] ) ? floatval( $this->data['max_discount'] ) : 0;
    }

    /**
     * Get usage limit
     */
    public function get_usage_limit() {
        return isset( $this->data['usage_limit'] ) ? absint( $this->data['usage_limit'] ) : 0;
    }

    /**
     * Get usage count
     */
    public function get_usage_count() {
        return isset( $this->data['usage_count'] ) ? absint( $this->data['usage_count'] ) : 0;
    }

    /**
     * Get start date
     */
    public function get_start_date() {
        return isset( $this->data['start_date'] ) ? $this->data['start_date'] : null;
    }

    /**
     * Get end date
     */
    public function get_end_date() {
        return isset( $this->data['end_date'] ) ? $this->data['end_date'] : null;
    }

    /**
     * Get status
     */
    public function get_status() {
        return isset( $this->data['status'] ) ? $this->data['status'] : 'active';
    }

    /**
     * Check if coupon is valid
     */
    public function is_valid( $cart_total = 0 ) {
        // Check if exists
        if ( ! $this->exists() ) {
            return new WP_Error( 'invalid_coupon', __( 'קופון לא קיים', 'commerce-layer' ) );
        }

        // Check status
        if ( $this->get_status() !== 'active' ) {
            return new WP_Error( 'inactive_coupon', __( 'קופון לא פעיל', 'commerce-layer' ) );
        }

        // Check usage limit
        $usage_limit = $this->get_usage_limit();
        if ( $usage_limit > 0 && $this->get_usage_count() >= $usage_limit ) {
            return new WP_Error( 'usage_limit_reached', __( 'קופון מוצה', 'commerce-layer' ) );
        }

        // Check dates
        $now = current_time( 'mysql' );
        $start_date = $this->get_start_date();
        $end_date = $this->get_end_date();

        if ( $start_date && $now < $start_date ) {
            return new WP_Error( 'coupon_not_started', __( 'קופון עדיין לא בתוקף', 'commerce-layer' ) );
        }

        if ( $end_date && $now > $end_date ) {
            return new WP_Error( 'coupon_expired', __( 'קופון פג תוקף', 'commerce-layer' ) );
        }

        // Check minimum order amount
        $min_amount = $this->get_min_order_amount();
        if ( $min_amount > 0 && $cart_total < $min_amount ) {
            return new WP_Error(
                'min_amount_not_met',
                sprintf(
                    __( 'סכום הזמנה מינימלי לקופון זה: %s', 'commerce-layer' ),
                    CL_Core::format_price( $min_amount )
                )
            );
        }

        return true;
    }

    /**
     * Calculate discount amount for given cart total
     */
    public function calculate_discount( $cart_total ) {
        if ( ! $this->exists() ) {
            return 0;
        }

        $discount = 0;
        $discount_type = $this->get_discount_type();
        $discount_value = $this->get_discount_value();

        if ( $discount_type === 'percent' ) {
            $discount = $cart_total * ( $discount_value / 100 );
        } else {
            // Fixed amount
            $discount = $discount_value;
        }

        // Apply maximum discount cap
        $max_discount = $this->get_max_discount();
        if ( $max_discount > 0 && $discount > $max_discount ) {
            $discount = $max_discount;
        }

        // Don't exceed cart total
        if ( $discount > $cart_total ) {
            $discount = $cart_total;
        }

        return round( $discount, 2 );
    }

    /**
     * Increment usage count
     */
    public function increment_usage() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_coupons';
        $wpdb->query( $wpdb->prepare(
            "UPDATE $table SET usage_count = usage_count + 1 WHERE id = %d",
            $this->id
        ) );

        $this->data['usage_count'] = $this->get_usage_count() + 1;
    }

    /**
     * Save coupon
     */
    public function save( $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_coupons';

        $coupon_data = array(
            'code'             => strtoupper( sanitize_text_field( $data['code'] ?? '' ) ),
            'description'      => sanitize_text_field( $data['description'] ?? '' ),
            'discount_type'    => sanitize_text_field( $data['discount_type'] ?? 'percent' ),
            'discount_value'   => floatval( $data['discount_value'] ?? 0 ),
            'min_order_amount' => isset( $data['min_order_amount'] ) && $data['min_order_amount'] !== '' ? floatval( $data['min_order_amount'] ) : null,
            'max_discount'     => isset( $data['max_discount'] ) && $data['max_discount'] !== '' ? floatval( $data['max_discount'] ) : null,
            'usage_limit'      => isset( $data['usage_limit'] ) && $data['usage_limit'] !== '' ? absint( $data['usage_limit'] ) : null,
            'start_date'       => ! empty( $data['start_date'] ) ? sanitize_text_field( $data['start_date'] ) : null,
            'end_date'         => ! empty( $data['end_date'] ) ? sanitize_text_field( $data['end_date'] ) : null,
            'status'           => sanitize_text_field( $data['status'] ?? 'active' ),
        );

        $format = array( '%s', '%s', '%s', '%f', '%f', '%f', '%d', '%s', '%s', '%s' );

        if ( $this->id ) {
            // Update
            $wpdb->update( $table, $coupon_data, array( 'id' => $this->id ), $format, array( '%d' ) );
        } else {
            // Insert
            $wpdb->insert( $table, $coupon_data, $format );
            $this->id = $wpdb->insert_id;
        }

        $this->load();

        return $this->id;
    }

    /**
     * Delete coupon
     */
    public function delete() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_coupons';
        $wpdb->delete( $table, array( 'id' => $this->id ), array( '%d' ) );

        $this->id = 0;
        $this->data = array();
    }

    /**
     * Get all coupons
     */
    public static function get_all( $args = array() ) {
        self::maybe_create_table();

        global $wpdb;

        $defaults = array(
            'status'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'limit'    => 0,
            'offset'   => 0,
        );

        $args = wp_parse_args( $args, $defaults );
        $table = $wpdb->prefix . 'cl_coupons';

        $where = '1=1';
        if ( ! empty( $args['status'] ) ) {
            $where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
        }

        $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
        if ( ! $orderby ) {
            $orderby = 'created_at DESC';
        }

        $limit = '';
        if ( $args['limit'] > 0 ) {
            $limit = $wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
        }

        $coupons = $wpdb->get_results( "SELECT * FROM $table WHERE $where ORDER BY $orderby $limit", ARRAY_A );

        return $coupons;
    }

    /**
     * Get coupon data array
     */
    public function get_data() {
        return $this->data;
    }
}
