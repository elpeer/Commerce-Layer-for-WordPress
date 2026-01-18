<?php
/**
 * Settings View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'הגדרות Commerce Layer', 'commerce-layer' ); ?></h1>

    <?php settings_errors( 'cl_settings' ); ?>

    <nav class="nav-tab-wrapper">
        <?php foreach ( $this->tabs as $tab_id => $tab_name ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id, admin_url( 'admin.php?page=cl-settings' ) ) ); ?>"
               class="nav-tab <?php echo $this->current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html( $tab_name ); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <form method="post" action="">
        <?php wp_nonce_field( 'cl_settings_nonce', 'cl_settings_nonce' ); ?>

        <?php if ( 'general' === $this->current_tab ) : ?>
            <!-- General Settings -->
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'סוגי תוכן למכירה', 'commerce-layer' ); ?></th>
                    <td>
                        <fieldset>
                            <?php
                            $enabled_types = get_option( 'cl_enabled_post_types', array() );
                            foreach ( $this->get_available_post_types() as $type => $label ) :
                            ?>
                                <label>
                                    <input type="checkbox"
                                           name="cl_enabled_post_types[]"
                                           value="<?php echo esc_attr( $type ); ?>"
                                           <?php checked( in_array( $type, $enabled_types ) ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'בחר על אילו סוגי תוכן להפעיל אפשרות מכירה', 'commerce-layer' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מטבע', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_currency">
                            <option value="ILS" <?php selected( get_option( 'cl_currency' ), 'ILS' ); ?>>₪ שקל ישראלי (ILS)</option>
                            <option value="USD" <?php selected( get_option( 'cl_currency' ), 'USD' ); ?>>$ דולר אמריקאי (USD)</option>
                            <option value="EUR" <?php selected( get_option( 'cl_currency' ), 'EUR' ); ?>>€ אירו (EUR)</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'סמל מטבע', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="text" name="cl_currency_symbol" value="<?php echo esc_attr( get_option( 'cl_currency_symbol', '₪' ) ); ?>" class="small-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מיקום סמל', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_currency_position">
                            <option value="right" <?php selected( get_option( 'cl_currency_position' ), 'right' ); ?>>100₪ (ימין)</option>
                            <option value="right_space" <?php selected( get_option( 'cl_currency_position' ), 'right_space' ); ?>>100 ₪ (ימין עם רווח)</option>
                            <option value="left" <?php selected( get_option( 'cl_currency_position' ), 'left' ); ?>>₪100 (שמאל)</option>
                            <option value="left_space" <?php selected( get_option( 'cl_currency_position' ), 'left_space' ); ?>>₪ 100 (שמאל עם רווח)</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'תבנית מספר', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <?php esc_html_e( 'מפריד אלפים:', 'commerce-layer' ); ?>
                            <input type="text" name="cl_thousand_separator" value="<?php echo esc_attr( get_option( 'cl_thousand_separator', ',' ) ); ?>" class="small-text">
                        </label>
                        <label style="margin-right: 20px;">
                            <?php esc_html_e( 'מפריד עשרוני:', 'commerce-layer' ); ?>
                            <input type="text" name="cl_decimal_separator" value="<?php echo esc_attr( get_option( 'cl_decimal_separator', '.' ) ); ?>" class="small-text">
                        </label>
                        <label style="margin-right: 20px;">
                            <?php esc_html_e( 'ספרות אחרי נקודה:', 'commerce-layer' ); ?>
                            <input type="number" name="cl_decimals" value="<?php echo esc_attr( get_option( 'cl_decimals', 2 ) ); ?>" class="small-text" min="0" max="4">
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מודל רכישה ברירת מחדל', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_purchase_mode">
                            <option value="cart" <?php selected( get_option( 'cl_purchase_mode' ), 'cart' ); ?>><?php esc_html_e( 'סל קניות', 'commerce-layer' ); ?></option>
                            <option value="buy_now" <?php selected( get_option( 'cl_purchase_mode' ), 'buy_now' ); ?>><?php esc_html_e( 'קנייה מיידית', 'commerce-layer' ); ?></option>
                            <option value="both" <?php selected( get_option( 'cl_purchase_mode' ), 'both' ); ?>><?php esc_html_e( 'שניהם', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

        <?php elseif ( 'checkout' === $this->current_tab ) : ?>
            <!-- Checkout Settings -->
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'שער תשלום', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_payment_gateway" id="cl-payment-gateway">
                            <option value="tranzila" <?php selected( get_option( 'cl_payment_gateway' ), 'tranzila' ); ?>>Tranzila</option>
                            <option value="cardcom" <?php selected( get_option( 'cl_payment_gateway' ), 'cardcom' ); ?>>Cardcom</option>
                            <option value="paypal" <?php selected( get_option( 'cl_payment_gateway' ), 'paypal' ); ?>>PayPal</option>
                            <option value="test" <?php selected( get_option( 'cl_payment_gateway' ), 'test' ); ?>><?php esc_html_e( 'מצב בדיקה', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <!-- Tranzila Settings -->
            <div class="cl-gateway-settings" data-gateway="tranzila">
                <h3>Tranzila</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'מספר מסוף', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="text" name="cl_tranzila_terminal" value="<?php echo esc_attr( get_option( 'cl_tranzila_terminal' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'סיסמה (אופציונלי)', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="password" name="cl_tranzila_password" value="<?php echo esc_attr( get_option( 'cl_tranzila_password' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Cardcom Settings -->
            <div class="cl-gateway-settings" data-gateway="cardcom" style="display: none;">
                <h3>Cardcom</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'מספר מסוף', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="text" name="cl_cardcom_terminal" value="<?php echo esc_attr( get_option( 'cl_cardcom_terminal' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'שם משתמש', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="text" name="cl_cardcom_username" value="<?php echo esc_attr( get_option( 'cl_cardcom_username' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- PayPal Settings -->
            <div class="cl-gateway-settings" data-gateway="paypal" style="display: none;">
                <h3>PayPal</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Client ID', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="text" name="cl_paypal_client_id" value="<?php echo esc_attr( get_option( 'cl_paypal_client_id' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <hr>

            <h3><?php esc_html_e( 'עמודי מערכת', 'commerce-layer' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'עמוד סל', 'commerce-layer' ); ?></th>
                    <td>
                        <?php $this->get_pages_dropdown( 'cl_cart_page_id', get_option( 'cl_cart_page_id' ) ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'עמוד תשלום', 'commerce-layer' ); ?></th>
                    <td>
                        <?php $this->get_pages_dropdown( 'cl_checkout_page_id', get_option( 'cl_checkout_page_id' ) ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'עמוד תודה', 'commerce-layer' ); ?></th>
                    <td>
                        <?php $this->get_pages_dropdown( 'cl_thank_you_page_id', get_option( 'cl_thank_you_page_id' ) ); ?>
                    </td>
                </tr>
            </table>

        <?php elseif ( 'display' === $this->current_tab ) : ?>
            <!-- Display Settings -->
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'מצב הצגה', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_display_mode">
                            <option value="auto" <?php selected( get_option( 'cl_display_mode' ), 'auto' ); ?>><?php esc_html_e( 'אוטומטי - הזרקה לתוכן', 'commerce-layer' ); ?></option>
                            <option value="shortcode" <?php selected( get_option( 'cl_display_mode' ), 'shortcode' ); ?>><?php esc_html_e( 'ידני - רק באמצעות Shortcode', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מיקום הזרקה אוטומטית', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_display_position">
                            <option value="after_content" <?php selected( get_option( 'cl_display_position' ), 'after_content' ); ?>><?php esc_html_e( 'אחרי התוכן', 'commerce-layer' ); ?></option>
                            <option value="before_content" <?php selected( get_option( 'cl_display_position' ), 'before_content' ); ?>><?php esc_html_e( 'לפני התוכן', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'טקסט כפתור "הוסף לסל"', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="text" name="cl_add_to_cart_text" value="<?php echo esc_attr( get_option( 'cl_add_to_cart_text', __( 'הוסף לסל', 'commerce-layer' ) ) ); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'טקסט כפתור "קנה עכשיו"', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="text" name="cl_buy_now_text" value="<?php echo esc_attr( get_option( 'cl_buy_now_text', __( 'קנה עכשיו', 'commerce-layer' ) ) ); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'סרגל צף', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cl_floating_bar_enabled" value="yes" <?php checked( get_option( 'cl_floating_bar_enabled' ), 'yes' ); ?>>
                            <?php esc_html_e( 'הפעל סרגל צף במובייל', 'commerce-layer' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'מציג סרגל קבוע בתחתית המסך במכשירים ניידים', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
            </table>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle gateway settings
    function toggleGatewaySettings() {
        var gateway = $('#cl-payment-gateway').val();
        $('.cl-gateway-settings').hide();
        $('.cl-gateway-settings[data-gateway="' + gateway + '"]').show();
    }

    toggleGatewaySettings();
    $('#cl-payment-gateway').on('change', toggleGatewaySettings);
});
</script>
