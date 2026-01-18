<?php
/**
 * Orders List View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'הזמנות', 'commerce-layer' ); ?></h1>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="cl-orders">
        <?php
        $orders_list->search_box( __( 'חפש הזמנות', 'commerce-layer' ), 'order_search' );
        $orders_list->display();
        ?>
    </form>
</div>
