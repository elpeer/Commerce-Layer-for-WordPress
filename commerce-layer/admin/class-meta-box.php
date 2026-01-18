<?php
/**
 * Meta Box Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Meta_Box {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
    }

    /**
     * Add meta boxes to enabled post types
     */
    public function add_meta_boxes() {
        $post_types = CL_Core::get_enabled_post_types();

        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'cl_commerce_meta_box',
                __( 'Commerce Layer', 'commerce-layer' ),
                array( $this, 'render_meta_box' ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render meta box content
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'cl_save_meta_box', 'cl_meta_box_nonce' );

        $product = new CL_Product( $post->ID );

        // Get global attributes
        global $wpdb;
        $attributes_table = $wpdb->prefix . 'cl_attributes';
        $values_table = $wpdb->prefix . 'cl_attribute_values';

        $global_attributes = $wpdb->get_results( "SELECT * FROM $attributes_table ORDER BY sort_order ASC", ARRAY_A );
        foreach ( $global_attributes as &$attr ) {
            $attr['values'] = $wpdb->get_results(
                $wpdb->prepare( "SELECT * FROM $values_table WHERE attribute_id = %d ORDER BY sort_order ASC", $attr['id'] ),
                ARRAY_A
            );
        }

        // Get variants
        $variants = $product->get_variants();

        include CL_PLUGIN_DIR . 'admin/views/meta-box.php';
    }

    /**
     * Save meta box data
     */
    public function save_meta_box( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['cl_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cl_meta_box_nonce'], 'cl_save_meta_box' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if commerce enabled for this post type
        if ( ! CL_Core::is_commerce_enabled( $post->post_type ) ) {
            return;
        }

        // Get product
        $product = new CL_Product( $post_id );

        // Save basic data
        $data = array(
            'enabled'       => isset( $_POST['cl_enabled'] ) ? 'yes' : 'no',
            'price'         => isset( $_POST['cl_price'] ) && $_POST['cl_price'] !== '' ? floatval( $_POST['cl_price'] ) : '',
            'sale_price'    => isset( $_POST['cl_sale_price'] ) && $_POST['cl_sale_price'] !== '' ? floatval( $_POST['cl_sale_price'] ) : '',
            'purchase_mode' => isset( $_POST['cl_purchase_mode'] ) ? sanitize_text_field( $_POST['cl_purchase_mode'] ) : 'both',
            'min_quantity'  => isset( $_POST['cl_min_quantity'] ) ? absint( $_POST['cl_min_quantity'] ) : 1,
            'max_quantity'  => isset( $_POST['cl_max_quantity'] ) ? absint( $_POST['cl_max_quantity'] ) : 0,
            'has_variants'  => isset( $_POST['cl_has_variants'] ) ? 'yes' : 'no',
            'attributes'    => isset( $_POST['cl_attributes'] ) ? array_map( 'sanitize_text_field', $_POST['cl_attributes'] ) : array(),
        );

        $product->save( $data );

        // Save variants
        if ( isset( $_POST['cl_variants'] ) && is_array( $_POST['cl_variants'] ) ) {
            $this->save_variants( $post_id, $_POST['cl_variants'] );
        }
    }

    /**
     * Save variants
     */
    private function save_variants( $post_id, $variants_data ) {
        $existing_ids = array();

        foreach ( $variants_data as $variant_data ) {
            $variant_id = isset( $variant_data['id'] ) ? absint( $variant_data['id'] ) : 0;

            // Skip deleted variants
            if ( isset( $variant_data['delete'] ) && $variant_data['delete'] ) {
                if ( $variant_id ) {
                    $variant = new CL_Variant( $variant_id );
                    $variant->delete();
                }
                continue;
            }

            $variant = new CL_Variant( $variant_id );

            $data = array(
                'post_id'        => $post_id,
                'sku'            => isset( $variant_data['sku'] ) ? sanitize_text_field( $variant_data['sku'] ) : '',
                'attributes'     => isset( $variant_data['attributes'] ) ? $variant_data['attributes'] : array(),
                'price'          => isset( $variant_data['price'] ) ? floatval( $variant_data['price'] ) : 0,
                'sale_price'     => isset( $variant_data['sale_price'] ) && $variant_data['sale_price'] !== '' ? floatval( $variant_data['sale_price'] ) : null,
                'image_id'       => isset( $variant_data['image_id'] ) ? absint( $variant_data['image_id'] ) : null,
                'stock_status'   => isset( $variant_data['stock_status'] ) ? sanitize_text_field( $variant_data['stock_status'] ) : 'instock',
                'stock_quantity' => isset( $variant_data['stock_quantity'] ) && $variant_data['stock_quantity'] !== '' ? absint( $variant_data['stock_quantity'] ) : null,
                'sort_order'     => isset( $variant_data['sort_order'] ) ? absint( $variant_data['sort_order'] ) : 0,
            );

            $new_id = $variant->save( $data );
            $existing_ids[] = $new_id;
        }
    }
}
