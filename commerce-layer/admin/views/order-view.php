<?php
/**
 * Single Order View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$data = $order->get_data();
?>
<div class="wrap cl-order-view">
    <h1>
        <?php
        printf(
            esc_html__( 'הזמנה %s', 'commerce-layer' ),
            esc_html( $order->get_order_number() )
        );
        ?>
        <span class="cl-status cl-status-<?php echo esc_attr( $order->get_status() ); ?>">
            <?php echo esc_html( $order->get_status_label() ); ?>
        </span>
    </h1>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orders' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'חזרה לרשימה', 'commerce-layer' ); ?>
    </a>

    <div class="cl-order-grid">
        <!-- Order Details -->
        <div class="cl-order-box">
            <h2><?php esc_html_e( 'פרטי הזמנה', 'commerce-layer' ); ?></h2>
            <table class="cl-order-details">
                <tr>
                    <th><?php esc_html_e( 'מספר הזמנה:', 'commerce-layer' ); ?></th>
                    <td><?php echo esc_html( $order->get_order_number() ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'תאריך:', 'commerce-layer' ); ?></th>
                    <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $order->get_created_date() ) ) ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'סטטוס:', 'commerce-layer' ); ?></th>
                    <td>
                        <select id="cl-order-status" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
                            <?php foreach ( CL_Order::$statuses as $status => $label ) : ?>
                                <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $order->get_status(), $status ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'אמצעי תשלום:', 'commerce-layer' ); ?></th>
                    <td><?php echo esc_html( $order->get_payment_method() ?: '—' ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'מזהה עסקה:', 'commerce-layer' ); ?></th>
                    <td><?php echo esc_html( $order->get_transaction_id() ?: '—' ); ?></td>
                </tr>
            </table>
        </div>

        <!-- Customer Details -->
        <div class="cl-order-box">
            <h2><?php esc_html_e( 'פרטי לקוח', 'commerce-layer' ); ?></h2>
            <table class="cl-order-details">
                <tr>
                    <th><?php esc_html_e( 'שם:', 'commerce-layer' ); ?></th>
                    <td><?php echo esc_html( $order->get_customer_name() ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'אימייל:', 'commerce-layer' ); ?></th>
                    <td>
                        <a href="mailto:<?php echo esc_attr( $order->get_customer_email() ); ?>">
                            <?php echo esc_html( $order->get_customer_email() ); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'טלפון:', 'commerce-layer' ); ?></th>
                    <td>
                        <a href="tel:<?php echo esc_attr( $order->get_customer_phone() ); ?>">
                            <?php echo esc_html( $order->get_customer_phone() ); ?>
                        </a>
                    </td>
                </tr>
                <?php
                $address = $order->get_billing_address();
                if ( ! empty( $address ) ) :
                ?>
                <tr>
                    <th><?php esc_html_e( 'כתובת:', 'commerce-layer' ); ?></th>
                    <td>
                        <?php
                        $address_parts = array_filter( array(
                            $address['address'] ?? '',
                            $address['city'] ?? '',
                            $address['postcode'] ?? '',
                        ) );
                        echo esc_html( implode( ', ', $address_parts ) );
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Order Items -->
    <div class="cl-order-box cl-order-items-box">
        <h2><?php esc_html_e( 'פריטים בהזמנה', 'commerce-layer' ); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-item"><?php esc_html_e( 'פריט', 'commerce-layer' ); ?></th>
                    <th class="column-price"><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                    <th class="column-quantity"><?php esc_html_e( 'כמות', 'commerce-layer' ); ?></th>
                    <th class="column-total"><?php esc_html_e( 'סה"כ', 'commerce-layer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $order->get_items() as $item ) : ?>
                    <tr>
                        <td class="column-item">
                            <strong>
                                <a href="<?php echo esc_url( get_edit_post_link( $item['post_id'] ) ); ?>">
                                    <?php echo esc_html( $item['name'] ); ?>
                                </a>
                            </strong>
                            <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                <br><small><?php echo esc_html( $item['variant_name'] ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="column-price"><?php echo CL_Core::format_price( $item['price'] ); ?></td>
                        <td class="column-quantity"><?php echo esc_html( $item['quantity'] ); ?></td>
                        <td class="column-total"><?php echo CL_Core::format_price( $item['total'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="cl-subtotal">
                    <td colspan="3" class="text-left"><?php esc_html_e( 'סיכום ביניים:', 'commerce-layer' ); ?></td>
                    <td><?php echo CL_Core::format_price( $order->get_subtotal() ); ?></td>
                </tr>
                <tr class="cl-total">
                    <td colspan="3" class="text-left"><strong><?php esc_html_e( 'סה"כ לתשלום:', 'commerce-layer' ); ?></strong></td>
                    <td><strong><?php echo CL_Core::format_price( $order->get_total() ); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ( $order->get_notes() ) : ?>
    <div class="cl-order-box">
        <h2><?php esc_html_e( 'הערות', 'commerce-layer' ); ?></h2>
        <p><?php echo esc_html( $order->get_notes() ); ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cl-order-status').on('change', function() {
        var status = $(this).val();
        var orderId = $(this).data('order-id');

        $.post(ajaxurl, {
            action: 'cl_update_order_status',
            nonce: clAdmin.nonce,
            order_id: orderId,
            status: status
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || clAdmin.strings.error);
            }
        });
    });
});
</script>
