<?php
/**
 * Thank You Template
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $order should be available from calling context
if ( ! isset( $order ) || ! $order instanceof CL_Order ) {
    return;
}

// Prepare tracking data
$tracking_items = array();
foreach ( $order->get_items() as $item ) {
    $tracking_items[] = array(
        'item_id'      => $item['post_id'],
        'item_name'    => $item['name'],
        'item_variant' => $item['variant_name'],
        'price'        => floatval( $item['price'] ),
        'quantity'     => intval( $item['quantity'] ),
    );
}

$is_lead = 'lead' === $order->get_status();
?>
<?php // Tracking: purchase or lead_submitted event ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.clTracking) {
        var orderData = {
            order_number: '<?php echo esc_js( $order->get_order_number() ); ?>',
            currency: '<?php echo esc_js( get_option( 'cl_currency', 'ILS' ) ); ?>',
            total: <?php echo floatval( $order->get_total() ); ?>,
            subtotal: <?php echo floatval( $order->get_subtotal() ); ?>,
            shipping: 0,
            items: <?php echo json_encode( $tracking_items ); ?>
        };

        <?php if ( $is_lead ) : ?>
        clTracking.leadSubmitted(orderData);
        <?php else : ?>
        clTracking.purchase(orderData);
        <?php endif; ?>
    }
});
</script>
<div class="cl-thank-you">
    <div class="cl-success-header">
        <span class="cl-success-icon">✓</span>
        <h2><?php esc_html_e( 'תודה על הזמנתך!', 'commerce-layer' ); ?></h2>
        <p><?php esc_html_e( 'ההזמנה שלך התקבלה בהצלחה', 'commerce-layer' ); ?></p>
    </div>

    <div class="cl-order-details">
        <div class="cl-detail-row">
            <span class="cl-label"><?php esc_html_e( 'מספר הזמנה:', 'commerce-layer' ); ?></span>
            <span class="cl-value"><?php echo esc_html( $order->get_order_number() ); ?></span>
        </div>

        <div class="cl-detail-row">
            <span class="cl-label"><?php esc_html_e( 'תאריך:', 'commerce-layer' ); ?></span>
            <span class="cl-value"><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $order->get_created_date() ) ) ); ?></span>
        </div>

        <div class="cl-detail-row">
            <span class="cl-label"><?php esc_html_e( 'סטטוס:', 'commerce-layer' ); ?></span>
            <span class="cl-value cl-status cl-status-<?php echo esc_attr( $order->get_status() ); ?>">
                <?php echo esc_html( $order->get_status_label() ); ?>
            </span>
        </div>

        <div class="cl-detail-row">
            <span class="cl-label"><?php esc_html_e( 'סכום:', 'commerce-layer' ); ?></span>
            <span class="cl-value cl-total"><?php echo CL_Core::format_price( $order->get_total() ); ?></span>
        </div>
    </div>

    <div class="cl-order-items">
        <h3><?php esc_html_e( 'פריטים בהזמנה', 'commerce-layer' ); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'פריט', 'commerce-layer' ); ?></th>
                    <th><?php esc_html_e( 'כמות', 'commerce-layer' ); ?></th>
                    <th><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $order->get_items() as $item ) : ?>
                    <tr>
                        <td>
                            <?php echo esc_html( $item['name'] ); ?>
                            <?php if ( ! empty( $item['variant_name'] ) ) : ?>
                                <br><small><?php echo esc_html( $item['variant_name'] ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $item['quantity'] ); ?></td>
                        <td><?php echo CL_Core::format_price( $item['total'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong><?php esc_html_e( 'סה"כ:', 'commerce-layer' ); ?></strong></td>
                    <td><strong><?php echo CL_Core::format_price( $order->get_total() ); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="cl-customer-details">
        <h3><?php esc_html_e( 'פרטי לקוח', 'commerce-layer' ); ?></h3>
        <p>
            <strong><?php echo esc_html( $order->get_customer_name() ); ?></strong><br>
            <?php echo esc_html( $order->get_customer_email() ); ?><br>
            <?php echo esc_html( $order->get_customer_phone() ); ?>
        </p>
        <?php
        $address = $order->get_billing_address();
        if ( ! empty( $address ) && array_filter( $address ) ) :
        ?>
            <p>
                <?php echo esc_html( $address['address'] ?? '' ); ?><br>
                <?php echo esc_html( $address['city'] ?? '' ); ?>
                <?php echo esc_html( $address['postcode'] ?? '' ); ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="cl-thank-you-actions">
        <p><?php esc_html_e( 'אישור נשלח לכתובת המייל שלך', 'commerce-layer' ); ?></p>
        <a href="<?php echo esc_url( home_url() ); ?>" class="cl-btn">
            <?php esc_html_e( 'חזרה לאתר', 'commerce-layer' ); ?>
        </a>
    </div>
</div>
