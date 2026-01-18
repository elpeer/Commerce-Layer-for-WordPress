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
$current_user = wp_get_current_user();
?>
<div class="wrap cl-dashboard-wrap">
    <!-- Header -->
    <div class="cl-dashboard-header">
        <div class="cl-header-content">
            <h1><?php esc_html_e( 'Commerce Layer', 'commerce-layer' ); ?></h1>
            <p class="cl-welcome"><?php printf( esc_html__( 'שלום %s, הנה סקירת החנות שלך', 'commerce-layer' ), esc_html( $current_user->display_name ) ); ?></p>
        </div>
        <div class="cl-header-actions">
            <div class="cl-period-tabs">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=7' ) ); ?>" class="<?php echo 7 === $days ? 'active' : ''; ?>">7 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=30' ) ); ?>" class="<?php echo 30 === $days ? 'active' : ''; ?>">30 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=commerce-layer&days=90' ) ); ?>" class="<?php echo 90 === $days ? 'active' : ''; ?>">90 <?php esc_html_e( 'ימים', 'commerce-layer' ); ?></a>
            </div>
        </div>
    </div>

    <!-- Main KPI Cards -->
    <div class="cl-kpi-grid">
        <div class="cl-kpi-card cl-kpi-revenue">
            <div class="cl-kpi-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="cl-kpi-content">
                <span class="cl-kpi-value"><?php echo esc_html( $currency_symbol . number_format( $analytics['revenue'], 0 ) ); ?></span>
                <span class="cl-kpi-label"><?php esc_html_e( 'הכנסות', 'commerce-layer' ); ?></span>
            </div>
        </div>

        <div class="cl-kpi-card cl-kpi-orders">
            <div class="cl-kpi-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div class="cl-kpi-content">
                <span class="cl-kpi-value"><?php echo esc_html( $analytics['purchases'] ); ?></span>
                <span class="cl-kpi-label"><?php esc_html_e( 'הזמנות', 'commerce-layer' ); ?></span>
            </div>
        </div>

        <div class="cl-kpi-card cl-kpi-leads">
            <div class="cl-kpi-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div class="cl-kpi-content">
                <span class="cl-kpi-value"><?php echo esc_html( $analytics['leads'] ); ?></span>
                <span class="cl-kpi-label"><?php esc_html_e( 'לידים', 'commerce-layer' ); ?></span>
            </div>
        </div>

        <div class="cl-kpi-card cl-kpi-visitors">
            <div class="cl-kpi-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <div class="cl-kpi-content">
                <span class="cl-kpi-value"><?php echo esc_html( $analytics['unique_visitors'] ); ?></span>
                <span class="cl-kpi-label"><?php esc_html_e( 'מבקרים', 'commerce-layer' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="cl-dashboard-main">
        <!-- Left Column -->
        <div class="cl-dashboard-left">
            <!-- Funnel Card -->
            <div class="cl-card cl-funnel-card">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'משפך מכירות', 'commerce-layer' ); ?></h3>
                </div>
                <div class="cl-card-body">
                    <div class="cl-funnel">
                        <div class="cl-funnel-step">
                            <div class="cl-funnel-bar" style="width: 100%;">
                                <span class="cl-funnel-value"><?php echo esc_html( $analytics['product_views'] ); ?></span>
                            </div>
                            <span class="cl-funnel-label"><?php esc_html_e( 'צפיות במוצרים', 'commerce-layer' ); ?></span>
                        </div>
                        <div class="cl-funnel-step">
                            <?php $cart_percent = $analytics['product_views'] > 0 ? round( ( $analytics['add_to_cart'] / $analytics['product_views'] ) * 100 ) : 0; ?>
                            <div class="cl-funnel-bar" style="width: <?php echo max( 20, $cart_percent ); ?>%;">
                                <span class="cl-funnel-value"><?php echo esc_html( $analytics['add_to_cart'] ); ?></span>
                            </div>
                            <span class="cl-funnel-label"><?php esc_html_e( 'הוספות לסל', 'commerce-layer' ); ?></span>
                        </div>
                        <div class="cl-funnel-step">
                            <?php $checkout_percent = $analytics['product_views'] > 0 ? round( ( $analytics['checkouts_started'] / $analytics['product_views'] ) * 100 ) : 0; ?>
                            <div class="cl-funnel-bar" style="width: <?php echo max( 15, $checkout_percent ); ?>%;">
                                <span class="cl-funnel-value"><?php echo esc_html( $analytics['checkouts_started'] ); ?></span>
                            </div>
                            <span class="cl-funnel-label"><?php esc_html_e( 'התחלות צ׳קאאוט', 'commerce-layer' ); ?></span>
                        </div>
                        <div class="cl-funnel-step">
                            <?php $purchase_percent = $analytics['product_views'] > 0 ? round( ( $analytics['purchases'] / $analytics['product_views'] ) * 100 ) : 0; ?>
                            <div class="cl-funnel-bar cl-funnel-success" style="width: <?php echo max( 10, $purchase_percent ); ?>%;">
                                <span class="cl-funnel-value"><?php echo esc_html( $analytics['purchases'] ); ?></span>
                            </div>
                            <span class="cl-funnel-label"><?php esc_html_e( 'רכישות', 'commerce-layer' ); ?></span>
                        </div>
                    </div>
                    <div class="cl-funnel-metrics">
                        <div class="cl-metric">
                            <span class="cl-metric-value cl-metric-success"><?php echo esc_html( $analytics['conversion_rate'] ); ?>%</span>
                            <span class="cl-metric-label"><?php esc_html_e( 'המרה', 'commerce-layer' ); ?></span>
                        </div>
                        <div class="cl-metric">
                            <span class="cl-metric-value cl-metric-warning"><?php echo esc_html( $analytics['abandonment_rate'] ); ?>%</span>
                            <span class="cl-metric-label"><?php esc_html_e( 'נטישה', 'commerce-layer' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="cl-card">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'הזמנות אחרונות', 'commerce-layer' ); ?></h3>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orders' ) ); ?>" class="cl-card-link"><?php esc_html_e( 'הצג הכל', 'commerce-layer' ); ?></a>
                </div>
                <div class="cl-card-body cl-card-body-table">
                    <?php if ( empty( $recent_orders ) ) : ?>
                        <div class="cl-empty-state">
                            <p><?php esc_html_e( 'אין הזמנות עדיין', 'commerce-layer' ); ?></p>
                        </div>
                    <?php else : ?>
                        <table class="cl-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'הזמנה', 'commerce-layer' ); ?></th>
                                    <th><?php esc_html_e( 'לקוח', 'commerce-layer' ); ?></th>
                                    <th><?php esc_html_e( 'סכום', 'commerce-layer' ); ?></th>
                                    <th><?php esc_html_e( 'סטטוס', 'commerce-layer' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $recent_orders as $order ) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orders&order_id=' . $order->get_id() ) ); ?>" class="cl-order-link">
                                                #<?php echo esc_html( $order->get_order_number() ); ?>
                                            </a>
                                            <span class="cl-order-date"><?php echo esc_html( date_i18n( 'd/m H:i', strtotime( $order->get_created_date() ) ) ); ?></span>
                                        </td>
                                        <td><?php echo esc_html( $order->get_customer_name() ); ?></td>
                                        <td class="cl-order-total"><?php echo CL_Core::format_price( $order->get_total() ); ?></td>
                                        <td>
                                            <span class="cl-badge cl-badge-<?php echo esc_attr( $order->get_status() ); ?>">
                                                <?php echo esc_html( $order->get_status_label() ); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="cl-dashboard-right">
            <!-- Order Status Summary -->
            <div class="cl-card cl-status-card">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'סטטוס הזמנות', 'commerce-layer' ); ?></h3>
                </div>
                <div class="cl-card-body">
                    <div class="cl-status-list">
                        <div class="cl-status-item">
                            <div class="cl-status-dot cl-dot-pending"></div>
                            <span class="cl-status-name"><?php esc_html_e( 'ממתינות', 'commerce-layer' ); ?></span>
                            <span class="cl-status-count"><?php echo esc_html( $pending_orders ); ?></span>
                        </div>
                        <div class="cl-status-item">
                            <div class="cl-status-dot cl-dot-paid"></div>
                            <span class="cl-status-name"><?php esc_html_e( 'שולמו', 'commerce-layer' ); ?></span>
                            <span class="cl-status-count"><?php echo esc_html( $paid_orders ); ?></span>
                        </div>
                        <div class="cl-status-item">
                            <div class="cl-status-dot cl-dot-lead"></div>
                            <span class="cl-status-name"><?php esc_html_e( 'לידים', 'commerce-layer' ); ?></span>
                            <span class="cl-status-count"><?php echo esc_html( $lead_orders ); ?></span>
                        </div>
                        <div class="cl-status-item cl-status-total">
                            <span class="cl-status-name"><?php esc_html_e( 'סה״כ', 'commerce-layer' ); ?></span>
                            <span class="cl-status-count"><?php echo esc_html( $total_orders ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="cl-card">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'מוצרים מובילים', 'commerce-layer' ); ?></h3>
                </div>
                <div class="cl-card-body">
                    <?php if ( empty( $analytics['top_products_views'] ) ) : ?>
                        <div class="cl-empty-state">
                            <p><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="cl-product-list">
                            <?php foreach ( array_slice( $analytics['top_products_views'], 0, 5 ) as $index => $product ) : ?>
                                <div class="cl-product-item">
                                    <span class="cl-product-rank"><?php echo esc_html( $index + 1 ); ?></span>
                                    <div class="cl-product-info">
                                        <a href="<?php echo esc_url( get_edit_post_link( $product['post_id'] ) ); ?>" class="cl-product-name">
                                            <?php echo esc_html( $product['product_name'] ); ?>
                                        </a>
                                        <span class="cl-product-views"><?php echo esc_html( $product['views'] ); ?> <?php esc_html_e( 'צפיות', 'commerce-layer' ); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Device & Traffic -->
            <div class="cl-card">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'מכשירים', 'commerce-layer' ); ?></h3>
                </div>
                <div class="cl-card-body">
                    <?php if ( empty( $analytics['device_breakdown'] ) ) : ?>
                        <div class="cl-empty-state">
                            <p><?php esc_html_e( 'אין נתונים עדיין', 'commerce-layer' ); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="cl-device-list">
                            <?php
                            $total_devices = array_sum( array_column( $analytics['device_breakdown'], 'count' ) );
                            $device_icons = array(
                                'desktop' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                                'mobile'  => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                                'tablet'  => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                            );
                            $device_labels = array(
                                'desktop' => __( 'מחשב', 'commerce-layer' ),
                                'mobile'  => __( 'נייד', 'commerce-layer' ),
                                'tablet'  => __( 'טאבלט', 'commerce-layer' ),
                            );
                            foreach ( $analytics['device_breakdown'] as $device ) :
                                $percent = $total_devices > 0 ? round( ( $device['count'] / $total_devices ) * 100 ) : 0;
                                $icon = isset( $device_icons[ $device['device_type'] ] ) ? $device_icons[ $device['device_type'] ] : '';
                                $label = isset( $device_labels[ $device['device_type'] ] ) ? $device_labels[ $device['device_type'] ] : $device['device_type'];
                            ?>
                                <div class="cl-device-row">
                                    <div class="cl-device-info">
                                        <span class="cl-device-icon"><?php echo $icon; ?></span>
                                        <span class="cl-device-name"><?php echo esc_html( $label ); ?></span>
                                    </div>
                                    <div class="cl-device-stats">
                                        <div class="cl-device-bar-wrap">
                                            <div class="cl-device-bar" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
                                        </div>
                                        <span class="cl-device-percent"><?php echo esc_html( $percent ); ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="cl-card cl-quick-actions">
                <div class="cl-card-header">
                    <h3><?php esc_html_e( 'פעולות מהירות', 'commerce-layer' ); ?></h3>
                </div>
                <div class="cl-card-body">
                    <div class="cl-action-buttons">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-products' ) ); ?>" class="cl-action-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <?php esc_html_e( 'מוצרים', 'commerce-layer' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-settings' ) ); ?>" class="cl-action-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <?php esc_html_e( 'הגדרות', 'commerce-layer' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-coupons' ) ); ?>" class="cl-action-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <?php esc_html_e( 'קופונים', 'commerce-layer' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Layout */
.cl-dashboard-wrap {
    max-width: 1400px;
    margin: 20px auto 0;
    padding: 0 20px;
}

/* Header */
.cl-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 25px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    color: #fff;
}

