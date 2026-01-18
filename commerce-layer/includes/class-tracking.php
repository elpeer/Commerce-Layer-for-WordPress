<?php
/**
 * Tracking Class
 *
 * Provides hooks and events for analytics integration with tools like
 * PixelYourSite, Google Analytics, Facebook Pixel, etc.
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Tracking {

    /**
     * Constructor
     */
    public function __construct() {
        // Server-side hooks are already defined through do_action() calls
        // This class adds JavaScript events for client-side tracking

        // Add tracking data to footer
        add_action( 'wp_footer', array( $this, 'output_tracking_script' ) );

        // Add data attributes to checkout page
        add_action( 'cl_checkout_before_form', array( $this, 'add_checkout_tracking_data' ) );

        // Add view_item event on product pages
        add_action( 'cl_product_card_displayed', array( $this, 'track_product_view' ), 10, 2 );
    }

    /**
     * Output tracking JavaScript events
     */
    public function output_tracking_script() {
        ?>
        <script>
        /**
         * Commerce Layer Tracking Events
         *
         * This script fires custom events that can be captured by:
         * - Google Tag Manager (GTM)
         * - PixelYourSite
         * - Facebook Pixel
         * - Google Analytics 4 (GA4)
         * - Any other analytics tool
         *
         * Events fired:
         * - cl_add_to_cart: When product is added to cart
         * - cl_remove_from_cart: When product is removed from cart
         * - cl_view_cart: When cart is viewed
         * - cl_begin_checkout: When checkout begins
         * - cl_purchase: When purchase is completed
         * - cl_lead_submitted: When lead form is submitted
         *
         * Each event includes product/order data in the detail property.
         *
         * Example GTM trigger:
         * Trigger Type: Custom Event
         * Event Name: cl_add_to_cart
         *
         * Example usage in custom code:
         * document.addEventListener('cl_add_to_cart', function(e) {
         *     console.log('Product added:', e.detail);
         *     // Send to your analytics
         * });
         */
        (function() {
            window.clTracking = {
                /**
                 * Fire a tracking event
                 */
                fire: function(eventName, data) {
                    // Fire custom DOM event
                    var event = new CustomEvent(eventName, {
                        detail: data,
                        bubbles: true
                    });
                    document.dispatchEvent(event);

                    // Push to dataLayer for GTM
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': eventName,
                        'cl_event_data': data
                    });

                    // Fire WordPress hook equivalent event name for compatibility
                    var wpEvent = new CustomEvent('cl_' + eventName.replace('cl_', ''), {
                        detail: data,
                        bubbles: true
                    });
                    document.dispatchEvent(wpEvent);

                    // Console log for debugging (only in debug mode)
                    if (window.clDebug) {
                        console.log('[CL Tracking]', eventName, data);
                    }
                },

                /**
                 * Track add to cart
                 */
                addToCart: function(product) {
                    this.fire('cl_add_to_cart', {
                        event_category: 'ecommerce',
                        event_action: 'add_to_cart',
                        currency: product.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(product.price) || 0,
                        items: [{
                            item_id: product.id,
                            item_name: product.name,
                            item_variant: product.variant || '',
                            price: parseFloat(product.price) || 0,
                            quantity: parseInt(product.quantity) || 1
                        }]
                    });
                },

                /**
                 * Track remove from cart
                 */
                removeFromCart: function(product) {
                    this.fire('cl_remove_from_cart', {
                        event_category: 'ecommerce',
                        event_action: 'remove_from_cart',
                        currency: product.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(product.price) || 0,
                        items: [{
                            item_id: product.id,
                            item_name: product.name,
                            item_variant: product.variant || '',
                            price: parseFloat(product.price) || 0,
                            quantity: parseInt(product.quantity) || 1
                        }]
                    });
                },

                /**
                 * Track view cart
                 */
                viewCart: function(cart) {
                    this.fire('cl_view_cart', {
                        event_category: 'ecommerce',
                        event_action: 'view_cart',
                        currency: cart.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(cart.total) || 0,
                        items: cart.items || []
                    });
                },

                /**
                 * Track begin checkout
                 */
                beginCheckout: function(cart) {
                    this.fire('cl_begin_checkout', {
                        event_category: 'ecommerce',
                        event_action: 'begin_checkout',
                        currency: cart.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(cart.total) || 0,
                        items: cart.items || []
                    });
                },

                /**
                 * Track purchase
                 */
                purchase: function(order) {
                    this.fire('cl_purchase', {
                        event_category: 'ecommerce',
                        event_action: 'purchase',
                        transaction_id: order.order_number,
                        currency: order.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(order.total) || 0,
                        shipping: parseFloat(order.shipping) || 0,
                        items: order.items || []
                    });
                },

                /**
                 * Track lead submission
                 */
                leadSubmitted: function(lead) {
                    this.fire('cl_lead_submitted', {
                        event_category: 'lead',
                        event_action: 'lead_submitted',
                        transaction_id: lead.order_number,
                        currency: lead.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(lead.total) || 0,
                        items: lead.items || []
                    });
                },

                /**
                 * Track product view
                 */
                viewItem: function(product) {
                    this.fire('cl_view_item', {
                        event_category: 'ecommerce',
                        event_action: 'view_item',
                        currency: product.currency || '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
                        value: parseFloat(product.price) || 0,
                        items: [{
                            item_id: product.id,
                            item_name: product.name,
                            price: parseFloat(product.price) || 0
                        }]
                    });
                }
            };
        })();
        </script>
        <?php
    }

    /**
     * Add checkout tracking data
     */
    public function add_checkout_tracking_data() {
        $cart = CL_Cart::get_instance();
        $items = $cart->get_contents();
        $totals = $cart->get_totals();

        $tracking_items = array();
        foreach ( $items as $item ) {
            $tracking_items[] = array(
                'item_id'      => $item['post_id'],
                'item_name'    => $item['name'],
                'item_variant' => $item['variant_name'],
                'price'        => floatval( $item['price'] ),
                'quantity'     => intval( $item['quantity'] ),
            );
        }

        $data = array(
            'currency' => get_option( 'cl_currency', 'ILS' ),
            'total'    => $totals['total'],
            'subtotal' => $totals['subtotal'],
            'items'    => $tracking_items,
        );

        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.clTracking) {
                clTracking.beginCheckout(<?php echo json_encode( $data ); ?>);
            }
        });
        </script>
        <?php
    }

    /**
     * Track product view
     */
    public function track_product_view( $product_id, $price ) {
        $product = get_post( $product_id );
        if ( ! $product ) {
            return;
        }

        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.clTracking) {
                clTracking.viewItem({
                    id: <?php echo intval( $product_id ); ?>,
                    name: <?php echo json_encode( $product->post_title ); ?>,
                    price: <?php echo floatval( $price ); ?>,
                    currency: '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>'
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Get available hooks documentation
     */
    public static function get_hooks_documentation() {
        return array(
            'server_side' => array(
                'cl_add_to_cart' => array(
                    'description' => 'Fired when a product is added to cart',
                    'params'      => array( 'post_id', 'quantity', 'variant_id', 'cart_item' ),
                ),
                'cl_remove_from_cart' => array(
                    'description' => 'Fired when a product is removed from cart',
                    'params'      => array( 'cart_key', 'cart_item' ),
                ),
                'cl_cart_updated' => array(
                    'description' => 'Fired when cart is updated (quantity change)',
                    'params'      => array( 'cart' ),
                ),
                'cl_order_created' => array(
                    'description' => 'Fired when a new order is created',
                    'params'      => array( 'order' ),
                ),
                'cl_payment_complete' => array(
                    'description' => 'Fired when payment is completed',
                    'params'      => array( 'order' ),
                ),
                'cl_lead_submitted' => array(
                    'description' => 'Fired when a lead is submitted (lead mode)',
                    'params'      => array( 'order' ),
                ),
                'cl_order_status_changed' => array(
                    'description' => 'Fired when order status changes',
                    'params'      => array( 'order_id', 'new_status', 'old_status' ),
                ),
            ),
            'javascript_events' => array(
                'cl_add_to_cart'      => 'Fired when product is added to cart',
                'cl_remove_from_cart' => 'Fired when product is removed from cart',
                'cl_view_cart'        => 'Fired when cart is viewed',
                'cl_begin_checkout'   => 'Fired when checkout begins',
                'cl_purchase'         => 'Fired when purchase is completed',
                'cl_lead_submitted'   => 'Fired when lead is submitted',
                'cl_view_item'        => 'Fired when product is viewed',
            ),
        );
    }
}

// Initialize tracking
new CL_Tracking();
