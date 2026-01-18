<?php
/**
 * Dashboard View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$currency_symbol = get_option( 'cl_currency_symbol', '₪' );
?>
<div class="wrap cl-dashboard">
    <h1><?php esc_html_e( 'Commerce Layer', 'commerce-layer' ); ?></h1>

    <!-- Period Selector -->
    <div class="cl-period-selector">
        <span><?php esc_html_e( 'תקופה:', 'commerce-layer' ); ?></span>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=7' ) ); ?>" class="<?php echo 7 === $days ? 'active' : ''; ?>">7 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=30' ) ); ?>" class="<?php echo 30 === $days ? 'active' : ''; ?>">30 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=90' ) ); ?>" class="<?php echo 90 === $days ? 'active' : ''; ?>">90 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
    </div>

    <div class="cl-dashboard-widgets">
        <!-- Main Stats Cards -->
        <div class="cl-stats-row cl-stats-main">
            <div class="cl-stat-card cl-stat-primary">
                <div class="cl-stat-icon">💰</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $currency_symbol . number_format( $analytics['revenue'], 2 ) ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'הכנסות', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-success">
                <div class="cl-stat-icon">🛒</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['purchases'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'רכישות', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-info">
                <div class="cl-stat-icon">📋</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['leads'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'לידים', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card">
                <div class="cl-stat-icon">👥</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['unique_visitors'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'מבקרים ייחודיים', 'commerce-layer' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Funnel Stats -->
        <div class="cl-stats-row">
            <div class="cl-stat-card cl-stat-small">
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['product_views'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'צפיות במוצרים', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-small">
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['add_to_cart'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'הוספות לסל', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-small">
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['checkouts_started'] ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'התחלות צ\'קאאוט', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-small cl-stat-success">
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['conversion_rate'] ); ?>%</span>
                    <span class="cl-stat-label"><?php esc_html_e( 'אחוז המרה', 'commerce-layer' ); ?></span>
                </div>
            </div>

            <div class="cl-stat-card cl-stat-small cl-stat-warning">
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $analytics['abandonment_rate'] ); ?>%</span>
                    <span class="cl-stat-label"><?php esc_html_e( 'נטישת סל', 'commerce-layer' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Orders Stats -->
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

            <div class="cl-stat-card cl-stat-info">
                <div class="cl-stat-icon">📝</div>
                <div class="cl-stat-content">
                    <span class="cl-stat-number"><?php echo esc_html( $lead_orders ); ?></span>
                    <span class="cl-stat-label"><?php esc_html_e( 'לידים בהמתנה', 'commerce-layer' ); ?></span>
                </div>
            </div>
        </div>

        <div class="cl-dashboard-grid">
            <!-- Top Products by Views -->
            <div class="cl-dashboard-section">
                <h2><?php esc_html_e( 'מוצרים פופולריים (צפיות)', 'commerce-layer' ); ?></h2>
                <?php if ( empty( $analytics['top_products_views'] ) ) : ?>
                    <p class="cl-no-data"><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'מוצר', 'commerce-layer' ); ?></th>
                                <th style="width:80px;"><?php esc_html_e( 'צפיות', 'commerce-layer' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $analytics['top_products_views'] as $product ) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url( get_edit_post_link( $product['post_id'] ) ); ?>">
                                            <?php echo esc_html( $product['product_name'] ); ?>
                                        </a>
                                    </td>
                                    <td><strong><?php echo esc_html( $product['views'] ); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Top Products by Cart Adds -->
            <div class="cl-dashboard-section">
                <h2><?php esc_html_e( 'מוצרים פופולריים (הוספות לסל)', 'commerce-layer' ); ?></h2>
                <?php if ( empty( $analytics['top_products_cart'] ) ) : ?>
                    <p class="cl-no-data"><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'מוצר', 'commerce-layer' ); ?></th>
                                <th style="width:80px;"><?php esc_html_e( 'הוספות', 'commerce-layer' ); ?></th>
                                <th style="width:100px;"><?php esc_html_e( 'שווי', 'commerce-layer' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $analytics['top_products_cart'] as $product ) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url( get_edit_post_link( $product['post_id'] ) ); ?>">
                                            <?php echo esc_html( $product['product_name'] ); ?>
                                        </a>
                                    </td>
                                    <td><strong><?php echo esc_html( $product['cart_adds'] ); ?></strong></td>
                                    <td><?php echo esc_html( $currency_symbol . number_format( $product['total_value'], 2 ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Device Breakdown -->
            <div class="cl-dashboard-section">
                <h2><?php esc_html_e( 'מכשירים', 'commerce-layer' ); ?></h2>
                <?php if ( empty( $analytics['device_breakdown'] ) ) : ?>
                    <p class="cl-no-data"><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                <?php else : ?>
                    <div class="cl-device-breakdown">
                        <?php
                        $total_devices = array_sum( array_column( $analytics['device_breakdown'], 'count' ) );
                        $device_labels = array(
                            'desktop' => '💻 ' . __( 'מחשב', 'commerce-layer' ),
                            'mobile'  => '📱 ' . __( 'מובייל', 'commerce-layer' ),
                            'tablet'  => '📟 ' . __( 'טאבלט', 'commerce-layer' ),
                        );
                        foreach ( $analytics['device_breakdown'] as $device ) :
                            $percent = $total_devices > 0 ? round( ( $device['count'] / $total_devices ) * 100 ) : 0;
                            $label = isset( $device_labels[ $device['device_type'] ] ) ? $device_labels[ $device['device_type'] ] : $device['device_type'];
                        ?>
                            <div class="cl-device-item">
                                <span class="cl-device-label"><?php echo esc_html( $label ); ?></span>
                                <div class="cl-device-bar">
                                    <div class="cl-device-fill" style="width: <?php echo esc_attr( $percent ); ?>%"></div>
                                </div>
                                <span class="cl-device-percent"><?php echo esc_html( $percent ); ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Top Referrers -->
            <div class="cl-dashboard-section">
                <h2><?php esc_html_e( 'מקורות תנועה', 'commerce-layer' ); ?></h2>
                <?php if ( empty( $analytics['top_referrers'] ) ) : ?>
                    <p class="cl-no-data"><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'מקור', 'commerce-layer' ); ?></th>
                                <th style="width:80px;"><?php esc_html_e( 'ביקורים', 'commerce-layer' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $analytics['top_referrers'] as $referrer ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $referrer['source'] ); ?></td>
                                    <td><strong><?php echo esc_html( $referrer['count'] ); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="cl-dashboard-section cl-dashboard-full">
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

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-coupons' ) ); ?>" class="cl-quick-link">
                    <span class="dashicons dashicons-tickets-alt"></span>
                    <?php esc_html_e( 'קופונים', 'commerce-layer' ); ?>
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

<style>
.cl-period-selector {
    margin-bottom: 20px;
}
.cl-period-selector a {
    display: inline-block;
    padding: 5px 15px;
    margin-left: 5px;
    background: #f0f0f1;
    color: #1d2327;
    text-decoration: none;
    border-radius: 4px;
}
.cl-period-selector a.active,
.cl-period-selector a:hover {
    background: #2271b1;
    color: #fff;
}
.cl-stats-main .cl-stat-card {
    flex: 1;
    min-width: 200px;
}
.cl-stat-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #fff !important;
}
.cl-stat-primary .cl-stat-number,
.cl-stat-primary .cl-stat-label {
    color: #fff !important;
}
.cl-stat-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    color: #fff !important;
}
.cl-stat-info .cl-stat-number,
.cl-stat-info .cl-stat-label {
    color: #fff !important;
}
.cl-stat-small {
    padding: 15px !important;
}
.cl-stat-small .cl-stat-number {
    font-size: 24px !important;
}
.cl-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 20px;
}
.cl-dashboard-full {
    grid-column: 1 / -1;
}
.cl-device-breakdown {
    padding: 10px 0;
}
.cl-device-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}
.cl-device-label {
    width: 100px;
    font-weight: 500;
}
.cl-device-bar {
    flex: 1;
    height: 20px;
    background: #e5e7eb;
    border-radius: 10px;
    margin: 0 10px;
    overflow: hidden;
}
.cl-device-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    border-radius: 10px;
    transition: width 0.3s ease;
}
.cl-device-percent {
    width: 50px;
    text-align: left;
    font-weight: bold;
}
@media (max-width: 1200px) {
    .cl-dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>
