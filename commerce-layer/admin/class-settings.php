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
            'general'  => __( 'כללי', 'commerce-layer' ),
            'checkout' => __( 'תשלום', 'commerce-layer' ),
            'display'  => __( 'תצוגה', 'commerce-layer' ),
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
                update_option( 'cl_floating_bar_enabled', isset( $_POST['cl_floating_bar_enabled'] ) ? 'yes' : 'no' );
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
