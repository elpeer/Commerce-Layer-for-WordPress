<?php
/**
 * Product Class
 *
 * Handles product/commerce data for a post
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Product {

    /**
     * Post ID
     */
    private $post_id;

    /**
     * Post object
     */
    private $post;

    /**
     * Meta cache
     */
    private $meta = array();

    /**
     * Constructor
     */
    public function __construct( $post_id ) {
        $this->post_id = absint( $post_id );
        $this->post    = get_post( $this->post_id );
        $this->load_meta();
    }

    /**
     * Load meta data
     */
    private function load_meta() {
        $this->meta = array(
            'enabled'       => get_post_meta( $this->post_id, '_cl_commerce_enabled', true ),
            'price'         => get_post_meta( $this->post_id, '_cl_price', true ),
            'sale_price'    => get_post_meta( $this->post_id, '_cl_sale_price', true ),
            'purchase_mode' => get_post_meta( $this->post_id, '_cl_purchase_mode', true ),
            'min_quantity'  => get_post_meta( $this->post_id, '_cl_min_quantity', true ),
            'max_quantity'  => get_post_meta( $this->post_id, '_cl_max_quantity', true ),
            'has_variants'  => get_post_meta( $this->post_id, '_cl_has_variants', true ),
            'attributes'    => get_post_meta( $this->post_id, '_cl_attributes', true ),
        );
    }

    /**
     * Check if post exists
     */
    public function exists() {
        return ! empty( $this->post );
    }

    /**
     * Check if commerce is enabled
     */
    public function is_commerce_enabled() {
        return 'yes' === $this->meta['enabled'];
    }

    /**
     * Get post ID
     */
    public function get_id() {
        return $this->post_id;
    }

    /**
     * Get post title
     */
    public function get_title() {
        return $this->post ? $this->post->post_title : '';
    }

    /**
     * Get permalink
     */
    public function get_permalink() {
        return get_permalink( $this->post_id );
    }

    /**
     * Get thumbnail
     */
    public function get_thumbnail( $size = 'thumbnail' ) {
        return get_the_post_thumbnail_url( $this->post_id, $size );
    }

    /**
     * Get regular price
     */
    public function get_regular_price() {
        return $this->meta['price'] !== '' ? floatval( $this->meta['price'] ) : false;
    }

    /**
     * Get sale price
     */
    public function get_sale_price() {
        return $this->meta['sale_price'] !== '' ? floatval( $this->meta['sale_price'] ) : false;
    }

    /**
     * Get active price
     */
    public function get_price( $variant_id = 0 ) {
        // If variant, get variant price
        if ( $variant_id ) {
            $variant = new CL_Variant( $variant_id );
            if ( $variant->exists() && $variant->get_post_id() === $this->post_id ) {
                return $variant->get_price();
            }
        }

        // Get product price
        $sale_price = $this->get_sale_price();
        if ( false !== $sale_price && $sale_price > 0 ) {
            return $sale_price;
        }

        return $this->get_regular_price();
    }

    /**
     * Check if on sale
     */
    public function is_on_sale( $variant_id = 0 ) {
        if ( $variant_id ) {
            $variant = new CL_Variant( $variant_id );
            if ( $variant->exists() ) {
                return $variant->is_on_sale();
            }
        }

        $sale_price = $this->get_sale_price();
        return false !== $sale_price && $sale_price > 0;
    }

    /**
     * Get price HTML
     */
    public function get_price_html( $variant_id = 0 ) {
        $price      = $this->get_price( $variant_id );
        $regular    = $variant_id ? ( new CL_Variant( $variant_id ) )->get_regular_price() : $this->get_regular_price();
        $on_sale    = $this->is_on_sale( $variant_id );

        if ( false === $price ) {
            return '<span class="cl-price cl-no-price">' . __( 'לא הוגדר מחיר', 'commerce-layer' ) . '</span>';
        }

        $html = '<span class="cl-price">';

        if ( $on_sale && $regular ) {
            $html .= '<del class="cl-price-regular">' . CL_Core::format_price( $regular ) . '</del> ';
            $html .= '<ins class="cl-price-sale">' . CL_Core::format_price( $price ) . '</ins>';
        } else {
            $html .= CL_Core::format_price( $price );
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Get purchase mode
     */
    public function get_purchase_mode() {
        $mode = $this->meta['purchase_mode'];
        if ( empty( $mode ) ) {
            return get_option( 'cl_purchase_mode', 'both' );
        }
        return $mode;
    }

    /**
     * Get minimum quantity
     */
    public function get_min_quantity() {
        $min = $this->meta['min_quantity'];
        return ! empty( $min ) ? absint( $min ) : 1;
    }

    /**
     * Get maximum quantity
     */
    public function get_max_quantity() {
        $max = $this->meta['max_quantity'];
        return ! empty( $max ) ? absint( $max ) : 0; // 0 = unlimited
    }

    /**
     * Check if has variants
     */
    public function has_variants() {
        return 'yes' === $this->meta['has_variants'];
    }

    /**
     * Get product attributes
     */
    public function get_attributes() {
        $attributes = $this->meta['attributes'];
        return is_array( $attributes ) ? $attributes : array();
    }

    /**
     * Get variants
     */
    public function get_variants() {
        return CL_Variant::get_by_post( $this->post_id );
    }

    /**
     * Get price range for products with variants
     */
    public function get_price_range() {
        if ( ! $this->has_variants() ) {
            return false;
        }

        $variants = $this->get_variants();
        if ( empty( $variants ) ) {
            return false;
        }

        $prices = array();
        foreach ( $variants as $variant ) {
            $prices[] = $variant->get_price();
        }

        $min = min( $prices );
        $max = max( $prices );

        if ( $min === $max ) {
            return array(
                'min' => $min,
                'max' => $max,
                'html' => CL_Core::format_price( $min ),
            );
        }

        return array(
            'min' => $min,
            'max' => $max,
            'html' => CL_Core::format_price( $min ) . ' - ' . CL_Core::format_price( $max ),
        );
    }

    /**
     * Check if purchasable
     */
    public function is_purchasable() {
        if ( ! $this->is_commerce_enabled() ) {
            return false;
        }

        if ( $this->has_variants() ) {
            $variants = $this->get_variants();
            return ! empty( $variants );
        }

        return false !== $this->get_price();
    }

    /**
     * Save commerce data
     */
    public function save( $data ) {
        $fields = array(
            'enabled'       => '_cl_commerce_enabled',
            'price'         => '_cl_price',
            'sale_price'    => '_cl_sale_price',
            'purchase_mode' => '_cl_purchase_mode',
            'min_quantity'  => '_cl_min_quantity',
            'max_quantity'  => '_cl_max_quantity',
            'has_variants'  => '_cl_has_variants',
            'attributes'    => '_cl_attributes',
        );

        foreach ( $fields as $key => $meta_key ) {
            if ( isset( $data[ $key ] ) ) {
                update_post_meta( $this->post_id, $meta_key, $data[ $key ] );
            }
        }

        $this->load_meta();
    }

    /**
     * Get data array
     */
    public function get_data() {
        return array(
            'id'            => $this->post_id,
            'title'         => $this->get_title(),
            'permalink'     => $this->get_permalink(),
            'thumbnail'     => $this->get_thumbnail(),
            'enabled'       => $this->is_commerce_enabled(),
            'price'         => $this->get_price(),
            'regular_price' => $this->get_regular_price(),
            'sale_price'    => $this->get_sale_price(),
            'on_sale'       => $this->is_on_sale(),
            'purchase_mode' => $this->get_purchase_mode(),
            'min_quantity'  => $this->get_min_quantity(),
            'max_quantity'  => $this->get_max_quantity(),
            'has_variants'  => $this->has_variants(),
            'purchasable'   => $this->is_purchasable(),
        );
    }
}
