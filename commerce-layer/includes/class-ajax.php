<?php
/**
 * AJAX Handler Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Ajax {

    /**
     * Constructor
     */
    public function __construct() {
        $this->register_actions();
    }

    /**
     * Register AJAX actions
     */
    private function register_actions() {
        // Public actions
        $public_actions = array(
            'cl_add_to_cart',
            'cl_update_cart',
            'cl_remove_from_cart',
            'cl_get_cart',
            'cl_get_side_cart',
            'cl_get_variant_data',
            'cl_apply_coupon',
        );

        foreach ( $public_actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( $this, $action ) );
            add_action( 'wp_ajax_nopriv_' . $action, array( $this, $action ) );
        }

        // Admin actions
        $admin_actions = array(
            'cl_save_product_meta',
            'cl_save_variant',
            'cl_delete_variant',
            'cl_update_order_status',
            'cl_get_attributes',
            'cl_save_attribute',
            'cl_generate_variants',
        );

        foreach ( $admin_actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( $this, $action ) );
        }
    }

    /**
     * Verify nonce
     */
    private function verify_nonce( $action = 'cl_ajax_nonce' ) {
        if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'אימות נכשל', 'commerce-layer' ) ) );
        }
    }

    /**
     * Add to cart
     */
    public function cl_add_to_cart() {
        $this->verify_nonce();

        $post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'פריט לא תקין', 'commerce-layer' ) ) );
        }

        $cart = CL_Cart::get_instance();
        $result = $cart->add_to_cart( $post_id, $quantity, $variant_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message'     => __( 'הפריט נוסף לסל', 'commerce-layer' ),
            'cart_key'    => $result,
            'items_count' => $cart->get_items_count(),
            'total'       => $cart->get_total(),
            'formatted'   => array(
                'total' => CL_Core::format_price( $cart->get_total() ),
            ),
            'cart_url'    => get_permalink( get_option( 'cl_cart_page_id' ) ),
        ) );
    }

    /**
     * Update cart
     */
    public function cl_update_cart() {
        $this->verify_nonce();

        $cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
        $quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;

        if ( ! $cart_key ) {
            wp_send_json_error( array( 'message' => __( 'פריט לא תקין', 'commerce-layer' ) ) );
        }

        $cart = CL_Cart::get_instance();
        $result = $cart->update_quantity( $cart_key, $quantity );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'עדכון נכשל', 'commerce-layer' ) ) );
        }

        wp_send_json_success( array(
            'message'     => __( 'הסל עודכן', 'commerce-layer' ),
            'items_count' => $cart->get_items_count(),
            'subtotal'    => $cart->get_subtotal(),
            'total'       => $cart->get_total(),
            'is_empty'    => $cart->is_empty(),
            'totals'      => $cart->get_totals(),
            'formatted'   => array(
                'subtotal' => CL_Core::format_price( $cart->get_subtotal() ),
                'total'    => CL_Core::format_price( $cart->get_total() ),
            ),
        ) );
    }

    /**
     * Remove from cart
     */
    public function cl_remove_from_cart() {
        $this->verify_nonce();

        $cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';

        if ( ! $cart_key ) {
            wp_send_json_error( array( 'message' => __( 'פריט לא תקין', 'commerce-layer' ) ) );
        }

        $cart = CL_Cart::get_instance();
        $result = $cart->remove_item( $cart_key );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'הסרה נכשלה', 'commerce-layer' ) ) );
        }

        wp_send_json_success( array(
            'message'     => __( 'הפריט הוסר', 'commerce-layer' ),
            'items_count' => $cart->get_items_count(),
            'subtotal'    => $cart->get_subtotal(),
            'total'       => $cart->get_total(),
            'is_empty'    => $cart->is_empty(),
            'totals'      => $cart->get_totals(),
            'formatted'   => array(
                'subtotal' => CL_Core::format_price( $cart->get_subtotal() ),
                'total'    => CL_Core::format_price( $cart->get_total() ),
            ),
        ) );
    }

    /**
     * Get cart
     */
    public function cl_get_cart() {
        $cart = CL_Cart::get_instance();

        wp_send_json_success( array(
            'contents'    => $cart->get_contents(),
            'items_count' => $cart->get_items_count(),
            'subtotal'    => $cart->get_subtotal(),
            'total'       => $cart->get_total(),
            'is_empty'    => $cart->is_empty(),
            'formatted'   => array(
                'subtotal' => CL_Core::format_price( $cart->get_subtotal() ),
                'total'    => CL_Core::format_price( $cart->get_total() ),
            ),
        ) );
    }

    /**
     * Get side cart HTML
     */
    public function cl_get_side_cart() {
        $this->verify_nonce();

        $cart = CL_Cart::get_instance();

        ob_start();
        include CL_PLUGIN_DIR . 'templates/side-cart.php';
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html'        => $html,
            'items_count' => $cart->get_items_count(),
            'totals'      => $cart->get_totals(),
            'is_empty'    => $cart->is_empty(),
        ) );
    }

    /**
     * Get variant data
     */
    public function cl_get_variant_data() {
        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : 0;

        if ( ! $variant_id ) {
            wp_send_json_error( array( 'message' => __( 'וריאציה לא תקינה', 'commerce-layer' ) ) );
        }

        $variant = new CL_Variant( $variant_id );

        if ( ! $variant->exists() ) {
            wp_send_json_error( array( 'message' => __( 'וריאציה לא נמצאה', 'commerce-layer' ) ) );
        }

        $product = new CL_Product( $variant->get_post_id() );

        wp_send_json_success( array(
            'variant'    => $variant->get_data(),
            'price_html' => $product->get_price_html( $variant_id ),
        ) );
    }

    /**
     * Apply coupon (placeholder for future)
     */
    public function cl_apply_coupon() {
        wp_send_json_error( array( 'message' => __( 'קופונים אינם זמינים בגרסה זו', 'commerce-layer' ) ) );
    }

    /**
     * Save product meta (admin)
     */
    public function cl_save_product_meta() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'פוסט לא תקין', 'commerce-layer' ) ) );
        }

        $product = new CL_Product( $post_id );
        $product->save( array(
            'enabled'       => isset( $_POST['enabled'] ) ? 'yes' : 'no',
            'price'         => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : '',
            'sale_price'    => isset( $_POST['sale_price'] ) ? floatval( $_POST['sale_price'] ) : '',
            'purchase_mode' => isset( $_POST['purchase_mode'] ) ? sanitize_text_field( $_POST['purchase_mode'] ) : 'both',
            'min_quantity'  => isset( $_POST['min_quantity'] ) ? absint( $_POST['min_quantity'] ) : 1,
            'max_quantity'  => isset( $_POST['max_quantity'] ) ? absint( $_POST['max_quantity'] ) : 0,
            'has_variants'  => isset( $_POST['has_variants'] ) ? 'yes' : 'no',
            'attributes'    => isset( $_POST['attributes'] ) ? array_map( 'sanitize_text_field', $_POST['attributes'] ) : array(),
        ) );

        wp_send_json_success( array( 'message' => __( 'נשמר בהצלחה', 'commerce-layer' ) ) );
    }

    /**
     * Save variant (admin)
     */
    public function cl_save_variant() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : 0;
        $post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'פוסט לא תקין', 'commerce-layer' ) ) );
        }

        $variant = new CL_Variant( $variant_id );

        $data = array(
            'post_id'        => $post_id,
            'sku'            => isset( $_POST['sku'] ) ? sanitize_text_field( $_POST['sku'] ) : '',
            'attributes'     => isset( $_POST['attributes'] ) ? $_POST['attributes'] : array(),
            'price'          => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0,
            'sale_price'     => isset( $_POST['sale_price'] ) && $_POST['sale_price'] !== '' ? floatval( $_POST['sale_price'] ) : null,
            'image_id'       => isset( $_POST['image_id'] ) ? absint( $_POST['image_id'] ) : null,
            'stock_status'   => isset( $_POST['stock_status'] ) ? sanitize_text_field( $_POST['stock_status'] ) : 'instock',
            'stock_quantity' => isset( $_POST['stock_quantity'] ) ? absint( $_POST['stock_quantity'] ) : null,
            'sort_order'     => isset( $_POST['sort_order'] ) ? absint( $_POST['sort_order'] ) : 0,
        );

        $new_id = $variant->save( $data );

        wp_send_json_success( array(
            'message'    => __( 'וריאציה נשמרה', 'commerce-layer' ),
            'variant_id' => $new_id,
            'variant'    => $variant->get_data(),
        ) );
    }

    /**
     * Delete variant (admin)
     */
    public function cl_delete_variant() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : 0;

        if ( ! $variant_id ) {
            wp_send_json_error( array( 'message' => __( 'וריאציה לא תקינה', 'commerce-layer' ) ) );
        }

        $variant = new CL_Variant( $variant_id );

        if ( ! $variant->exists() ) {
            wp_send_json_error( array( 'message' => __( 'וריאציה לא נמצאה', 'commerce-layer' ) ) );
        }

        $variant->delete();

        wp_send_json_success( array( 'message' => __( 'וריאציה נמחקה', 'commerce-layer' ) ) );
    }

    /**
     * Update order status (admin)
     */
    public function cl_update_order_status() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

        if ( ! $order_id || ! $status ) {
            wp_send_json_error( array( 'message' => __( 'נתונים חסרים', 'commerce-layer' ) ) );
        }

        $order = new CL_Order( $order_id );

        if ( ! $order->exists() ) {
            wp_send_json_error( array( 'message' => __( 'הזמנה לא נמצאה', 'commerce-layer' ) ) );
        }

        $result = $order->set_status( $status );

        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'עדכון סטטוס נכשל', 'commerce-layer' ) ) );
        }

        wp_send_json_success( array(
            'message'      => __( 'סטטוס עודכן', 'commerce-layer' ),
            'status'       => $status,
            'status_label' => $order->get_status_label(),
        ) );
    }

    /**
     * Get attributes (admin)
     */
    public function cl_get_attributes() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_attributes';
        $attributes = $wpdb->get_results( "SELECT * FROM $table ORDER BY sort_order ASC", ARRAY_A );

        // Get values for each attribute
        $values_table = $wpdb->prefix . 'cl_attribute_values';
        foreach ( $attributes as &$attr ) {
            $attr['values'] = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $values_table WHERE attribute_id = %d ORDER BY sort_order ASC",
                    $attr['id']
                ),
                ARRAY_A
            );
        }

        wp_send_json_success( array( 'attributes' => $attributes ) );
    }

    /**
     * Save attribute (admin)
     */
    public function cl_save_attribute() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        global $wpdb;

        $attr_id = isset( $_POST['attribute_id'] ) ? absint( $_POST['attribute_id'] ) : 0;
        $name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $slug    = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : sanitize_title( $name );
        $values  = isset( $_POST['values'] ) ? array_map( 'sanitize_text_field', $_POST['values'] ) : array();

        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'שם מאפיין נדרש', 'commerce-layer' ) ) );
        }

        $attrs_table = $wpdb->prefix . 'cl_attributes';
        $values_table = $wpdb->prefix . 'cl_attribute_values';

        if ( $attr_id ) {
            // Update
            $wpdb->update(
                $attrs_table,
                array( 'name' => $name, 'slug' => $slug ),
                array( 'id' => $attr_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
        } else {
            // Insert
            $wpdb->insert(
                $attrs_table,
                array( 'name' => $name, 'slug' => $slug, 'type' => 'select' ),
                array( '%s', '%s', '%s' )
            );
            $attr_id = $wpdb->insert_id;
        }

        // Update values
        $wpdb->delete( $values_table, array( 'attribute_id' => $attr_id ), array( '%d' ) );

        foreach ( $values as $i => $value ) {
            if ( empty( $value ) ) {
                continue;
            }
            $wpdb->insert(
                $values_table,
                array(
                    'attribute_id' => $attr_id,
                    'value'        => $value,
                    'slug'         => sanitize_title( $value ),
                    'sort_order'   => $i,
                ),
                array( '%d', '%s', '%s', '%d' )
            );
        }

        wp_send_json_success( array(
            'message'      => __( 'מאפיין נשמר', 'commerce-layer' ),
            'attribute_id' => $attr_id,
        ) );
    }

    /**
     * Generate variants from attributes (admin)
     */
    public function cl_generate_variants() {
        $this->verify_nonce( 'cl_admin_nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $attributes = isset( $_POST['attributes'] ) ? $_POST['attributes'] : array();
        $base_price = isset( $_POST['base_price'] ) ? floatval( $_POST['base_price'] ) : 0;

        if ( ! $post_id || empty( $attributes ) ) {
            wp_send_json_error( array( 'message' => __( 'נתונים חסרים', 'commerce-layer' ) ) );
        }

        // Generate all combinations
        $combinations = array( array() );

        foreach ( $attributes as $attr_slug => $values ) {
            if ( empty( $values ) ) {
                continue;
            }

            $new_combinations = array();
            foreach ( $combinations as $combo ) {
                foreach ( $values as $value ) {
                    $new_combo = $combo;
                    $new_combo[ $attr_slug ] = $value;
                    $new_combinations[] = $new_combo;
                }
            }
            $combinations = $new_combinations;
        }

        // Create variants
        $created = 0;
        foreach ( $combinations as $combo ) {
            if ( empty( $combo ) ) {
                continue;
            }

            $variant = new CL_Variant();
            $variant->save( array(
                'post_id'    => $post_id,
                'attributes' => $combo,
                'price'      => $base_price,
            ) );
            $created++;
        }

        // Get all variants for this post
        $variants = CL_Variant::get_by_post( $post_id );
        $variants_data = array();
        foreach ( $variants as $v ) {
            $variants_data[] = $v->get_data();
        }

        wp_send_json_success( array(
            'message'  => sprintf( __( 'נוצרו %d וריאציות', 'commerce-layer' ), $created ),
            'variants' => $variants_data,
        ) );
    }
}
