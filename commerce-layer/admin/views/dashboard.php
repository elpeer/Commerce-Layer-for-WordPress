<?php
/**
 * Dashboard View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap cl-dashboard">
    <h1><?php esc_html_e( 'Commerce Layer', 'commerce-layer' ); ?></h1>

    <div class="cl-dashboard-widgets">
        <!-- Stats Cards -->
        <div class="cl-stats-row">
            <div class="cl-stat-card">
                <div class="cl-stat-icon">📦</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $total_orders ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'סה"כ הזמנות', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-warning">
                <div class="cl-stat-icon">⏳</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $pending_orders ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'ממתינות לתשלום', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-success">
                <div class="cl-stat-icon">✓</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $paid_orders ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'הזמנות ששולמו', 'commerce-layer' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="cl-dashboard-section">
            <h2><?php esc_html_e( 'הזמנות אחרונות', 'commerce-layer' ); ?></h2>

            <?php if ( empty( $recent_orders ) ) : ?>
                <p class="cl-no-data"><?php esc_html_e( 'אין הזמנות עדיין', 'commerce-layer' ); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'מספר הזמנה', 'commerce-layer' ); ?></th>
                            <th><?php esc_html_e( 'לקוח', 'commerce-layer' ); ?></th>
                            <th><?php esc_html_e( 'סכום', 'commerce-layer' ); ?></th>
                            <th><?php esc_html_e( 'סטטוס', 'commerce-layer' ); ?></th>
                            <th><?php esc_html_e( 'תאריך', 'commerce-layer' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_orders as $order ) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orders&order_id=' . $order->get_id() ) ); ?>">
                                        <?php echo esc_html( $order->get_order_number() ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $order->get_customer_name() ); ?></td>
                                <td><?php echo CL_Core::format_price( $order->get_total() ); ?></td>
                                <td>
                                    <span class="cl-status cl-status-<?php echo esc_attr( $order->get_status() ); ?>">
                                        <?php echo esc_html( $order->get_status_label() ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $order->get_created_date() ) ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orders' ) ); ?>" class="button">
                        <?php esc_html_e( 'צפה בכל ההזמנות', 'commerce-layer' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="cl-dashboard-section">
            <h2><?php esc_html_e( 'קישורים מהירים', 'commerce-layer' ); ?></h2>
            <div class="cl-quick-links">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-settings' ) ); ?>" class="cl-quick-link">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'הגדרות', 'commerce-layer' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-attributes' ) ); ?>" class="cl-quick-link">
                    <span class="dashicons dashicons-tag"></span>
                    <?php esc_html_e( 'מאפיינים', 'commerce-layer' ); ?>
                </a>

                <a href="<?php echo esc_url( get_permalink( get_option( 'cl_cart_page_id' ) ) ); ?>" class="cl-quick-link" target="_blank">
                    <span class="dashicons dashicons-cart"></span>
                    <?php esc_html_e( 'עמוד סל', 'commerce-layer' ); ?>
                </a>

                <a href="<?php echo esc_url( get_permalink( get_option( 'cl_checkout_page_id' ) ) ); ?>" class="cl-quick-link" target="_blank">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e( 'עמוד תשלום', 'commerce-layer' ); ?>
                </a>
            </div>
        </div>

        <!-- Help Section -->
        <div class="cl-dashboard-section cl-help-section">
            <h2><?php esc_html_e( 'צריך עזרה?', 'commerce-layer' ); ?></h2>
            <div class="cl-help-content">
                <p><?php esc_html_e( 'Shortcodes זמינים:', 'commerce-layer' ); ?></p>
                <ul>
                    <li><code>[commerce_box]</code> - <?php esc_html_e( 'רכיב מכירה מלא', 'commerce-layer' ); ?></li>
                    <li><code>[commerce_price]</code> - <?php esc_html_e( 'הצגת מחיר בלבד', 'commerce-layer' ); ?></li>
                    <li><code>[commerce_add_to_cart]</code> - <?php esc_html_e( 'כפתור הוסף לסל', 'commerce-layer' ); ?></li>
                    <li><code>[commerce_buy_now]</code> - <?php esc_html_e( 'כפתור קנה עכשיו', 'commerce-layer' ); ?></li>
                    <li><code>[cl_mini_cart]</code> - <?php esc_html_e( 'מיני סל', 'commerce-layer' ); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
