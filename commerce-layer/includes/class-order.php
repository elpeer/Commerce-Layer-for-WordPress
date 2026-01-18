<?php
/**
 * Order Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Order {

    /**
     * Order ID
     */
    private $id;

    /**
     * Order data
     */
    private $data;

    /**
     * Order items
     */
    private $items;

    /**
     * Valid statuses
     */
    public static $statuses = array(
        'pending'  => 'ממתין לתשלום',
        'paid'     => 'שולם',
        'canceled' => 'בוטל',
        'refunded' => 'זוכה',
        'lead'     => 'ליד',
    );

    /**
     * Constructor
     */
    public function __construct( $id = 0 ) {
        $this->id = absint( $id );
        if ( $this->id ) {
            $this->load();
        }
    }

    /**
     * Load order data
     */
    private function load() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_orders';
        $this->data = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $this->id ),
            ARRAY_A
        );

        if ( $this->data ) {
            $this->load_items();
        }
    }

    /**
     * Load order items
     */
    private function load_items() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_order_items';
        $this->items = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d", $this->id ),
            ARRAY_A
        );

        // Decode meta
        foreach ( $this->items as &$item ) {
            $item['meta'] = json_decode( $item['meta'], true ) ?: array();
        }
    }

    /**
     * Check if exists
     */
    public function exists() {
        return ! empty( $this->data );
    }

    /**
     * Get ID
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get order number
     */
    public function get_order_number() {
        return $this->data ? $this->data['order_number'] : '';
    }

    /**
     * Get status
     */
    public function get_status() {
        return $this->data ? $this->data['status'] : 'pending';
    }

    /**
     * Get status label
     */
    public function get_status_label() {
        $status = $this->get_status();
        return isset( self::$statuses[ $status ] ) ? self::$statuses[ $status ] : $status;
    }

    /**
     * Get customer email
     */
    public function get_customer_email() {
        return $this->data ? $this->data['customer_email'] : '';
    }

    /**
     * Get customer name
     */
    public function get_customer_name() {
        return $this->data ? $this->data['customer_name'] : '';
    }

    /**
     * Get customer phone
     */
    public function get_customer_phone() {
        return $this->data ? $this->data['customer_phone'] : '';
    }

    /**
     * Get billing address
     */
    public function get_billing_address() {
        if ( ! $this->data || empty( $this->data['billing_address'] ) ) {
            return array();
        }
        $address = json_decode( $this->data['billing_address'], true );
        return is_array( $address ) ? $address : array();
    }

    /**
     * Get subtotal
     */
    public function get_subtotal() {
        return $this->data ? floatval( $this->data['subtotal'] ) : 0;
    }

    /**
     * Get total
     */
    public function get_total() {
        return $this->data ? floatval( $this->data['total'] ) : 0;
    }

    /**
     * Get currency
     */
    public function get_currency() {
        return $this->data ? $this->data['currency'] : get_option( 'cl_currency', 'ILS' );
    }

    /**
     * Get payment method
     */
    public function get_payment_method() {
        return $this->data ? $this->data['payment_method'] : '';
    }

    /**
     * Get payment transaction ID
     */
    public function get_transaction_id() {
        return $this->data ? $this->data['payment_transaction_id'] : '';
    }

    /**
     * Get notes
     */
    public function get_notes() {
        return $this->data ? $this->data['notes'] : '';
    }

    /**
     * Get created date
     */
    public function get_created_date() {
        return $this->data ? $this->data['created_at'] : '';
    }

    /**
     * Get items
     */
    public function get_items() {
        return $this->items ?: array();
    }

    /**
     * Get items count
     */
    public function get_items_count() {
        $count = 0;
        foreach ( $this->get_items() as $item ) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Set status
     */
    public function set_status( $status ) {
        global $wpdb;

        if ( ! isset( self::$statuses[ $status ] ) ) {
            return false;
        }

        $table = $wpdb->prefix . 'cl_orders';
        $result = $wpdb->update(
            $table,
            array( 'status' => $status ),
            array( 'id' => $this->id ),
            array( '%s' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            $old_status = $this->data['status'];
            $this->data['status'] = $status;

            do_action( 'cl_order_status_changed', $this->id, $status, $old_status );

            return true;
        }

        return false;
    }

    /**
     * Set payment details
     */
    public function set_payment_details( $method, $transaction_id = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_orders';
        return $wpdb->update(
            $table,
            array(
                'payment_method'         => $method,
                'payment_transaction_id' => $transaction_id,
            ),
            array( 'id' => $this->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Generate order number
     */
    private static function generate_order_number() {
        $prefix = apply_filters( 'cl_order_number_prefix', 'CL-' );
        $number = time() . wp_rand( 100, 999 );
        return $prefix . $number;
    }

    /**
     * Create order from cart
     *
     * @param array   $customer_data Customer data.
     * @param CL_Cart $cart          Cart instance.
     * @param string  $status        Order status (default: 'pending').
     * @return CL_Order|WP_Error
     */
    public static function create_from_cart( $customer_data, $cart = null, $status = 'pending' ) {
        global $wpdb;

        if ( null === $cart ) {
            $cart = CL_Cart::get_instance();
        }

        if ( $cart->is_empty() ) {
            return new WP_Error( 'empty_cart', __( 'הסל ריק', 'commerce-layer' ) );
        }

        // Validate status
        if ( ! isset( self::$statuses[ $status ] ) ) {
            $status = 'pending';
        }

        // Prepare billing address
        $billing_address = array(
            'address'  => isset( $customer_data['address'] ) ? sanitize_textarea_field( $customer_data['address'] ) : '',
            'city'     => isset( $customer_data['city'] ) ? sanitize_text_field( $customer_data['city'] ) : '',
            'postcode' => isset( $customer_data['postcode'] ) ? sanitize_text_field( $customer_data['postcode'] ) : '',
        );

        // Insert order
        $orders_table = $wpdb->prefix . 'cl_orders';
        $result = $wpdb->insert(
            $orders_table,
            array(
                'order_number'    => self::generate_order_number(),
                'status'          => $status,
                'customer_email'  => sanitize_email( $customer_data['email'] ),
                'customer_name'   => sanitize_text_field( $customer_data['name'] ),
                'customer_phone'  => isset( $customer_data['phone'] ) ? sanitize_text_field( $customer_data['phone'] ) : '',
                'billing_address' => json_encode( $billing_address ),
                'subtotal'        => $cart->get_subtotal(),
                'total'           => $cart->get_total(),
                'currency'        => get_option( 'cl_currency', 'ILS' ),
                'notes'           => isset( $customer_data['notes'] ) ? sanitize_textarea_field( $customer_data['notes'] ) : '',
                'ip_address'      => self::get_client_ip(),
                'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s' )
        );

        if ( ! $result ) {
            return new WP_Error( 'db_error', __( 'שגיאה ביצירת ההזמנה', 'commerce-layer' ) );
        }

        $order_id = $wpdb->insert_id;

        // Insert order items
        $items_table = $wpdb->prefix . 'cl_order_items';
        foreach ( $cart->get_contents() as $cart_item ) {
            $wpdb->insert(
                $items_table,
                array(
                    'order_id'     => $order_id,
                    'post_id'      => $cart_item['post_id'],
                    'variant_id'   => $cart_item['variant_id'],
                    'name'         => $cart_item['name'],
                    'variant_name' => $cart_item['variant_name'],
                    'quantity'     => $cart_item['quantity'],
                    'price'        => $cart_item['price'],
                    'total'        => $cart_item['price'] * $cart_item['quantity'],
                    'meta'         => json_encode( $cart_item['meta'] ),
                ),
                array( '%d', '%d', '%d', '%s', '%s', '%d', '%f', '%f', '%s' )
            );
        }

        $order = new self( $order_id );

        do_action( 'cl_order_created', $order );

        return $order;
    }

    /**
     * Get client IP
     */
    private static function get_client_ip() {
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
     * Get order by order number
     */
    public static function get_by_order_number( $order_number ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_orders';
        $id = $wpdb->get_var(
            $wpdb->prepare( "SELECT id FROM $table WHERE order_number = %s", $order_number )
        );

        if ( $id ) {
            return new self( $id );
        }

        return null;
    }

    /**
     * Get orders with filters
     */
    public static function get_orders( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'status'   => '',
            'email'    => '',
            'search'   => '',
            'per_page' => 20,
            'page'     => 1,
            'orderby'  => 'created_at',
            'order'    => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $table = $wpdb->prefix . 'cl_orders';
        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['email'] ) ) {
            $where[] = 'customer_email = %s';
            $values[] = $args['email'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where[] = '(order_number LIKE %s OR customer_name LIKE %s OR customer_email LIKE %s)';
            $search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $search;
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode( ' AND ', $where );

        // Sanitize orderby
        $allowed_orderby = array( 'id', 'order_number', 'status', 'total', 'created_at' );
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ( $args['page'] - 1 ) * $args['per_page'];

        $sql = "SELECT id FROM $table WHERE $where_sql ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $values[] = $args['per_page'];
        $values[] = $offset;

        $results = $wpdb->get_results(
            $wpdb->prepare( $sql, $values )
        );

        $orders = array();
        foreach ( $results as $row ) {
            $orders[] = new self( $row->id );
        }

        return $orders;
    }

    /**
     * Get total count with filters
     */
    public static function get_count( $args = array() ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_orders';
        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        $where_sql = implode( ' AND ', $where );

        if ( empty( $values ) ) {
            return $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE $where_sql" );
        }

        return $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $where_sql", $values )
        );
    }

    /**
     * Cleanup old pending orders
     */
    public static function cleanup_old_pending_orders( $days = 7 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_orders';
        $items_table = $wpdb->prefix . 'cl_order_items';

        // Get old pending order IDs
        $old_orders = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );

        if ( empty( $old_orders ) ) {
            return 0;
        }

        $ids = implode( ',', array_map( 'absint', $old_orders ) );

        // Delete items
        $wpdb->query( "DELETE FROM $items_table WHERE order_id IN ($ids)" );

        // Delete orders
        return $wpdb->query( "DELETE FROM $table WHERE id IN ($ids)" );
    }

    /**
     * Get data array
     */
    public function get_data() {
        return array(
            'id'              => $this->id,
            'order_number'    => $this->get_order_number(),
            'status'          => $this->get_status(),
            'status_label'    => $this->get_status_label(),
            'customer_email'  => $this->get_customer_email(),
            'customer_name'   => $this->get_customer_name(),
            'customer_phone'  => $this->get_customer_phone(),
            'billing_address' => $this->get_billing_address(),
            'subtotal'        => $this->get_subtotal(),
            'total'           => $this->get_total(),
            'currency'        => $this->get_currency(),
            'payment_method'  => $this->get_payment_method(),
            'transaction_id'  => $this->get_transaction_id(),
            'notes'           => $this->get_notes(),
            'created_at'      => $this->get_created_date(),
            'items'           => $this->get_items(),
            'items_count'     => $this->get_items_count(),
            'formatted'       => array(
                'subtotal' => CL_Core::format_price( $this->get_subtotal() ),
                'total'    => CL_Core::format_price( $this->get_total() ),
            ),
        );
    }
}