.cl-dashboard-header h1 {
    margin: 0 0 5px;
    font-size: 28px;
    font-weight: 700;
    color: #fff;
}

.cl-welcome {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

.cl-period-tabs {
    display: flex;
    gap: 5px;
    background: rgba(255,255,255,0.2);
    padding: 5px;
    border-radius: 8px;
}

.cl-period-tabs a {
    padding: 8px 16px;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    transition: all 0.2s;
}

.cl-period-tabs a:hover {
    background: rgba(255,255,255,0.2);
}

.cl-period-tabs a.active {
    background: #fff;
    color: #667eea;
    font-weight: 600;
}

/* KPI Cards */
.cl-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.cl-kpi-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.cl-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.cl-kpi-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cl-kpi-revenue .cl-kpi-icon { background: #ecfdf5; color: #059669; }
.cl-kpi-orders .cl-kpi-icon { background: #eff6ff; color: #2563eb; }
.cl-kpi-leads .cl-kpi-icon { background: #fef3c7; color: #d97706; }
.cl-kpi-visitors .cl-kpi-icon { background: #f3e8ff; color: #9333ea; }

.cl-kpi-content {
    display: flex;
    flex-direction: column;
}

.cl-kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.cl-kpi-label {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}

/* Main Grid */
.cl-dashboard-main {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
}

.cl-dashboard-left {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.cl-dashboard-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Cards */
.cl-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}

.cl-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.cl-card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.cl-card-link {
    font-size: 13px;
    color: #667eea;
    text-decoration: none;
}

.cl-card-link:hover {
    text-decoration: underline;
}

.cl-card-body {
    padding: 20px 24px;
}

.cl-card-body-table {
    padding: 0;
}

/* Funnel */
.cl-funnel {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cl-funnel-step {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cl-funnel-bar {
    height: 36px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 12px;
    min-width: 60px;
    transition: width 0.5s ease;
}

.cl-funnel-bar.cl-funnel-success {
    background: linear-gradient(90deg, #059669, #10b981);
}

.cl-funnel-value {
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

.cl-funnel-label {
    font-size: 13px;
    color: #64748b;
    min-width: 120px;
}

.cl-funnel-metrics {
    display: flex;
    gap: 30px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}

.cl-metric {
    display: flex;
    flex-direction: column;
}

.cl-metric-value {
    font-size: 24px;
    font-weight: 700;
}

.cl-metric-value.cl-metric-success { color: #059669; }
.cl-metric-value.cl-metric-warning { color: #dc2626; }

.cl-metric-label {
    font-size: 13px;
    color: #64748b;
}

/* Table */
.cl-table {
    width: 100%;
    border-collapse: collapse;
}

.cl-table th {
    text-align: right;
    padding: 12px 24px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.cl-table td {
    padding: 14px 24px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}

.cl-table tr:last-child td {
    border-bottom: none;
}

.cl-order-link {
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
}

.cl-order-link:hover {
    color: #667eea;
}

.cl-order-date {
    display: block;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

.cl-order-total {
    font-weight: 600;
    color: #059669;
}

/* Badges */
.cl-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.cl-badge-pending { background: #fef3c7; color: #92400e; }
.cl-badge-paid { background: #d1fae5; color: #065f46; }
.cl-badge-cancelled { background: #fee2e2; color: #991b1b; }
.cl-badge-lead { background: #dbeafe; color: #1e40af; }

/* Status Card */
.cl-status-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.cl-status-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cl-status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.cl-dot-pending { background: #f59e0b; }
.cl-dot-paid { background: #10b981; }
.cl-dot-lead { background: #3b82f6; }

.cl-status-name {
    flex: 1;
    font-size: 14px;
    color: #475569;
}

.cl-status-count {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.cl-status-total {
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}

.cl-status-total .cl-status-name {
    font-weight: 600;
    color: #1e293b;
}

/* Product List */
.cl-product-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cl-product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.cl-product-item:last-child {
    border-bottom: none;
}

.cl-product-rank {
    width: 28px;
    height: 28px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
}

.cl-product-info {
    flex: 1;
    min-width: 0;
}

.cl-product-info .cl-product-name {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cl-product-info .cl-product-name:hover {
    color: #667eea;
}

.cl-product-views {
    font-size: 12px;
    color: #94a3b8;
}

/* Device List */
.cl-device-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.cl-device-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cl-device-info {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 80px;
}

.cl-device-icon {
    color: #64748b;
}

.cl-device-name {
    font-size: 13px;
    color: #475569;
}

.cl-device-stats {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cl-device-bar-wrap {
    flex: 1;
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.cl-device-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 4px;
}

.cl-device-percent {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    min-width: 40px;
    text-align: left;
}

/* Quick Actions */
.cl-action-buttons {
    display: flex;
    gap: 10px;
}

.cl-action-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    background: #f8fafc;
    border-radius: 10px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
}

.cl-action-btn:hover {
    background: #667eea;
    color: #fff;
}

/* Empty State */
.cl-empty-state {
    padding: 30px;
    text-align: center;
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 1200px) {
    .cl-dashboard-main {
        grid-template-columns: 1fr;
    }

    .cl-dashboard-right {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 900px) {
    .cl-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .cl-dashboard-right {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .cl-dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .cl-kpi-grid {
        grid-template-columns: 1fr;
    }
}
</style>
