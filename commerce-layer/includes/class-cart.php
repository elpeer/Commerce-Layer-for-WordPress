<?php
/**
 * Shopping Cart Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Cart {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Cart contents
     */
    private $cart_contents = array();

    /**
     * Session key
     */
    private $session_key = 'cl_cart';

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
        $this->init_session();
        $this->load_cart();
    }

    /**
     * Initialize session
     */
    private function init_session() {
        if ( ! session_id() && ! headers_sent() ) {
            session_start();
        }
    }

    /**
     * Load cart from session
     */
    private function load_cart() {
        if ( isset( $_SESSION[ $this->session_key ] ) ) {
            $this->cart_contents = $_SESSION[ $this->session_key ];
        }
    }

    /**
     * Save cart to session
     */
    private function save_cart() {
        $_SESSION[ $this->session_key ] = $this->cart_contents;
    }

    /**
     * Generate cart item key
     */
    private function generate_cart_item_key( $post_id, $variant_id = 0 ) {
        return md5( $post_id . '_' . $variant_id );
    }

    /**
     * Add item to cart
     */
    public function add_to_cart( $post_id, $quantity = 1, $variant_id = 0, $meta = array() ) {
        $post_id    = absint( $post_id );
        $quantity   = absint( $quantity );
        $variant_id = absint( $variant_id );

        if ( $quantity <= 0 ) {
            $quantity = 1;
        }

        // Validate post
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'invalid_post', __( 'פריט לא תקין', 'commerce-layer' ) );
        }

        // Check if commerce is enabled
        $product = new CL_Product( $post_id );
        if ( ! $product->is_commerce_enabled() ) {
            return new WP_Error( 'commerce_disabled', __( 'פריט זה אינו זמין לרכישה', 'commerce-layer' ) );
        }

        // Get prices (sale price and regular price)
        $price = $product->get_price( $variant_id );
        if ( false === $price ) {
            return new WP_Error( 'no_price', __( 'לא הוגדר מחיר לפריט זה', 'commerce-layer' ) );
        }

        // Get regular price for discount calculations
        $regular_price = $price;
        if ( $variant_id ) {
            $variant = new CL_Variant( $variant_id );
            if ( $variant->exists() ) {
                $variant_regular = $variant->get_regular_price();
                if ( $variant_regular && $variant_regular > $price ) {
                    $regular_price = $variant_regular;
                }
            }
        } else {
            $product_regular = $product->get_regular_price();
            if ( $product_regular && $product_regular > $price ) {
                $regular_price = $product_regular;
            }
        }

        // Check quantity limits
        $min_qty = $product->get_min_quantity();
        $max_qty = $product->get_max_quantity();

        if ( $quantity < $min_qty ) {
            return new WP_Error( 'min_quantity', sprintf( __( 'כמות מינימום להזמנה: %d', 'commerce-layer' ), $min_qty ) );
        }

        if ( $max_qty > 0 && $quantity > $max_qty ) {
            return new WP_Error( 'max_quantity', sprintf( __( 'כמות מקסימום להזמנה: %d', 'commerce-layer' ), $max_qty ) );
        }

        // Generate key
        $cart_item_key = $this->generate_cart_item_key( $post_id, $variant_id );

        // Get variant data if exists
        $variant_data = null;
        $variant_name = '';
        if ( $variant_id ) {
            $variant_obj = new CL_Variant( $variant_id );
            if ( $variant_obj->exists() ) {
                $variant_data = $variant_obj->get_attributes();
                $variant_name = $variant_obj->get_name();
            }
        }

        // Add or update cart
        if ( isset( $this->cart_contents[ $cart_item_key ] ) ) {
            $new_quantity = $this->cart_contents[ $cart_item_key ]['quantity'] + $quantity;

            if ( $max_qty > 0 && $new_quantity > $max_qty ) {
                $new_quantity = $max_qty;
            }

            $this->cart_contents[ $cart_item_key ]['quantity'] = $new_quantity;
            // Update prices in case they changed
            $this->cart_contents[ $cart_item_key ]['price'] = $price;
            $this->cart_contents[ $cart_item_key ]['regular_price'] = $regular_price;
        } else {
            $this->cart_contents[ $cart_item_key ] = array(
                'post_id'       => $post_id,
                'variant_id'    => $variant_id,
                'name'          => $post->post_title,
                'variant_name'  => $variant_name,
                'variant_data'  => $variant_data,
                'price'         => $price,
                'regular_price' => $regular_price,
                'quantity'      => $quantity,
                'meta'          => $meta,
            );
        }

        $this->save_cart();

        do_action( 'cl_added_to_cart', $cart_item_key, $post_id, $quantity, $variant_id );

        return $cart_item_key;
    }

    /**
     * Update cart item quantity
     */
    public function update_quantity( $cart_item_key, $quantity ) {
        $quantity = absint( $quantity );

        if ( ! isset( $this->cart_contents[ $cart_item_key ] ) ) {
            return false;
        }

        if ( $quantity <= 0 ) {
            return $this->remove_item( $cart_item_key );
        }

        $item    = $this->cart_contents[ $cart_item_key ];
        $product = new CL_Product( $item['post_id'] );

        // Check quantity limits
        $min_qty = $product->get_min_quantity();
        $max_qty = $product->get_max_quantity();

        if ( $quantity < $min_qty ) {
            $quantity = $min_qty;
        }

        if ( $max_qty > 0 && $quantity > $max_qty ) {
            $quantity = $max_qty;
        }

        $this->cart_contents[ $cart_item_key ]['quantity'] = $quantity;
        $this->save_cart();

        do_action( 'cl_cart_item_updated', $cart_item_key, $quantity );

        return true;
    }

    /**
     * Remove item from cart
     */
    public function remove_item( $cart_item_key ) {
        if ( isset( $this->cart_contents[ $cart_item_key ] ) ) {
            $item = $this->cart_contents[ $cart_item_key ];
            unset( $this->cart_contents[ $cart_item_key ] );
            $this->save_cart();

            do_action( 'cl_cart_item_removed', $cart_item_key, $item );

            return true;
        }

        return false;
    }

    /**
     * Clear cart
     */
    public function clear() {
        $this->cart_contents = array();
        $this->save_cart();

        do_action( 'cl_cart_cleared' );
    }

    /**
     * Get cart contents
     */
    public function get_contents() {
        return $this->cart_contents;
    }

    /**
     * Get cart item
     */
    public function get_item( $cart_item_key ) {
        return isset( $this->cart_contents[ $cart_item_key ] ) ? $this->cart_contents[ $cart_item_key ] : null;
    }

    /**
     * Get cart items count
     */
    public function get_items_count() {
        $count = 0;
        foreach ( $this->cart_contents as $item ) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Get cart subtotal
     */
    public function get_subtotal() {
        $subtotal = 0;
        foreach ( $this->cart_contents as $item ) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Get cart total
     */
    public function get_total() {
        // For now, total equals subtotal (no taxes/shipping in v1)
        return $this->get_subtotal();
    }

    /**
     * Get total savings from discounts
     */
    public function get_total_savings() {
        $savings = 0;
        foreach ( $this->cart_contents as $item ) {
            $regular_price = isset( $item['regular_price'] ) ? floatval( $item['regular_price'] ) : $item['price'];
            $price = floatval( $item['price'] );
            if ( $regular_price > $price ) {
                $savings += ( $regular_price - $price ) * $item['quantity'];
            }
        }
        return $savings;
    }

    /**
     * Get subtotal before discounts (using regular prices)
     */
    public function get_subtotal_before_discounts() {
        $subtotal = 0;
        foreach ( $this->cart_contents as $item ) {
            $regular_price = isset( $item['regular_price'] ) ? floatval( $item['regular_price'] ) : $item['price'];
            $subtotal += $regular_price * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Get all totals
     */
    public function get_totals() {
        $subtotal = $this->get_subtotal();
        $subtotal_before_discounts = $this->get_subtotal_before_discounts();
        $total_savings = $this->get_total_savings();
        return array(
            'subtotal'                 => $subtotal,
            'subtotal_before_discounts' => $subtotal_before_discounts,
            'discount'                 => 0,
            'savings'                  => $total_savings,
            'shipping'                 => 0,
            'total'                    => $subtotal,
        );
    }

    /**
     * Get cart items (alias for get_contents)
     */
    public function get_items() {
        return $this->cart_contents;
    }

    /**
     * Check if cart is empty
     */
    public function is_empty() {
        return empty( $this->cart_contents );
    }

    /**
     * Cleanup expired sessions
     */
    public function cleanup_expired_sessions() {
        // Session cleanup is handled by PHP
        // This can be extended for database-based sessions
    }

    /**
     * REST API: Get cart
     */
    public function rest_get_cart( WP_REST_Request $request ) {
        return new WP_REST_Response( array(
            'contents'    => $this->get_contents(),
            'items_count' => $this->get_items_count(),
            'subtotal'    => $this->get_subtotal(),
            'total'       => $this->get_total(),
            'formatted'   => array(
                'subtotal' => CL_Core::format_price( $this->get_subtotal() ),
                'total'    => CL_Core::format_price( $this->get_total() ),
            ),
        ), 200 );
    }

    /**
     * REST API: Add to cart
     */
    public function rest_add_to_cart( WP_REST_Request $request ) {
        $post_id    = $request->get_param( 'post_id' );
        $quantity   = $request->get_param( 'quantity' ) ?: 1;
        $variant_id = $request->get_param( 'variant_id' ) ?: 0;

        $result = $this->add_to_cart( $post_id, $quantity, $variant_id );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => $result->get_error_message(),
            ), 400 );
        }

        return new WP_REST_Response( array(
            'success'     => true,
            'cart_key'    => $result,
            'items_count' => $this->get_items_count(),
            'total'       => $this->get_total(),
            'formatted'   => array(
                'total' => CL_Core::format_price( $this->get_total() ),
            ),
        ), 200 );
    }

    /**
     * REST API: Update cart
     */
    public function rest_update_cart( WP_REST_Request $request ) {
        $cart_key = $request->get_param( 'cart_key' );
        $quantity = $request->get_param( 'quantity' );

        $result = $this->update_quantity( $cart_key, $quantity );

        if ( ! $result ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => __( 'פריט לא נמצא בסל', 'commerce-layer' ),
            ), 400 );
        }

        return new WP_REST_Response( array(
            'success'     => true,
            'items_count' => $this->get_items_count(),
            'total'       => $this->get_total(),
            'formatted'   => array(
                'total' => CL_Core::format_price( $this->get_total() ),
            ),
        ), 200 );
    }

    /**
     * REST API: Remove from cart
     */
    public function rest_remove_from_cart( WP_REST_Request $request ) {
        $cart_key = $request->get_param( 'cart_key' );

        $result = $this->remove_item( $cart_key );

        if ( ! $result ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => __( 'פריט לא נמצא בסל', 'commerce-layer' ),
            ), 400 );
        }

        return new WP_REST_Response( array(
            'success'     => true,
            'items_count' => $this->get_items_count(),
            'total'       => $this->get_total(),
            'formatted'   => array(
                'total' => CL_Core::format_price( $this->get_total() ),
            ),
        ), 200 );
    }
}
