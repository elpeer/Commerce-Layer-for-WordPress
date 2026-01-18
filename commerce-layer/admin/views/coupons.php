<?php
/**
 * Coupons View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle coupon actions
if ( isset( $_POST['cl_coupon_action'] ) && check_admin_referer( 'cl_coupons_nonce', 'cl_coupons_nonce' ) ) {
    $action = sanitize_text_field( $_POST['cl_coupon_action'] );

    if ( $action === 'add' || $action === 'edit' ) {
        $coupon_id = isset( $_POST['cl_coupon_id'] ) ? absint( $_POST['cl_coupon_id'] ) : 0;
        $coupon = new CL_Coupon( $coupon_id );

        $coupon->save( array(
            'code'             => $_POST['cl_coupon_code'] ?? '',
            'description'      => $_POST['cl_coupon_description'] ?? '',
            'discount_type'    => $_POST['cl_coupon_discount_type'] ?? 'percent',
            'discount_value'   => $_POST['cl_coupon_discount_value'] ?? 0,
            'min_order_amount' => $_POST['cl_coupon_min_order'] ?? '',
            'max_discount'     => $_POST['cl_coupon_max_discount'] ?? '',
            'usage_limit'      => $_POST['cl_coupon_usage_limit'] ?? '',
            'start_date'       => $_POST['cl_coupon_start_date'] ?? '',
            'end_date'         => $_POST['cl_coupon_end_date'] ?? '',
            'status'           => $_POST['cl_coupon_status'] ?? 'active',
        ) );

        echo '<div class="notice notice-success"><p>' . esc_html__( 'הקופון נשמר בהצלחה', 'commerce-layer' ) . '</p></div>';

        // Redirect to list after save
        if ( ! isset( $_GET['edit_coupon'] ) ) {
            echo '<script>window.location.href = "' . esc_url( admin_url( 'admin.php?page=cl-coupons' ) ) . '";</script>';
        }
    } elseif ( $action === 'delete' ) {
        $coupon_id = isset( $_POST['cl_coupon_id'] ) ? absint( $_POST['cl_coupon_id'] ) : 0;
        if ( $coupon_id ) {
            $coupon = new CL_Coupon( $coupon_id );
            $coupon->delete();
            echo '<div class="notice notice-success"><p>' . esc_html__( 'הקופון נמחק בהצלחה', 'commerce-layer' ) . '</p></div>';
        }
    }
}

$coupons = CL_Coupon::get_all();
$editing_coupon = null;
if ( isset( $_GET['edit_coupon'] ) ) {
    $editing_coupon = new CL_Coupon( absint( $_GET['edit_coupon'] ) );
    if ( ! $editing_coupon->exists() ) {
        $editing_coupon = null;
    }
}
$is_adding = isset( $_GET['add_coupon'] );
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'קופונים', 'commerce-layer' ); ?></h1>
    <?php if ( ! $is_adding && ! $editing_coupon ) : ?>
        <a href="<?php echo esc_url( add_query_arg( 'add_coupon', '1' ) ); ?>" class="page-title-action"><?php esc_html_e( 'הוסף קופון חדש', 'commerce-layer' ); ?></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <?php if ( $is_adding || $editing_coupon ) : ?>
        <!-- Add/Edit Coupon Form -->
        <form method="post" action="">
            <?php wp_nonce_field( 'cl_coupons_nonce', 'cl_coupons_nonce' ); ?>
            <input type="hidden" name="cl_coupon_action" value="<?php echo $editing_coupon ? 'edit' : 'add'; ?>">
            <?php if ( $editing_coupon ) : ?>
                <input type="hidden" name="cl_coupon_id" value="<?php echo esc_attr( $editing_coupon->get_id() ); ?>">
            <?php endif; ?>

            <h2><?php echo $editing_coupon ? __( 'עריכת קופון', 'commerce-layer' ) : __( 'הוספת קופון חדש', 'commerce-layer' ); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'קוד קופון', 'commerce-layer' ); ?> <span style="color:red;">*</span></th>
                    <td>
                        <input type="text" name="cl_coupon_code" value="<?php echo $editing_coupon ? esc_attr( $editing_coupon->get_code() ) : ''; ?>" class="regular-text" required style="text-transform: uppercase;">
                        <p class="description"><?php esc_html_e( 'הקוד שהלקוחות יזינו לקבלת ההנחה', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'תיאור', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="text" name="cl_coupon_description" value="<?php echo $editing_coupon ? esc_attr( $editing_coupon->get_description() ) : ''; ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'תיאור פנימי (לא מוצג ללקוחות)', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'סוג הנחה', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_coupon_discount_type" id="cl-coupon-discount-type">
                            <option value="percent" <?php selected( $editing_coupon ? $editing_coupon->get_discount_type() : '', 'percent' ); ?>><?php esc_html_e( 'אחוז הנחה (%)', 'commerce-layer' ); ?></option>
                            <option value="fixed" <?php selected( $editing_coupon ? $editing_coupon->get_discount_type() : '', 'fixed' ); ?>><?php esc_html_e( 'סכום קבוע', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'ערך הנחה', 'commerce-layer' ); ?> <span style="color:red;">*</span></th>
                    <td>
                        <input type="number" name="cl_coupon_discount_value" value="<?php echo $editing_coupon ? esc_attr( $editing_coupon->get_discount_value() ) : ''; ?>" class="small-text" min="0" step="0.01" required>
                        <span id="cl-discount-suffix">%</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'סכום הזמנה מינימלי', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_coupon_min_order" value="<?php echo $editing_coupon && $editing_coupon->get_min_order_amount() ? esc_attr( $editing_coupon->get_min_order_amount() ) : ''; ?>" class="small-text" min="0" step="0.01"> <?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?>
                        <p class="description"><?php esc_html_e( 'השאר ריק לביטול הגבלה', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'הנחה מקסימלית', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_coupon_max_discount" value="<?php echo $editing_coupon && $editing_coupon->get_max_discount() ? esc_attr( $editing_coupon->get_max_discount() ) : ''; ?>" class="small-text" min="0" step="0.01"> <?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?>
                        <p class="description"><?php esc_html_e( 'רלוונטי להנחת אחוזים - הגבלת סכום ההנחה', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'מגבלת שימושים', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_coupon_usage_limit" value="<?php echo $editing_coupon && $editing_coupon->get_usage_limit() ? esc_attr( $editing_coupon->get_usage_limit() ) : ''; ?>" class="small-text" min="0">
                        <?php if ( $editing_coupon ) : ?>
                            <span style="color: #666;">(<?php printf( __( 'נוצל %d פעמים', 'commerce-layer' ), $editing_coupon->get_usage_count() ); ?>)</span>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'כמה פעמים ניתן להשתמש בקופון. השאר ריק ללא הגבלה', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'תאריך התחלה', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="datetime-local" name="cl_coupon_start_date" value="<?php echo $editing_coupon && $editing_coupon->get_start_date() ? esc_attr( date( 'Y-m-d\TH:i', strtotime( $editing_coupon->get_start_date() ) ) ) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'תאריך סיום', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="datetime-local" name="cl_coupon_end_date" value="<?php echo $editing_coupon && $editing_coupon->get_end_date() ? esc_attr( date( 'Y-m-d\TH:i', strtotime( $editing_coupon->get_end_date() ) ) ) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'סטטוס', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_coupon_status">
                            <option value="active" <?php selected( $editing_coupon ? $editing_coupon->get_status() : '', 'active' ); ?>><?php esc_html_e( 'פעיל', 'commerce-layer' ); ?></option>
                            <option value="inactive" <?php selected( $editing_coupon ? $editing_coupon->get_status() : '', 'inactive' ); ?>><?php esc_html_e( 'לא פעיל', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="<?php echo $editing_coupon ? esc_attr__( 'עדכן קופון', 'commerce-layer' ) : esc_attr__( 'הוסף קופון', 'commerce-layer' ); ?>">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-coupons' ) ); ?>" class="button"><?php esc_html_e( 'ביטול', 'commerce-layer' ); ?></a>
            </p>
        </form>

        <script>
        jQuery(document).ready(function($) {
            function updateDiscountSuffix() {
                var type = $('#cl-coupon-discount-type').val();
                $('#cl-discount-suffix').text(type === 'percent' ? '%' : '<?php echo esc_js( get_option( 'cl_currency_symbol', '₪' ) ); ?>');
            }
            updateDiscountSuffix();
            $('#cl-coupon-discount-type').on('change', updateDiscountSuffix);
        });
        </script>

    <?php else : ?>
        <!-- Coupons List -->
        <?php if ( empty( $coupons ) ) : ?>
            <div class="cl-empty-state" style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ccd0d4; margin-top: 20px;">
                <span class="dashicons dashicons-tickets-alt" style="font-size: 48px; color: #c3c4c7; margin-bottom: 20px;"></span>
                <h2 style="margin: 0 0 10px;"><?php esc_html_e( 'עדיין אין קופונים', 'commerce-layer' ); ?></h2>
                <p style="color: #646970; margin-bottom: 20px;"><?php esc_html_e( 'צור את הקופון הראשון שלך והתחל להציע הנחות ללקוחות', 'commerce-layer' ); ?></p>
                <a href="<?php echo esc_url( add_query_arg( 'add_coupon', '1' ) ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'הוסף קופון חדש', 'commerce-layer' ); ?></a>
            </div>
        <?php else : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'cl_coupons_nonce', 'cl_coupons_nonce' ); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 15%;"><?php esc_html_e( 'קוד', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 20%;"><?php esc_html_e( 'תיאור', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 12%;"><?php esc_html_e( 'הנחה', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 12%;"><?php esc_html_e( 'שימושים', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 15%;"><?php esc_html_e( 'תוקף', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 10%;"><?php esc_html_e( 'סטטוס', 'commerce-layer' ); ?></th>
                            <th scope="col" style="width: 16%;"><?php esc_html_e( 'פעולות', 'commerce-layer' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $coupons as $coupon_data ) : ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url( add_query_arg( 'edit_coupon', $coupon_data['id'] ) ); ?>">
                                        <code style="font-size: 14px;"><?php echo esc_html( $coupon_data['code'] ); ?></code>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html( $coupon_data['description'] ?: '-' ); ?></td>
                            <td>
                                <strong>
                                <?php
                                if ( $coupon_data['discount_type'] === 'percent' ) {
                                    echo esc_html( $coupon_data['discount_value'] ) . '%';
                                } else {
                                    echo CL_Core::format_price( $coupon_data['discount_value'] );
                                }
                                ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                if ( $coupon_data['usage_limit'] ) {
                                    echo esc_html( $coupon_data['usage_count'] ) . ' / ' . esc_html( $coupon_data['usage_limit'] );
                                } else {
                                    echo esc_html( $coupon_data['usage_count'] ) . ' / <span style="color:#666;">∞</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ( $coupon_data['end_date'] ) {
                                    $end_date = strtotime( $coupon_data['end_date'] );
                                    if ( $end_date < time() ) {
                                        echo '<span style="color: #dc3232;">' . __( 'פג תוקף', 'commerce-layer' ) . '</span>';
                                    } else {
                                        echo date_i18n( 'd/m/Y', $end_date );
                                    }
                                } else {
                                    echo '<span style="color:#666;">' . __( 'ללא הגבלה', 'commerce-layer' ) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ( $coupon_data['status'] === 'active' ) : ?>
                                    <span style="color: #46b450; font-weight: 600;"><?php esc_html_e( 'פעיל', 'commerce-layer' ); ?></span>
                                <?php else : ?>
                                    <span style="color: #dc3232;"><?php esc_html_e( 'לא פעיל', 'commerce-layer' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( add_query_arg( 'edit_coupon', $coupon_data['id'] ) ); ?>" class="button button-small"><?php esc_html_e( 'עריכה', 'commerce-layer' ); ?></a>
                                <button type="submit" name="cl_coupon_action" value="delete" class="button button-small" style="color: #a00;" onclick="if(!confirm('<?php esc_attr_e( 'האם אתה בטוח שברצונך למחוק קופון זה?', 'commerce-layer' ); ?>')) return false;">
                                    <?php esc_html_e( 'מחיקה', 'commerce-layer' ); ?>
                                </button>
                                <input type="hidden" name="cl_coupon_id" value="<?php echo esc_attr( $coupon_data['id'] ); ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
