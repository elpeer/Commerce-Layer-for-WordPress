<?php
/**
 * Variant Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Variant {

    /**
     * Variant ID
     */
    private $id;

    /**
     * Variant data
     */
    private $data;

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
     * Load variant data
     */
    private function load() {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_variants';
        $this->data = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $this->id ),
            ARRAY_A
        );
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
     * Get post ID
     */
    public function get_post_id() {
        return $this->data ? absint( $this->data['post_id'] ) : 0;
    }

    /**
     * Get SKU
     */
    public function get_sku() {
        return $this->data ? $this->data['sku'] : '';
    }

    /**
     * Get attributes
     */
    public function get_attributes() {
        if ( ! $this->data ) {
            return array();
        }
        $attrs = json_decode( $this->data['attributes'], true );
        return is_array( $attrs ) ? $attrs : array();
    }

    /**
     * Get variant name from attributes
     */
    public function get_name() {
        $attrs = $this->get_attributes();
        if ( empty( $attrs ) ) {
            return '';
        }

        $parts = array();
        foreach ( $attrs as $attr_slug => $value ) {
            $parts[] = $value;
        }

        return implode( ' / ', $parts );
    }

    /**
     * Get regular price
     */
    public function get_regular_price() {
        return $this->data ? floatval( $this->data['price'] ) : 0;
    }

    /**
     * Get sale price
     */
    public function get_sale_price() {
        if ( ! $this->data || empty( $this->data['sale_price'] ) ) {
            return false;
        }
        return floatval( $this->data['sale_price'] );
    }

    /**
     * Get active price
     */
    public function get_price() {
        $sale = $this->get_sale_price();
        if ( false !== $sale && $sale > 0 ) {
            return $sale;
        }
        return $this->get_regular_price();
    }

    /**
     * Check if on sale
     */
    public function is_on_sale() {
        $sale = $this->get_sale_price();
        return false !== $sale && $sale > 0 && $sale < $this->get_regular_price();
    }

    /**
     * Get image ID
     */
    public function get_image_id() {
        return $this->data ? absint( $this->data['image_id'] ) : 0;
    }

    /**
     * Get image URL
     */
    public function get_image_url( $size = 'thumbnail' ) {
        $image_id = $this->get_image_id();
        if ( ! $image_id ) {
            return '';
        }
        return wp_get_attachment_image_url( $image_id, $size );
    }

    /**
     * Get stock status
     */
    public function get_stock_status() {
        return $this->data ? $this->data['stock_status'] : 'instock';
    }

    /**
     * Check if in stock
     */
    public function is_in_stock() {
        return 'instock' === $this->get_stock_status();
    }

    /**
     * Get stock quantity
     */
    public function get_stock_quantity() {
        return $this->data ? intval( $this->data['stock_quantity'] ) : null;
    }

    /**
     * Save variant
     */
    public function save( $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_variants';

        $defaults = array(
            'post_id'        => 0,
            'sku'            => '',
            'attributes'     => '{}',
            'price'          => 0,
            'sale_price'     => null,
            'image_id'       => null,
            'stock_status'   => 'instock',
            'stock_quantity' => null,
            'sort_order'     => 0,
        );

        $data = wp_parse_args( $data, $defaults );

        // Ensure attributes is JSON
        if ( is_array( $data['attributes'] ) ) {
            $data['attributes'] = json_encode( $data['attributes'] );
        }

        if ( $this->id ) {
            // Update
            $wpdb->update(
                $table,
                array(
                    'sku'            => $data['sku'],
                    'attributes'     => $data['attributes'],
                    'price'          => $data['price'],
                    'sale_price'     => $data['sale_price'],
                    'image_id'       => $data['image_id'],
                    'stock_status'   => $data['stock_status'],
                    'stock_quantity' => $data['stock_quantity'],
                    'sort_order'     => $data['sort_order'],
                ),
                array( 'id' => $this->id ),
                array( '%s', '%s', '%f', '%f', '%d', '%s', '%d', '%d' ),
                array( '%d' )
            );
        } else {
            // Insert
            $wpdb->insert(
                $table,
                array(
                    'post_id'        => $data['post_id'],
                    'sku'            => $data['sku'],
                    'attributes'     => $data['attributes'],
                    'price'          => $data['price'],
                    'sale_price'     => $data['sale_price'],
                    'image_id'       => $data['image_id'],
                    'stock_status'   => $data['stock_status'],
                    'stock_quantity' => $data['stock_quantity'],
                    'sort_order'     => $data['sort_order'],
                ),
                array( '%d', '%s', '%s', '%f', '%f', '%d', '%s', '%d', '%d' )
            );
            $this->id = $wpdb->insert_id;
        }

        $this->load();

        return $this->id;
    }

    /**
     * Delete variant
     */
    public function delete() {
        global $wpdb;

        if ( ! $this->id ) {
            return false;
        }

        $table = $wpdb->prefix . 'cl_variants';
        return $wpdb->delete( $table, array( 'id' => $this->id ), array( '%d' ) );
    }

    /**
     * Get variants by post ID
     */
    public static function get_by_post( $post_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_variants';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE post_id = %d ORDER BY sort_order ASC, id ASC",
                $post_id
            )
        );

        $variants = array();
        foreach ( $results as $row ) {
            $variants[] = new self( $row->id );
        }

        return $variants;
    }

    /**
     * Delete all variants for post
     */
    public static function delete_by_post( $post_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cl_variants';
        return $wpdb->delete( $table, array( 'post_id' => $post_id ), array( '%d' ) );
    }

    /**
     * Get data array
     */
    public function get_data() {
        return array(
            'id'             => $this->id,
            'post_id'        => $this->get_post_id(),
            'sku'            => $this->get_sku(),
            'attributes'     => $this->get_attributes(),
            'name'           => $this->get_name(),
            'price'          => $this->get_price(),
            'regular_price'  => $this->get_regular_price(),
            'sale_price'     => $this->get_sale_price(),
            'on_sale'        => $this->is_on_sale(),
            'image_url'      => $this->get_image_url(),
            'stock_status'   => $this->get_stock_status(),
            'in_stock'       => $this->is_in_stock(),
            'stock_quantity' => $this->get_stock_quantity(),
        );
    }
}
