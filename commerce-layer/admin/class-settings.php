<?php
/**
 * Settings Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Settings {

    /**
     * Settings tabs
     */
    private $tabs = array();

    /**
     * Current tab
     */
    private $current_tab = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->tabs = array(
            'general'      => __( 'כללי', 'commerce-layer' ),
            'checkout'     => __( 'תשלום', 'commerce-layer' ),
            'shipping'     => __( 'משלוח', 'commerce-layer' ),
            'display'      => __( 'תצוגה', 'commerce-layer' ),
            'styling'      => __( 'עיצוב', 'commerce-layer' ),
            'translations' => __( 'תרגום טקסטים', 'commerce-layer' ),
        );

        $this->current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General settings
        register_setting( 'cl_general_settings', 'cl_enabled_post_types', array(
            'sanitize_callback' => array( $this, 'sanitize_post_types' ),
        ) );
        register_setting( 'cl_general_settings', 'cl_currency' );
        register_setting( 'cl_general_settings', 'cl_currency_symbol' );
        register_setting( 'cl_general_settings', 'cl_currency_position' );
        register_setting( 'cl_general_settings', 'cl_thousand_separator' );
        register_setting( 'cl_general_settings', 'cl_decimal_separator' );
        register_setting( 'cl_general_settings', 'cl_decimals' );
        register_setting( 'cl_general_settings', 'cl_purchase_mode' );

        // Checkout settings
        register_setting( 'cl_checkout_settings', 'cl_payment_gateway' );
        register_setting( 'cl_checkout_settings', 'cl_tranzila_terminal' );
        register_setting( 'cl_checkout_settings', 'cl_tranzila_password' );
        register_setting( 'cl_checkout_settings', 'cl_cardcom_terminal' );
        register_setting( 'cl_checkout_settings', 'cl_cardcom_username' );
        register_setting( 'cl_checkout_settings', 'cl_paypal_client_id' );
        register_setting( 'cl_checkout_settings', 'cl_cart_page_id' );
        register_setting( 'cl_checkout_settings', 'cl_checkout_page_id' );
        register_setting( 'cl_checkout_settings', 'cl_thank_you_page_id' );

        // Display settings
        register_setting( 'cl_display_settings', 'cl_display_mode' );
        register_setting( 'cl_display_settings', 'cl_display_position' );
        register_setting( 'cl_display_settings', 'cl_add_to_cart_text' );
        register_setting( 'cl_display_settings', 'cl_buy_now_text' );
        register_setting( 'cl_display_settings', 'cl_floating_bar_enabled' );
    }

    /**
     * Sanitize post types
     */
    public function sanitize_post_types( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }
        return array_map( 'sanitize_text_field', $value );
    }

    /**
     * Render settings page
     */
    public function render() {
        // Handle save
        if ( isset( $_POST['submit'] ) && check_admin_referer( 'cl_settings_nonce', 'cl_settings_nonce' ) ) {
            $this->save_settings();
        }

        include CL_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Save settings manually (for post types array)
     */
    private function save_settings() {
        $tab = $this->current_tab;

        switch ( $tab ) {
            case 'general':
                $post_types = isset( $_POST['cl_enabled_post_types'] ) ? $_POST['cl_enabled_post_types'] : array();
                update_option( 'cl_enabled_post_types', $this->sanitize_post_types( $post_types ) );
                update_option( 'cl_currency', sanitize_text_field( $_POST['cl_currency'] ?? 'ILS' ) );
                update_option( 'cl_currency_symbol', sanitize_text_field( $_POST['cl_currency_symbol'] ?? '₪' ) );
                update_option( 'cl_currency_position', sanitize_text_field( $_POST['cl_currency_position'] ?? 'right' ) );
                update_option( 'cl_thousand_separator', sanitize_text_field( $_POST['cl_thousand_separator'] ?? ',' ) );
                update_option( 'cl_decimal_separator', sanitize_text_field( $_POST['cl_decimal_separator'] ?? '.' ) );
                update_option( 'cl_decimals', absint( $_POST['cl_decimals'] ?? 2 ) );
                update_option( 'cl_purchase_mode', sanitize_text_field( $_POST['cl_purchase_mode'] ?? 'both' ) );
                break;

            case 'checkout':
                // Checkout mode (payment or lead)
                update_option( 'cl_checkout_mode', sanitize_text_field( $_POST['cl_checkout_mode'] ?? 'payment' ) );
                update_option( 'cl_lead_button_text', sanitize_text_field( $_POST['cl_lead_button_text'] ?? __( 'שלח פנייה', 'commerce-layer' ) ) );

                // Payment gateway settings
                update_option( 'cl_payment_gateway', sanitize_text_field( $_POST['cl_payment_gateway'] ?? 'tranzila' ) );
                update_option( 'cl_tranzila_terminal', sanitize_text_field( $_POST['cl_tranzila_terminal'] ?? '' ) );
                update_option( 'cl_tranzila_password', sanitize_text_field( $_POST['cl_tranzila_password'] ?? '' ) );
                update_option( 'cl_cardcom_terminal', sanitize_text_field( $_POST['cl_cardcom_terminal'] ?? '' ) );
                update_option( 'cl_cardcom_username', sanitize_text_field( $_POST['cl_cardcom_username'] ?? '' ) );
                update_option( 'cl_paypal_client_id', sanitize_text_field( $_POST['cl_paypal_client_id'] ?? '' ) );
                update_option( 'cl_cart_page_id', absint( $_POST['cl_cart_page_id'] ?? 0 ) );
                update_option( 'cl_checkout_page_id', absint( $_POST['cl_checkout_page_id'] ?? 0 ) );
                update_option( 'cl_thank_you_page_id', absint( $_POST['cl_thank_you_page_id'] ?? 0 ) );
                break;

            case 'display':
                update_option( 'cl_display_mode', sanitize_text_field( $_POST['cl_display_mode'] ?? 'auto' ) );
                update_option( 'cl_display_position', sanitize_text_field( $_POST['cl_display_position'] ?? 'after_content' ) );
                update_option( 'cl_add_to_cart_text', sanitize_text_field( $_POST['cl_add_to_cart_text'] ?? '' ) );
                update_option( 'cl_buy_now_text', sanitize_text_field( $_POST['cl_buy_now_text'] ?? '' ) );
                update_option( 'cl_continue_shopping_text', sanitize_text_field( $_POST['cl_continue_shopping_text'] ?? __( 'המשך בקנייה', 'commerce-layer' ) ) );
                update_option( 'cl_floating_bar_enabled', isset( $_POST['cl_floating_bar_enabled'] ) ? 'yes' : 'no' );

                // Custom button injection settings
                update_option( 'cl_custom_injection_enabled', isset( $_POST['cl_custom_injection_enabled'] ) ? 'yes' : 'no' );
                update_option( 'cl_custom_injection_selector', sanitize_text_field( $_POST['cl_custom_injection_selector'] ?? '' ) );
                update_option( 'cl_custom_injection_position', sanitize_text_field( $_POST['cl_custom_injection_position'] ?? 'after' ) );
                update_option( 'cl_custom_injection_show_price', isset( $_POST['cl_custom_injection_show_price'] ) ? 'yes' : 'no' );

                // Floating cart icon settings
                update_option( 'cl_floating_cart_icon_enabled', isset( $_POST['cl_floating_cart_icon_enabled'] ) ? 'yes' : 'no' );
                update_option( 'cl_cart_icon_desktop_position', sanitize_text_field( $_POST['cl_cart_icon_desktop_position'] ?? 'top-left' ) );
                update_option( 'cl_cart_icon_desktop_offset', absint( $_POST['cl_cart_icon_desktop_offset'] ?? 20 ) );
                update_option( 'cl_cart_icon_mobile_position', sanitize_text_field( $_POST['cl_cart_icon_mobile_position'] ?? 'bottom-right' ) );
                update_option( 'cl_cart_icon_mobile_offset', absint( $_POST['cl_cart_icon_mobile_offset'] ?? 20 ) );
                break;

            case 'shipping':
                // Shipping methods (JSON array)
                $shipping_methods = array();
                if ( isset( $_POST['cl_shipping_method_name'] ) && is_array( $_POST['cl_shipping_method_name'] ) ) {
                    foreach ( $_POST['cl_shipping_method_name'] as $index => $name ) {
                        if ( ! empty( $name ) ) {
                            $shipping_methods[] = array(
                                'name'    => sanitize_text_field( $name ),
                                'price'   => floatval( $_POST['cl_shipping_method_price'][ $index ] ?? 0 ),
                                'enabled' => isset( $_POST['cl_shipping_method_enabled'][ $index ] ) ? true : false,
                            );
                        }
                    }
                }
                update_option( 'cl_shipping_methods', $shipping_methods );
                update_option( 'cl_free_shipping_threshold', floatval( $_POST['cl_free_shipping_threshold'] ?? 0 ) );
                update_option( 'cl_show_discount_in_cart', isset( $_POST['cl_show_discount_in_cart'] ) ? 'yes' : 'no' );
                break;

            case 'styling':
                // Theme preset
                update_option( 'cl_theme_preset', sanitize_text_field( $_POST['cl_theme_preset'] ?? 'modern' ) );

                // Custom colors (only saved if preset is 'custom')
                update_option( 'cl_color_primary', sanitize_hex_color( $_POST['cl_color_primary'] ?? '#2563eb' ) );
                update_option( 'cl_color_primary_dark', sanitize_hex_color( $_POST['cl_color_primary_dark'] ?? '#1e40af' ) );
                update_option( 'cl_color_secondary', sanitize_hex_color( $_POST['cl_color_secondary'] ?? '#64748b' ) );
                update_option( 'cl_color_text', sanitize_hex_color( $_POST['cl_color_text'] ?? '#1e293b' ) );
                update_option( 'cl_color_background', sanitize_hex_color( $_POST['cl_color_background'] ?? '#ffffff' ) );
                update_option( 'cl_color_success', sanitize_hex_color( $_POST['cl_color_success'] ?? '#10b981' ) );
                update_option( 'cl_color_danger', sanitize_hex_color( $_POST['cl_color_danger'] ?? '#ef4444' ) );

                // Button styling
                update_option( 'cl_button_corners', sanitize_text_field( $_POST['cl_button_corners'] ?? 'rounded' ) );
                update_option( 'cl_button_style', sanitize_text_field( $_POST['cl_button_style'] ?? 'gradient' ) );

                // Add to cart button colors
                update_option( 'cl_add_to_cart_bg', sanitize_hex_color( $_POST['cl_add_to_cart_bg'] ?? '#2563eb' ) );
                update_option( 'cl_add_to_cart_text_color', sanitize_hex_color( $_POST['cl_add_to_cart_text_color'] ?? '#ffffff' ) );

                // Buy now button colors
                update_option( 'cl_buy_now_bg', sanitize_hex_color( $_POST['cl_buy_now_bg'] ?? '#10b981' ) );
                update_option( 'cl_buy_now_text_color', sanitize_hex_color( $_POST['cl_buy_now_text_color'] ?? '#ffffff' ) );

                // Continue shopping button colors
                update_option( 'cl_continue_shopping_bg', sanitize_hex_color( $_POST['cl_continue_shopping_bg'] ?? '#ffffff' ) );
                update_option( 'cl_continue_shopping_text_color', sanitize_hex_color( $_POST['cl_continue_shopping_text_color'] ?? '#1e293b' ) );
                update_option( 'cl_continue_shopping_border_color', sanitize_hex_color( $_POST['cl_continue_shopping_border_color'] ?? '#2563eb' ) );

                // Cart icon colors
                update_option( 'cl_cart_icon_color', sanitize_hex_color( $_POST['cl_cart_icon_color'] ?? '#2563eb' ) );
                update_option( 'cl_cart_icon_bg', sanitize_hex_color( $_POST['cl_cart_icon_bg'] ?? '#ffffff' ) );
                update_option( 'cl_cart_icon_badge_bg', sanitize_hex_color( $_POST['cl_cart_icon_badge_bg'] ?? '#ef4444' ) );

                // Side cart settings
                update_option( 'cl_side_cart_width', absint( $_POST['cl_side_cart_width'] ?? 420 ) );
                update_option( 'cl_side_cart_side', sanitize_text_field( $_POST['cl_side_cart_side'] ?? 'right' ) );
                update_option( 'cl_side_cart_shipping_bar', isset( $_POST['cl_side_cart_shipping_bar'] ) ? 'yes' : 'no' );
                update_option( 'cl_side_cart_urgency_timer', isset( $_POST['cl_side_cart_urgency_timer'] ) ? 'yes' : 'no' );

                // Checkout settings
                update_option( 'cl_checkout_max_width', absint( $_POST['cl_checkout_max_width'] ?? 88 ) );

                // Custom CSS
                update_option( 'cl_custom_css', wp_strip_all_tags( $_POST['cl_custom_css'] ?? '' ) );
                break;

            case 'translations':
                // Button texts
                update_option( 'cl_text_add_to_cart', sanitize_text_field( $_POST['cl_text_add_to_cart'] ?? __( 'הוסף לסל', 'commerce-layer' ) ) );
                update_option( 'cl_text_buy_now', sanitize_text_field( $_POST['cl_text_buy_now'] ?? __( 'קנה עכשיו', 'commerce-layer' ) ) );
                update_option( 'cl_text_view_cart', sanitize_text_field( $_POST['cl_text_view_cart'] ?? __( 'צפה בסל', 'commerce-layer' ) ) );
                update_option( 'cl_text_checkout', sanitize_text_field( $_POST['cl_text_checkout'] ?? __( 'לתשלום', 'commerce-layer' ) ) );
                update_option( 'cl_text_continue_shopping', sanitize_text_field( $_POST['cl_text_continue_shopping'] ?? __( 'המשך בקנייה', 'commerce-layer' ) ) );
                update_option( 'cl_text_continue_to_checkout', sanitize_text_field( $_POST['cl_text_continue_to_checkout'] ?? __( 'המשך לתשלום', 'commerce-layer' ) ) );

                // Cart texts
                update_option( 'cl_text_cart_title', sanitize_text_field( $_POST['cl_text_cart_title'] ?? __( 'סל קניות', 'commerce-layer' ) ) );
                update_option( 'cl_text_cart_empty', sanitize_text_field( $_POST['cl_text_cart_empty'] ?? __( 'הסל שלך ריק', 'commerce-layer' ) ) );
                update_option( 'cl_text_product', sanitize_text_field( $_POST['cl_text_product'] ?? __( 'מוצר', 'commerce-layer' ) ) );
                update_option( 'cl_text_price', sanitize_text_field( $_POST['cl_text_price'] ?? __( 'מחיר', 'commerce-layer' ) ) );
                update_option( 'cl_text_quantity', sanitize_text_field( $_POST['cl_text_quantity'] ?? __( 'כמות', 'commerce-layer' ) ) );
                update_option( 'cl_text_total', sanitize_text_field( $_POST['cl_text_total'] ?? __( 'סה"כ', 'commerce-layer' ) ) );
                update_option( 'cl_text_subtotal', sanitize_text_field( $_POST['cl_text_subtotal'] ?? __( 'סיכום ביניים', 'commerce-layer' ) ) );
                update_option( 'cl_text_remove', sanitize_text_field( $_POST['cl_text_remove'] ?? __( 'הסר', 'commerce-layer' ) ) );

                // Shipping texts
                update_option( 'cl_text_free_shipping', sanitize_text_field( $_POST['cl_text_free_shipping'] ?? __( 'זכאי למשלוח חינם!', 'commerce-layer' ) ) );
                update_option( 'cl_text_add_for_free_shipping', sanitize_text_field( $_POST['cl_text_add_for_free_shipping'] ?? __( 'הוסף %s לקבלת משלוח חינם!', 'commerce-layer' ) ) );
                update_option( 'cl_text_shipping', sanitize_text_field( $_POST['cl_text_shipping'] ?? __( 'משלוח', 'commerce-layer' ) ) );

                // Checkout texts
                update_option( 'cl_text_customer_details', sanitize_text_field( $_POST['cl_text_customer_details'] ?? __( 'פרטי הלקוח', 'commerce-layer' ) ) );
                update_option( 'cl_text_first_name', sanitize_text_field( $_POST['cl_text_first_name'] ?? __( 'שם פרטי', 'commerce-layer' ) ) );
                update_option( 'cl_text_last_name', sanitize_text_field( $_POST['cl_text_last_name'] ?? __( 'שם משפחה', 'commerce-layer' ) ) );
                update_option( 'cl_text_email', sanitize_text_field( $_POST['cl_text_email'] ?? __( 'אימייל', 'commerce-layer' ) ) );
                update_option( 'cl_text_phone', sanitize_text_field( $_POST['cl_text_phone'] ?? __( 'טלפון', 'commerce-layer' ) ) );
                update_option( 'cl_text_address', sanitize_text_field( $_POST['cl_text_address'] ?? __( 'כתובת', 'commerce-layer' ) ) );
                update_option( 'cl_text_city', sanitize_text_field( $_POST['cl_text_city'] ?? __( 'עיר', 'commerce-layer' ) ) );
                update_option( 'cl_text_notes', sanitize_text_field( $_POST['cl_text_notes'] ?? __( 'הערות להזמנה', 'commerce-layer' ) ) );
                update_option( 'cl_text_order_summary', sanitize_text_field( $_POST['cl_text_order_summary'] ?? __( 'סיכום הזמנה', 'commerce-layer' ) ) );
                update_option( 'cl_text_place_order', sanitize_text_field( $_POST['cl_text_place_order'] ?? __( 'לתשלום מאובטח', 'commerce-layer' ) ) );

                // Discount texts
                update_option( 'cl_text_coupon', sanitize_text_field( $_POST['cl_text_coupon'] ?? __( 'קופון הנחה', 'commerce-layer' ) ) );
                update_option( 'cl_text_coupon_placeholder', sanitize_text_field( $_POST['cl_text_coupon_placeholder'] ?? __( 'הזן קוד קופון', 'commerce-layer' ) ) );
                update_option( 'cl_text_apply_coupon', sanitize_text_field( $_POST['cl_text_apply_coupon'] ?? __( 'החל קופון', 'commerce-layer' ) ) );
                update_option( 'cl_text_savings', sanitize_text_field( $_POST['cl_text_savings'] ?? __( 'סה"כ חיסכון', 'commerce-layer' ) ) );
                update_option( 'cl_text_discount', sanitize_text_field( $_POST['cl_text_discount'] ?? __( 'הנחה', 'commerce-layer' ) ) );

                // Thank you page texts
                update_option( 'cl_text_thank_you', sanitize_text_field( $_POST['cl_text_thank_you'] ?? __( 'תודה על הזמנתך!', 'commerce-layer' ) ) );
                update_option( 'cl_text_order_received', sanitize_text_field( $_POST['cl_text_order_received'] ?? __( 'ההזמנה התקבלה בהצלחה', 'commerce-layer' ) ) );
                update_option( 'cl_text_order_number', sanitize_text_field( $_POST['cl_text_order_number'] ?? __( 'מספר הזמנה', 'commerce-layer' ) ) );

                // Other texts
                update_option( 'cl_text_select', sanitize_text_field( $_POST['cl_text_select'] ?? __( 'בחר', 'commerce-layer' ) ) );
                update_option( 'cl_text_sale', sanitize_text_field( $_POST['cl_text_sale'] ?? __( 'מבצע', 'commerce-layer' ) ) );
                update_option( 'cl_text_out_of_stock', sanitize_text_field( $_POST['cl_text_out_of_stock'] ?? __( 'אזל מהמלאי', 'commerce-layer' ) ) );
                update_option( 'cl_text_added_to_cart', sanitize_text_field( $_POST['cl_text_added_to_cart'] ?? __( 'הפריט נוסף לסל', 'commerce-layer' ) ) );
                break;
        }

        add_settings_error( 'cl_settings', 'settings_updated', __( 'ההגדרות נשמרו', 'commerce-layer' ), 'success' );
    }

    /**
     * Get available post types
     */
    public function get_available_post_types() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );

        $excluded = array( 'attachment' );

        $available = array();
        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, $excluded ) ) {
                continue;
            }
            $available[ $post_type->name ] = $post_type->label;
        }

        return $available;
    }

    /**
     * Get pages for dropdown
     */
    public function get_pages_dropdown( $name, $selected = 0 ) {
        wp_dropdown_pages( array(
            'name'              => $name,
            'id'                => $name,
            'selected'          => $selected,
            'show_option_none'  => __( '— בחר עמוד —', 'commerce-layer' ),
            'option_none_value' => 0,
            'class'             => 'regular-text',
        ) );
    }
}
