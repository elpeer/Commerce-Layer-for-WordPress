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

        <?php elseif ( 'shipping' === $this->current_tab ) : ?>
            <!-- Shipping Settings -->
            <h2 class="title"><?php esc_html_e( 'אפשרויות משלוח', 'commerce-layer' ); ?></h2>
            <p class="description"><?php esc_html_e( 'הגדר את אפשרויות המשלוח שיוצגו ללקוחות בעמוד התשלום', 'commerce-layer' ); ?></p>

            <?php
            $shipping_methods = get_option( 'cl_shipping_methods', array() );
            if ( empty( $shipping_methods ) ) {
                // Default shipping methods
                $shipping_methods = array(
                    array( 'name' => 'איסוף עצמי', 'price' => 0, 'enabled' => true ),
                    array( 'name' => 'משלוח רגיל', 'price' => 30, 'enabled' => true ),
                    array( 'name' => 'משלוח אקספרס', 'price' => 50, 'enabled' => true ),
                );
            }
            ?>

            <table class="widefat striped" id="cl-shipping-methods-table" style="max-width: 600px;">
                <thead>
                    <tr>
                        <th style="width: 40px;"><?php esc_html_e( 'פעיל', 'commerce-layer' ); ?></th>
                        <th><?php esc_html_e( 'שם אפשרות', 'commerce-layer' ); ?></th>
                        <th style="width: 100px;"><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $shipping_methods as $index => $method ) : ?>
                    <tr class="cl-shipping-method-row">
                        <td>
                            <input type="checkbox" name="cl_shipping_method_enabled[<?php echo $index; ?>]" value="1" <?php checked( $method['enabled'] ?? true ); ?>>
                        </td>
                        <td>
                            <input type="text" name="cl_shipping_method_name[<?php echo $index; ?>]" value="<?php echo esc_attr( $method['name'] ); ?>" class="regular-text">
                        </td>
                        <td>
                            <input type="number" name="cl_shipping_method_price[<?php echo $index; ?>]" value="<?php echo esc_attr( $method['price'] ); ?>" min="0" step="0.01" class="small-text"> <?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?>
                        </td>
                        <td>
                            <button type="button" class="button cl-remove-shipping-method" title="<?php esc_attr_e( 'הסר', 'commerce-layer' ); ?>">×</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <button type="button" class="button" id="cl-add-shipping-method">
                                <?php esc_html_e( '+ הוסף אפשרות משלוח', 'commerce-layer' ); ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <h2 class="title" style="margin-top: 30px;"><?php esc_html_e( 'משלוח חינם', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'סף למשלוח חינם', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_free_shipping_threshold" value="<?php echo esc_attr( get_option( 'cl_free_shipping_threshold', 200 ) ); ?>" min="0" step="1" class="small-text"> <?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?>
                        <p class="description"><?php esc_html_e( 'סכום הזמנה מינימלי לקבלת משלוח חינם. הזן 0 לביטול.', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2 class="title" style="margin-top: 30px;"><?php esc_html_e( 'תצוגת הנחות', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'הצג הנחות בסל', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cl_show_discount_in_cart" value="yes" <?php checked( get_option( 'cl_show_discount_in_cart', 'yes' ), 'yes' ); ?>>
                            <?php esc_html_e( 'הצג מחיר מקורי, הנחה וחיסכון לכל מוצר ובסך הכל', 'commerce-layer' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(document).ready(function($) {
                var methodIndex = <?php echo count( $shipping_methods ); ?>;

                // Add new shipping method
                $('#cl-add-shipping-method').on('click', function() {
                    var row = '<tr class="cl-shipping-method-row">' +
                        '<td><input type="checkbox" name="cl_shipping_method_enabled[' + methodIndex + ']" value="1" checked></td>' +
                        '<td><input type="text" name="cl_shipping_method_name[' + methodIndex + ']" value="" class="regular-text" placeholder="<?php esc_attr_e( 'שם אפשרות', 'commerce-layer' ); ?>"></td>' +
                        '<td><input type="number" name="cl_shipping_method_price[' + methodIndex + ']" value="0" min="0" step="0.01" class="small-text"> <?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?></td>' +
                        '<td><button type="button" class="button cl-remove-shipping-method" title="<?php esc_attr_e( 'הסר', 'commerce-layer' ); ?>">×</button></td>' +
                        '</tr>';
                    $('#cl-shipping-methods-table tbody').append(row);
                    methodIndex++;
                });

                // Remove shipping method
                $(document).on('click', '.cl-remove-shipping-method', function() {
                    $(this).closest('tr').remove();
                });
            });
            </script>

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

            <!-- Floating Cart Icon Settings -->
            <h2 class="title"><?php esc_html_e( 'אייקון סל צף', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'הפעל אייקון סל צף', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cl_floating_cart_icon_enabled" value="yes" <?php checked( get_option( 'cl_floating_cart_icon_enabled', 'yes' ), 'yes' ); ?>>
                            <?php esc_html_e( 'הצג אייקון סל צף באתר', 'commerce-layer' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'לחיצה על האייקון פותחת את סל הקניות הצדדי', 'commerce-layer' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מיקום בדסקטופ', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_cart_icon_desktop_position">
                            <option value="top-left" <?php selected( get_option( 'cl_cart_icon_desktop_position', 'top-left' ), 'top-left' ); ?>><?php esc_html_e( 'למעלה משמאל', 'commerce-layer' ); ?></option>
                            <option value="top-center" <?php selected( get_option( 'cl_cart_icon_desktop_position' ), 'top-center' ); ?>><?php esc_html_e( 'למעלה באמצע', 'commerce-layer' ); ?></option>
                            <option value="top-right" <?php selected( get_option( 'cl_cart_icon_desktop_position' ), 'top-right' ); ?>><?php esc_html_e( 'למעלה מימין', 'commerce-layer' ); ?></option>
                            <option value="bottom-left" <?php selected( get_option( 'cl_cart_icon_desktop_position' ), 'bottom-left' ); ?>><?php esc_html_e( 'למטה משמאל', 'commerce-layer' ); ?></option>
                            <option value="bottom-center" <?php selected( get_option( 'cl_cart_icon_desktop_position' ), 'bottom-center' ); ?>><?php esc_html_e( 'למטה באמצע', 'commerce-layer' ); ?></option>
                            <option value="bottom-right" <?php selected( get_option( 'cl_cart_icon_desktop_position' ), 'bottom-right' ); ?>><?php esc_html_e( 'למטה מימין', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מרחק מהקצה (דסקטופ)', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_cart_icon_desktop_offset" value="<?php echo esc_attr( get_option( 'cl_cart_icon_desktop_offset', 20 ) ); ?>" min="0" max="200" class="small-text"> <?php esc_html_e( 'פיקסלים', 'commerce-layer' ); ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מיקום במובייל', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_cart_icon_mobile_position">
                            <option value="top-left" <?php selected( get_option( 'cl_cart_icon_mobile_position' ), 'top-left' ); ?>><?php esc_html_e( 'למעלה משמאל', 'commerce-layer' ); ?></option>
                            <option value="top-center" <?php selected( get_option( 'cl_cart_icon_mobile_position' ), 'top-center' ); ?>><?php esc_html_e( 'למעלה באמצע', 'commerce-layer' ); ?></option>
                            <option value="top-right" <?php selected( get_option( 'cl_cart_icon_mobile_position' ), 'top-right' ); ?>><?php esc_html_e( 'למעלה מימין', 'commerce-layer' ); ?></option>
                            <option value="bottom-left" <?php selected( get_option( 'cl_cart_icon_mobile_position' ), 'bottom-left' ); ?>><?php esc_html_e( 'למטה משמאל', 'commerce-layer' ); ?></option>
                            <option value="bottom-center" <?php selected( get_option( 'cl_cart_icon_mobile_position' ), 'bottom-center' ); ?>><?php esc_html_e( 'למטה באמצע', 'commerce-layer' ); ?></option>
                            <option value="bottom-right" <?php selected( get_option( 'cl_cart_icon_mobile_position', 'bottom-right' ), 'bottom-right' ); ?>><?php esc_html_e( 'למטה מימין', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'מרחק מהקצה (מובייל)', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_cart_icon_mobile_offset" value="<?php echo esc_attr( get_option( 'cl_cart_icon_mobile_offset', 20 ) ); ?>" min="0" max="200" class="small-text"> <?php esc_html_e( 'פיקסלים', 'commerce-layer' ); ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'שורטקוד למיקום ידני', 'commerce-layer' ); ?></th>
                    <td>
                        <code style="background: #f0f0f0; padding: 8px 12px; display: inline-block; border-radius: 4px;">[cl_cart_icon]</code>
                        <p class="description"><?php esc_html_e( 'השתמש בשורטקוד זה אם תרצה למקם את האייקון ידנית (למשל בתפריט או בהדר)', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
            </table>
        <?php elseif ( 'styling' === $this->current_tab ) : ?>
            <!-- Styling Settings -->
            <h2 class="title"><?php esc_html_e( 'ערכת נושא', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'בחר ערכת נושא', 'commerce-layer' ); ?></th>
                    <td>
                        <fieldset>
                            <?php $theme_preset = get_option( 'cl_theme_preset', 'modern' ); ?>
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="cl_theme_preset" value="classic" <?php checked( $theme_preset, 'classic' ); ?>>
                                <strong><?php esc_html_e( 'קלאסי', 'commerce-layer' ); ?></strong>
                                <span style="color: #666;"> - <?php esc_html_e( 'שחור / לבן / אפור', 'commerce-layer' ); ?></span>
                            </label>
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="cl_theme_preset" value="modern" <?php checked( $theme_preset, 'modern' ); ?>>
                                <strong><?php esc_html_e( 'מודרני', 'commerce-layer' ); ?></strong>
                                <span style="color: #666;"> - <?php esc_html_e( 'כחול / לבן (ברירת מחדל)', 'commerce-layer' ); ?></span>
                            </label>
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="cl_theme_preset" value="custom" <?php checked( $theme_preset, 'custom' ); ?>>
                                <strong><?php esc_html_e( 'מותאם אישית', 'commerce-layer' ); ?></strong>
                                <span style="color: #666;"> - <?php esc_html_e( 'בחר צבעים בעצמך', 'commerce-layer' ); ?></span>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <!-- Custom Colors (shown only when custom is selected) -->
            <div id="cl-custom-colors" style="<?php echo $theme_preset !== 'custom' ? 'display:none;' : ''; ?>">
                <h2 class="title"><?php esc_html_e( 'צבעים מותאמים אישית', 'commerce-layer' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע ראשי', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_primary" value="<?php echo esc_attr( get_option( 'cl_color_primary', '#2563eb' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע ראשי כהה (hover)', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_primary_dark" value="<?php echo esc_attr( get_option( 'cl_color_primary_dark', '#1e40af' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע משני', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_secondary" value="<?php echo esc_attr( get_option( 'cl_color_secondary', '#64748b' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע טקסט', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_text" value="<?php echo esc_attr( get_option( 'cl_color_text', '#1e293b' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע רקע', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_background" value="<?php echo esc_attr( get_option( 'cl_color_background', '#ffffff' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע הצלחה', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_success" value="<?php echo esc_attr( get_option( 'cl_color_success', '#10b981' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'צבע שגיאה', 'commerce-layer' ); ?></th>
                        <td>
                            <input type="color" name="cl_color_danger" value="<?php echo esc_attr( get_option( 'cl_color_danger', '#ef4444' ) ); ?>">
                        </td>
                    </tr>
                </table>
            </div>

            <h2 class="title"><?php esc_html_e( 'כפתורים', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'פינות כפתור', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_button_corners">
                            <option value="square" <?php selected( get_option( 'cl_button_corners', 'rounded' ), 'square' ); ?>><?php esc_html_e( 'מרובע', 'commerce-layer' ); ?></option>
                            <option value="rounded" <?php selected( get_option( 'cl_button_corners', 'rounded' ), 'rounded' ); ?>><?php esc_html_e( 'מעוגל קל', 'commerce-layer' ); ?></option>
                            <option value="pill" <?php selected( get_option( 'cl_button_corners', 'rounded' ), 'pill' ); ?>><?php esc_html_e( 'מעוגל מאוד', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'סגנון כפתור', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_button_style">
                            <option value="filled" <?php selected( get_option( 'cl_button_style', 'gradient' ), 'filled' ); ?>><?php esc_html_e( 'מלא', 'commerce-layer' ); ?></option>
                            <option value="outline" <?php selected( get_option( 'cl_button_style', 'gradient' ), 'outline' ); ?>><?php esc_html_e( 'מסגרת', 'commerce-layer' ); ?></option>
                            <option value="gradient" <?php selected( get_option( 'cl_button_style', 'gradient' ), 'gradient' ); ?>><?php esc_html_e( 'גרדיאנט', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e( 'כפתור "הוסף לסל"', 'commerce-layer' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע רקע', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_add_to_cart_bg" value="<?php echo esc_attr( get_option( 'cl_add_to_cart_bg', '#2563eb' ) ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע טקסט', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_add_to_cart_text" value="<?php echo esc_attr( get_option( 'cl_add_to_cart_text', '#ffffff' ) ); ?>">
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e( 'כפתור "קנה עכשיו"', 'commerce-layer' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע רקע', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_buy_now_bg" value="<?php echo esc_attr( get_option( 'cl_buy_now_bg', '#10b981' ) ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע טקסט', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_buy_now_text" value="<?php echo esc_attr( get_option( 'cl_buy_now_text', '#ffffff' ) ); ?>">
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e( 'אייקון סל קניות', 'commerce-layer' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע אייקון', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_cart_icon_color" value="<?php echo esc_attr( get_option( 'cl_cart_icon_color', '#2563eb' ) ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע רקע', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_cart_icon_bg" value="<?php echo esc_attr( get_option( 'cl_cart_icon_bg', '#ffffff' ) ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'צבע תגית כמות', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="color" name="cl_cart_icon_badge_bg" value="<?php echo esc_attr( get_option( 'cl_cart_icon_badge_bg', '#ef4444' ) ); ?>">
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e( 'סל צדדי (Side Cart)', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'רוחב הסל', 'commerce-layer' ); ?></th>
                    <td>
                        <input type="number" name="cl_side_cart_width" value="<?php echo esc_attr( get_option( 'cl_side_cart_width', 420 ) ); ?>" min="300" max="600" class="small-text"> <?php esc_html_e( 'פיקסלים', 'commerce-layer' ); ?>
                        <p class="description"><?php esc_html_e( 'מומלץ: 380-480 פיקסלים', 'commerce-layer' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'צד פתיחה', 'commerce-layer' ); ?></th>
                    <td>
                        <select name="cl_side_cart_side">
                            <option value="right" <?php selected( get_option( 'cl_side_cart_side', 'right' ), 'right' ); ?>><?php esc_html_e( 'ימין', 'commerce-layer' ); ?></option>
                            <option value="left" <?php selected( get_option( 'cl_side_cart_side', 'right' ), 'left' ); ?>><?php esc_html_e( 'שמאל', 'commerce-layer' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'פס משלוח חינם', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cl_side_cart_shipping_bar" value="yes" <?php checked( get_option( 'cl_side_cart_shipping_bar', 'yes' ), 'yes' ); ?>>
                            <?php esc_html_e( 'הצג פס התקדמות למשלוח חינם', 'commerce-layer' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'טיימר דחיפות', 'commerce-layer' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cl_side_cart_urgency_timer" value="yes" <?php checked( get_option( 'cl_side_cart_urgency_timer', 'no' ), 'yes' ); ?>>
                            <?php esc_html_e( 'הצג טיימר דחיפות (המוצרים מוגבלים)', 'commerce-layer' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e( 'CSS מותאם אישית', 'commerce-layer' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'קוד CSS נוסף', 'commerce-layer' ); ?></th>
                    <td>
                        <textarea name="cl_custom_css" rows="10" class="large-text code" placeholder="<?php esc_attr_e( '/* הוסף כאן CSS מותאם אישית */', 'commerce-layer' ); ?>"><?php echo esc_textarea( get_option( 'cl_custom_css', '' ) ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'הזן קוד CSS מותאם אישית שיחול על כל רכיבי התוסף', 'commerce-layer' ); ?></p>
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

    // Toggle custom colors section based on theme preset
    $('input[name="cl_theme_preset"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#cl-custom-colors').slideDown();
        } else {
            $('#cl-custom-colors').slideUp();
        }
    });
});
</script>
