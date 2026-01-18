<?php
/**
 * Products List Class
 *
 * Handles the products management table for bulk editing
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Products_List {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_cl_save_product_row', array( $this, 'ajax_save_product_row' ) );
        add_action( 'wp_ajax_cl_bulk_save_products', array( $this, 'ajax_bulk_save_products' ) );
    }

    /**
     * Get all products from enabled post types
     */
    public function get_products( $args = array() ) {
        $defaults = array(
            'post_type'      => '',
            'posts_per_page' => 50,
            'paged'          => 1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'search'         => '',
        );

        $args = wp_parse_args( $args, $defaults );

        // Get enabled post types
        $enabled_post_types = CL_Core::get_enabled_post_types();

        if ( empty( $enabled_post_types ) ) {
            return array(
                'products' => array(),
                'total'    => 0,
                'pages'    => 0,
            );
        }

        // Filter by specific post type if provided
        if ( ! empty( $args['post_type'] ) && in_array( $args['post_type'], $enabled_post_types ) ) {
            $post_types = array( $args['post_type'] );
        } else {
            $post_types = $enabled_post_types;
        }

        $query_args = array(
            'post_type'      => $post_types,
            'posts_per_page' => $args['posts_per_page'],
            'paged'          => $args['paged'],
            'orderby'        => $args['orderby'],
            'order'          => $args['order'],
            'post_status'    => 'publish',
        );

        // Search
        if ( ! empty( $args['search'] ) ) {
            $query_args['s'] = $args['search'];
        }

        $query = new WP_Query( $query_args );

        $products = array();
        foreach ( $query->posts as $post ) {
            $product = new CL_Product( $post->ID );
            $products[] = array(
                'post'    => $post,
                'product' => $product,
            );
        }

        return array(
            'products' => $products,
            'total'    => $query->found_posts,
            'pages'    => $query->max_num_pages,
        );
    }

    /**
     * AJAX: Save single product row
     */
    public function ajax_save_product_row() {
        check_ajax_referer( 'cl_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'מזהה פוסט חסר', 'commerce-layer' ) ) );
        }

        $product = new CL_Product( $post_id );

        $data = array(
            'enabled'       => isset( $_POST['enabled'] ) && $_POST['enabled'] === 'yes' ? 'yes' : 'no',
            'price'         => isset( $_POST['price'] ) && $_POST['price'] !== '' ? floatval( $_POST['price'] ) : '',
            'sale_price'    => isset( $_POST['sale_price'] ) && $_POST['sale_price'] !== '' ? floatval( $_POST['sale_price'] ) : '',
            'purchase_mode' => isset( $_POST['purchase_mode'] ) ? sanitize_text_field( $_POST['purchase_mode'] ) : 'both',
            'min_quantity'  => isset( $_POST['min_quantity'] ) ? absint( $_POST['min_quantity'] ) : 1,
            'max_quantity'  => isset( $_POST['max_quantity'] ) ? absint( $_POST['max_quantity'] ) : 0,
        );

        $product->save( $data );

        wp_send_json_success( array(
            'message' => __( 'נשמר בהצלחה', 'commerce-layer' ),
            'post_id' => $post_id,
        ) );
    }

    /**
     * AJAX: Bulk save products
     */
    public function ajax_bulk_save_products() {
        check_ajax_referer( 'cl_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'אין הרשאה', 'commerce-layer' ) ) );
        }

        $products_data = isset( $_POST['products'] ) ? $_POST['products'] : array();

        if ( empty( $products_data ) ) {
            wp_send_json_error( array( 'message' => __( 'אין נתונים לשמירה', 'commerce-layer' ) ) );
        }

        $saved = 0;
        $errors = array();

        foreach ( $products_data as $product_data ) {
            $post_id = isset( $product_data['post_id'] ) ? absint( $product_data['post_id'] ) : 0;

            if ( ! $post_id ) {
                continue;
            }

            $product = new CL_Product( $post_id );

            $data = array(
                'enabled'       => isset( $product_data['enabled'] ) && $product_data['enabled'] === 'yes' ? 'yes' : 'no',
                'price'         => isset( $product_data['price'] ) && $product_data['price'] !== '' ? floatval( $product_data['price'] ) : '',
                'sale_price'    => isset( $product_data['sale_price'] ) && $product_data['sale_price'] !== '' ? floatval( $product_data['sale_price'] ) : '',
                'purchase_mode' => isset( $product_data['purchase_mode'] ) ? sanitize_text_field( $product_data['purchase_mode'] ) : 'both',
                'min_quantity'  => isset( $product_data['min_quantity'] ) ? absint( $product_data['min_quantity'] ) : 1,
                'max_quantity'  => isset( $product_data['max_quantity'] ) ? absint( $product_data['max_quantity'] ) : 0,
            );

            $product->save( $data );
            $saved++;
        }

        wp_send_json_success( array(
            'message' => sprintf( __( '%d מוצרים נשמרו בהצלחה', 'commerce-layer' ), $saved ),
            'saved'   => $saved,
        ) );
    }

    /**
     * Render products management page
     */
    public function render() {
        // Get enabled post types for filter
        $enabled_post_types = CL_Core::get_enabled_post_types();
        $post_type_objects = array();
        foreach ( $enabled_post_types as $pt ) {
            $post_type_objects[ $pt ] = get_post_type_object( $pt );
        }

        // Current filters
        $current_post_type = isset( $_GET['post_type_filter'] ) ? sanitize_text_field( $_GET['post_type_filter'] ) : '';
        $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
        $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

        // Get products
        $result = $this->get_products( array(
            'post_type' => $current_post_type,
            'paged'     => $current_page,
            'search'    => $search,
        ) );

        $products = $result['products'];
        $total = $result['total'];
        $pages = $result['pages'];

        include CL_PLUGIN_DIR . 'admin/views/products-list.php';
    }
}
