<?php
/**
 * Checkout Class
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Checkout {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_cl_process_checkout', array( $this, 'process_checkout' ) );
        add_action( 'wp_ajax_nopriv_cl_process_checkout', array( $this, 'process_checkout' ) );

        // Payment gateway callbacks
        add_action( 'wp_ajax_cl_payment_callback', array( $this, 'payment_callback' ) );
        add_action( 'wp_ajax_nopriv_cl_payment_callback', array( $this, 'payment_callback' ) );

        // IPN/webhook handlers
        add_action( 'init', array( $this, 'register_endpoints' ) );
    }

    /**
     * Register payment endpoints
     */
    public function register_endpoints() {
        add_rewrite_rule(
            '^cl-payment/([^/]+)/?$',
            'index.php?cl_payment_action=$matches[1]',
            'top'
        );

        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'cl_payment_action';
            return $vars;
        } );

        add_action( 'template_redirect', array( $this, 'handle_payment_endpoint' ) );
    }

    /**
     * Handle payment endpoint
     */
    public function handle_payment_endpoint() {
        $action = get_query_var( 'cl_payment_action' );

        if ( ! $action ) {
            return;
        }

        switch ( $action ) {
            case 'success':
                $this->handle_payment_success();
                break;
            case 'cancel':
                $this->handle_payment_cancel();
                break;
            case 'ipn':
                $this->handle_ipn();
                break;
        }

        exit;
    }

    /**
     * Process checkout
     */
    public function process_checkout() {
        // Verify nonce
        if ( ! check_ajax_referer( 'cl_checkout_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'אימות נכשל', 'commerce-layer' ) ) );
        }

        // Get cart
        $cart = CL_Cart::get_instance();

        if ( $cart->is_empty() ) {
            wp_send_json_error( array( 'message' => __( 'הסל ריק', 'commerce-layer' ) ) );
        }

        // Validate customer data
        $validation = $this->validate_customer_data( $_POST );

        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( array( 'message' => $validation->get_error_message() ) );
        }

        // Create order
        $order = CL_Order::create_from_cart( $_POST, $cart );

        if ( is_wp_error( $order ) ) {
            wp_send_json_error( array( 'message' => $order->get_error_message() ) );
        }

        // Get payment gateway
        $gateway = get_option( 'cl_payment_gateway', 'tranzila' );

        // Process payment
        $payment_result = $this->process_payment( $order, $gateway );

        if ( is_wp_error( $payment_result ) ) {
            // Don't delete order, keep it as pending
            wp_send_json_error( array( 'message' => $payment_result->get_error_message() ) );
        }

        // Clear cart on success
        if ( isset( $payment_result['success'] ) && $payment_result['success'] ) {
            $cart->clear();
        }

        wp_send_json_success( $payment_result );
    }

    /**
     * Validate customer data
     */
    private function validate_customer_data( $data ) {
        $errors = array();

        // Required fields
        if ( empty( $data['name'] ) ) {
            $errors[] = __( 'שם מלא נדרש', 'commerce-layer' );
        }

        if ( empty( $data['email'] ) ) {
            $errors[] = __( 'כתובת אימייל נדרשת', 'commerce-layer' );
        } elseif ( ! is_email( $data['email'] ) ) {
            $errors[] = __( 'כתובת אימייל לא תקינה', 'commerce-layer' );
        }

        if ( empty( $data['phone'] ) ) {
            $errors[] = __( 'מספר טלפון נדרש', 'commerce-layer' );
        }

        if ( ! empty( $errors ) ) {
            return new WP_Error( 'validation_error', implode( '<br>', $errors ) );
        }

        return true;
    }

    /**
     * Process payment
     */
    private function process_payment( $order, $gateway ) {
        switch ( $gateway ) {
            case 'tranzila':
                return $this->process_tranzila( $order );
            case 'cardcom':
                return $this->process_cardcom( $order );
            case 'paypal':
                return $this->process_paypal( $order );
            case 'test':
                return $this->process_test_payment( $order );
            default:
                return new WP_Error( 'invalid_gateway', __( 'שער תשלום לא תקין', 'commerce-layer' ) );
        }
    }

    /**
     * Process Tranzila payment
     */
    private function process_tranzila( $order ) {
        $terminal = get_option( 'cl_tranzila_terminal', '' );
        $password = get_option( 'cl_tranzila_password', '' );

        if ( empty( $terminal ) ) {
            return new WP_Error( 'gateway_config', __( 'שער תשלום לא מוגדר', 'commerce-layer' ) );
        }

        // Build Tranzila iframe URL
        $params = array(
            'supplier'    => $terminal,
            'TranzilaPW'  => $password,
            'sum'         => $order->get_total(),
            'currency'    => '1', // 1 = ILS
            'cred_type'   => '1', // Regular transaction
            'tranmode'    => 'A', // Authorization + capture
            'pdesc'       => sprintf( __( 'הזמנה %s', 'commerce-layer' ), $order->get_order_number() ),
            'contact'     => $order->get_customer_name(),
            'email'       => $order->get_customer_email(),
            'phone'       => $order->get_customer_phone(),
            'order_id'    => $order->get_order_number(),
            'success_url' => add_query_arg( array(
                'cl_payment'   => 'success',
                'order_number' => $order->get_order_number(),
            ), home_url() ),
            'fail_url'    => add_query_arg( array(
                'cl_payment'   => 'cancel',
                'order_number' => $order->get_order_number(),
            ), home_url() ),
            'notify_url'  => home_url( '/cl-payment/ipn/' ),
        );

        $iframe_url = 'https://secure5.tranzila.com/' . $terminal . '/iframe.php?' . http_build_query( $params );

        return array(
            'success'      => true,
            'redirect'     => false,
            'iframe'       => true,
            'iframe_url'   => $iframe_url,
            'order_number' => $order->get_order_number(),
        );
    }

    /**
     * Process Cardcom payment
     */
    private function process_cardcom( $order ) {
        $terminal = get_option( 'cl_cardcom_terminal', '' );
        $username = get_option( 'cl_cardcom_username', '' );

        if ( empty( $terminal ) ) {
            return new WP_Error( 'gateway_config', __( 'שער תשלום לא מוגדר', 'commerce-layer' ) );
        }

        // Build Cardcom lowprofile request
        $params = array(
            'TerminalNumber'     => $terminal,
            'UserName'           => $username,
            'SumToBill'          => $order->get_total(),
            'CoinID'             => '1', // ILS
            'Language'           => 'he',
            'ProductName'        => sprintf( __( 'הזמנה %s', 'commerce-layer' ), $order->get_order_number() ),
            'SuccessRedirectUrl' => add_query_arg( array(
                'cl_payment'   => 'success',
                'order_number' => $order->get_order_number(),
            ), home_url() ),
            'ErrorRedirectUrl'   => add_query_arg( array(
                'cl_payment'   => 'cancel',
                'order_number' => $order->get_order_number(),
            ), home_url() ),
            'IndicatorUrl'       => home_url( '/cl-payment/ipn/' ),
            'InvoiceHead.CustName'       => $order->get_customer_name(),
            'InvoiceHead.SendByEmail'    => 'true',
            'InvoiceHead.CustMail'       => $order->get_customer_email(),
            'InvoiceHead.CustMobilePH'   => $order->get_customer_phone(),
        );

        // Create lowprofile request
        $response = wp_remote_post( 'https://secure.cardcom.solutions/Interface/LowProfile.aspx', array(
            'body' => $params,
        ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'gateway_error', __( 'שגיאה בחיבור לשער התשלום', 'commerce-layer' ) );
        }

        parse_str( wp_remote_retrieve_body( $response ), $result );

        if ( empty( $result['LowProfileCode'] ) ) {
            return new WP_Error( 'gateway_error', $result['Description'] ?? __( 'שגיאה בשער התשלום', 'commerce-layer' ) );
        }

        $iframe_url = 'https://secure.cardcom.solutions/external/LowProfileClearing/' . $result['LowProfileCode'];

        return array(
            'success'      => true,
            'redirect'     => false,
            'iframe'       => true,
            'iframe_url'   => $iframe_url,
            'order_number' => $order->get_order_number(),
        );
    }

    /**
     * Process PayPal payment
     */
    private function process_paypal( $order ) {
        $client_id = get_option( 'cl_paypal_client_id', '' );

        if ( empty( $client_id ) ) {
            return new WP_Error( 'gateway_config', __( 'PayPal לא מוגדר', 'commerce-layer' ) );
        }

        // PayPal requires client-side integration
        return array(
            'success'      => true,
            'redirect'     => false,
            'paypal'       => true,
            'client_id'    => $client_id,
            'amount'       => $order->get_total(),
            'currency'     => $order->get_currency(),
            'order_number' => $order->get_order_number(),
        );
    }

    /**
     * Process test payment (for development)
     */
    private function process_test_payment( $order ) {
        // Simulate successful payment
        $order->set_status( 'paid' );
        $order->set_payment_details( 'test', 'TEST-' . time() );

        $thank_you_url = add_query_arg(
            'order',
            $order->get_order_number(),
            get_permalink( get_option( 'cl_thank_you_page_id' ) )
        );

        return array(
            'success'      => true,
            'redirect'     => true,
            'redirect_url' => $thank_you_url,
            'order_number' => $order->get_order_number(),
        );
    }

    /**
     * Handle payment success
     */
    private function handle_payment_success() {
        $order_number = isset( $_GET['order_number'] ) ? sanitize_text_field( $_GET['order_number'] ) : '';

        if ( empty( $order_number ) ) {
            wp_redirect( home_url() );
            exit;
        }

        $order = CL_Order::get_by_order_number( $order_number );

        if ( ! $order ) {
            wp_redirect( home_url() );
            exit;
        }

        // Update order status if still pending
        if ( 'pending' === $order->get_status() ) {
            $order->set_status( 'paid' );
        }

        // Clear cart
        $cart = CL_Cart::get_instance();
        $cart->clear();

        // Redirect to thank you page
        $thank_you_url = add_query_arg(
            'order',
            $order->get_order_number(),
            get_permalink( get_option( 'cl_thank_you_page_id' ) )
        );

        wp_redirect( $thank_you_url );
        exit;
    }

    /**
     * Handle payment cancel
     */
    private function handle_payment_cancel() {
        $order_number = isset( $_GET['order_number'] ) ? sanitize_text_field( $_GET['order_number'] ) : '';

        // Redirect to checkout with error
        $checkout_url = add_query_arg(
            'payment_error',
            '1',
            get_permalink( get_option( 'cl_checkout_page_id' ) )
        );

        wp_redirect( $checkout_url );
        exit;
    }

    /**
     * Handle IPN/webhook
     */
    private function handle_ipn() {
        $gateway = get_option( 'cl_payment_gateway', 'tranzila' );

        switch ( $gateway ) {
            case 'tranzila':
                $this->handle_tranzila_ipn();
                break;
            case 'cardcom':
                $this->handle_cardcom_ipn();
                break;
        }

        echo 'OK';
        exit;
    }

    /**
     * Handle Tranzila IPN
     */
    private function handle_tranzila_ipn() {
        $response = $_POST;

        if ( empty( $response['order_id'] ) ) {
            return;
        }

        $order = CL_Order::get_by_order_number( $response['order_id'] );

        if ( ! $order ) {
            return;
        }

        // Check response code
        if ( isset( $response['Response'] ) && '000' === $response['Response'] ) {
            $order->set_status( 'paid' );
            $order->set_payment_details( 'tranzila', $response['ConfirmationCode'] ?? '' );

            do_action( 'cl_payment_complete', $order );
        }
    }

    /**
     * Handle Cardcom IPN
     */
    private function handle_cardcom_ipn() {
        $response = $_POST;

        if ( empty( $response['InternalDealNumber'] ) ) {
            return;
        }

        // Find order by deal number or custom field
        // Implementation depends on how you store the deal number

        // For now, we rely on success_url redirect
    }

    /**
     * Send order confirmation email
     */
    public static function send_order_confirmation( $order ) {
        $to = $order->get_customer_email();
        $subject = sprintf( __( 'אישור הזמנה %s', 'commerce-layer' ), $order->get_order_number() );

        $message = sprintf(
            __( "שלום %s,\n\nתודה על הזמנתך!\n\nמספר הזמנה: %s\nסכום: %s\n\nפרטי ההזמנה:\n", 'commerce-layer' ),
            $order->get_customer_name(),
            $order->get_order_number(),
            CL_Core::format_price( $order->get_total() )
        );

        foreach ( $order->get_items() as $item ) {
            $message .= sprintf(
                "- %s x %d = %s\n",
                $item['name'] . ( $item['variant_name'] ? ' (' . $item['variant_name'] . ')' : '' ),
                $item['quantity'],
                CL_Core::format_price( $item['total'] )
            );
        }

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
        );

        wp_mail( $to, $subject, $message, $headers );
    }
}

// Send email on payment complete
add_action( 'cl_payment_complete', array( 'CL_Checkout', 'send_order_confirmation' ) );
add_action( 'cl_order_status_changed', function( $order_id, $new_status, $old_status ) {
    if ( 'paid' === $new_status && 'pending' === $old_status ) {
        $order = new CL_Order( $order_id );
        CL_Checkout::send_order_confirmation( $order );
    }
}, 10, 3 );
