<?php
/**
 * Meta Box View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="cl-meta-box">
    <!-- Enable Commerce -->
    <div class="cl-field cl-field-toggle">
        <label>
            <input type="checkbox" name="cl_enabled" value="yes" <?php checked( $product->is_commerce_enabled() ); ?>>
            <strong><?php esc_html_e( 'הפעל מכירה לפריט זה', 'commerce-layer' ); ?></strong>
        </label>
    </div>

    <div class="cl-commerce-fields" style="<?php echo $product->is_commerce_enabled() ? '' : 'display:none;'; ?>">
        <!-- Price Section -->
        <div class="cl-section">
            <h4><?php esc_html_e( 'תמחור', 'commerce-layer' ); ?></h4>

            <div class="cl-field-row">
                <div class="cl-field">
                    <label for="cl_price"><?php esc_html_e( 'מחיר רגיל', 'commerce-layer' ); ?></label>
                    <input type="number" name="cl_price" id="cl_price"
                           value="<?php echo esc_attr( $product->get_regular_price() ); ?>"
                           step="0.01" min="0" class="small-text">
                    <span class="cl-currency"><?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?></span>
                </div>

                <div class="cl-field">
                    <label for="cl_sale_price"><?php esc_html_e( 'מחיר מבצע', 'commerce-layer' ); ?></label>
                    <input type="number" name="cl_sale_price" id="cl_sale_price"
                           value="<?php echo esc_attr( $product->get_sale_price() ); ?>"
                           step="0.01" min="0" class="small-text">
                    <span class="cl-currency"><?php echo esc_html( get_option( 'cl_currency_symbol', '₪' ) ); ?></span>
                </div>
            </div>
        </div>

        <!-- Purchase Options -->
        <div class="cl-section">
            <h4><?php esc_html_e( 'אפשרויות רכישה', 'commerce-layer' ); ?></h4>

            <div class="cl-field">
                <label for="cl_purchase_mode"><?php esc_html_e( 'מצב רכישה', 'commerce-layer' ); ?></label>
                <select name="cl_purchase_mode" id="cl_purchase_mode">
                    <option value="" <?php selected( $product->get_purchase_mode(), '' ); ?>><?php esc_html_e( 'ברירת מחדל', 'commerce-layer' ); ?></option>
                    <option value="cart" <?php selected( $product->get_purchase_mode(), 'cart' ); ?>><?php esc_html_e( 'הוסף לסל בלבד', 'commerce-layer' ); ?></option>
                    <option value="buy_now" <?php selected( $product->get_purchase_mode(), 'buy_now' ); ?>><?php esc_html_e( 'קנה עכשיו בלבד', 'commerce-layer' ); ?></option>
                    <option value="both" <?php selected( $product->get_purchase_mode(), 'both' ); ?>><?php esc_html_e( 'שניהם', 'commerce-layer' ); ?></option>
                </select>
            </div>

            <div class="cl-field-row">
                <div class="cl-field">
                    <label for="cl_min_quantity"><?php esc_html_e( 'כמות מינימום', 'commerce-layer' ); ?></label>
                    <input type="number" name="cl_min_quantity" id="cl_min_quantity"
                           value="<?php echo esc_attr( $product->get_min_quantity() ); ?>"
                           min="1" class="small-text">
                </div>

                <div class="cl-field">
                    <label for="cl_max_quantity"><?php esc_html_e( 'כמות מקסימום', 'commerce-layer' ); ?></label>
                    <input type="number" name="cl_max_quantity" id="cl_max_quantity"
                           value="<?php echo esc_attr( $product->get_max_quantity() ); ?>"
                           min="0" class="small-text">
                    <span class="description"><?php esc_html_e( '0 = ללא הגבלה', 'commerce-layer' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="cl-section cl-variants-section">
            <h4><?php esc_html_e( 'וריאציות', 'commerce-layer' ); ?></h4>

            <div class="cl-field cl-field-toggle">
                <label>
                    <input type="checkbox" name="cl_has_variants" id="cl_has_variants" value="yes"
                           <?php checked( $product->has_variants() ); ?>>
                    <?php esc_html_e( 'לפריט זה יש וריאציות (מידות, צבעים וכו\')', 'commerce-layer' ); ?>
                </label>
            </div>

            <div class="cl-variants-config" style="<?php echo $product->has_variants() ? '' : 'display:none;'; ?>">
                <!-- Attribute Selection -->
                <?php if ( ! empty( $global_attributes ) ) : ?>
                <div class="cl-field">
                    <label><?php esc_html_e( 'בחר מאפיינים', 'commerce-layer' ); ?></label>
                    <div class="cl-attributes-select">
                        <?php
                        $selected_attrs = $product->get_attributes();
                        foreach ( $global_attributes as $attr ) :
                        ?>
                            <label class="cl-attribute-checkbox">
                                <input type="checkbox" name="cl_attributes[]"
                                       value="<?php echo esc_attr( $attr['slug'] ); ?>"
                                       data-values='<?php echo esc_attr( json_encode( wp_list_pluck( $attr['values'], 'value' ) ) ); ?>'
                                       <?php checked( in_array( $attr['slug'], $selected_attrs ) ); ?>>
                                <?php echo esc_html( $attr['name'] ); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="description">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-attributes' ) ); ?>" target="_blank">
                            <?php esc_html_e( 'נהל מאפיינים', 'commerce-layer' ); ?>
                        </a>
                    </p>
                </div>
                <?php else : ?>
                <p class="cl-no-attributes">
                    <?php esc_html_e( 'לא הוגדרו מאפיינים עדיין.', 'commerce-layer' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-attributes' ) ); ?>"><?php esc_html_e( 'הוסף מאפיינים', 'commerce-layer' ); ?></a>
                </p>
                <?php endif; ?>

                <!-- Variants List -->
                <div class="cl-variants-list">
                    <h5><?php esc_html_e( 'רשימת וריאציות', 'commerce-layer' ); ?></h5>

                    <table class="wp-list-table widefat fixed striped cl-variants-table">
                        <thead>
                            <tr>
                                <th class="column-attributes"><?php esc_html_e( 'מאפיינים', 'commerce-layer' ); ?></th>
                                <th class="column-sku"><?php esc_html_e( 'מק"ט', 'commerce-layer' ); ?></th>
                                <th class="column-price"><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                                <th class="column-sale"><?php esc_html_e( 'מחיר מבצע', 'commerce-layer' ); ?></th>
                                <th class="column-stock"><?php esc_html_e( 'זמינות', 'commerce-layer' ); ?></th>
                                <th class="column-actions"><?php esc_html_e( 'פעולות', 'commerce-layer' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="cl-variants-body">
                            <?php
                            $variant_index = 0;
                            foreach ( $variants as $variant ) :
                                $v_data = $variant->get_data();
                            ?>
                            <tr class="cl-variant-row" data-index="<?php echo $variant_index; ?>">
                                <td class="column-attributes">
                                    <input type="hidden" name="cl_variants[<?php echo $variant_index; ?>][id]" value="<?php echo esc_attr( $v_data['id'] ); ?>">
                                    <?php
                                    foreach ( $v_data['attributes'] as $attr_slug => $attr_value ) :
                                    ?>
                                        <input type="hidden" name="cl_variants[<?php echo $variant_index; ?>][attributes][<?php echo esc_attr( $attr_slug ); ?>]" value="<?php echo esc_attr( $attr_value ); ?>">
                                        <span class="cl-variant-attr"><?php echo esc_html( $attr_value ); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="column-sku">
                                    <input type="text" name="cl_variants[<?php echo $variant_index; ?>][sku]" value="<?php echo esc_attr( $v_data['sku'] ); ?>" class="small-text">
                                </td>
                                <td class="column-price">
                                    <input type="number" name="cl_variants[<?php echo $variant_index; ?>][price]" value="<?php echo esc_attr( $v_data['regular_price'] ); ?>" step="0.01" min="0" class="small-text">
                                </td>
                                <td class="column-sale">
                                    <input type="number" name="cl_variants[<?php echo $variant_index; ?>][sale_price]" value="<?php echo esc_attr( $v_data['sale_price'] ); ?>" step="0.01" min="0" class="small-text">
                                </td>
                                <td class="column-stock">
                                    <select name="cl_variants[<?php echo $variant_index; ?>][stock_status]">
                                        <option value="instock" <?php selected( $v_data['stock_status'], 'instock' ); ?>><?php esc_html_e( 'במלאי', 'commerce-layer' ); ?></option>
                                        <option value="outofstock" <?php selected( $v_data['stock_status'], 'outofstock' ); ?>><?php esc_html_e( 'אזל', 'commerce-layer' ); ?></option>
                                    </select>
                                </td>
                                <td class="column-actions">
                                    <button type="button" class="button cl-delete-variant"><?php esc_html_e( 'מחק', 'commerce-layer' ); ?></button>
                                    <input type="hidden" name="cl_variants[<?php echo $variant_index; ?>][delete]" value="" class="cl-delete-flag">
                                </td>
                            </tr>
                            <?php
                                $variant_index++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>

                    <div class="cl-variants-actions">
                        <button type="button" class="button cl-generate-variants"><?php esc_html_e( 'צור וריאציות אוטומטית', 'commerce-layer' ); ?></button>
                        <button type="button" class="button cl-add-variant"><?php esc_html_e( 'הוסף וריאציה ידנית', 'commerce-layer' ); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="cl-variant-row-template">
<tr class="cl-variant-row" data-index="{{index}}">
    <td class="column-attributes">
        <input type="hidden" name="cl_variants[{{index}}][id]" value="">
        {{attributes_inputs}}
        {{attributes_display}}
    </td>
    <td class="column-sku">
        <input type="text" name="cl_variants[{{index}}][sku]" value="" class="small-text">
    </td>
    <td class="column-price">
        <input type="number" name="cl_variants[{{index}}][price]" value="{{base_price}}" step="0.01" min="0" class="small-text">
    </td>
    <td class="column-sale">
        <input type="number" name="cl_variants[{{index}}][sale_price]" value="" step="0.01" min="0" class="small-text">
    </td>
    <td class="column-stock">
        <select name="cl_variants[{{index}}][stock_status]">
            <option value="instock"><?php esc_html_e( 'במלאי', 'commerce-layer' ); ?></option>
            <option value="outofstock"><?php esc_html_e( 'אזל', 'commerce-layer' ); ?></option>
        </select>
    </td>
    <td class="column-actions">
        <button type="button" class="button cl-delete-variant"><?php esc_html_e( 'מחק', 'commerce-layer' ); ?></button>
        <input type="hidden" name="cl_variants[{{index}}][delete]" value="" class="cl-delete-flag">
    </td>
</tr>
</script>

<style>
.cl-meta-box { padding: 10px 0; }
.cl-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; }
.cl-section h4 { margin: 0 0 15px; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
.cl-field { margin-bottom: 15px; }
.cl-field label { display: block; margin-bottom: 5px; font-weight: 600; }
.cl-field-row { display: flex; gap: 20px; }
.cl-field-row .cl-field { flex: 1; }
.cl-field-toggle { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
.cl-field-toggle label { display: flex; align-items: center; margin: 0; }
.cl-field-toggle input { margin-left: 10px; }
.cl-currency { margin-right: 5px; color: #666; }
.cl-attributes-select { display: flex; flex-wrap: wrap; gap: 10px; }
.cl-attribute-checkbox { padding: 8px 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
.cl-variants-table { margin-top: 15px; }
.cl-variants-table input.small-text { width: 80px; }
.cl-variant-attr { display: inline-block; padding: 2px 8px; background: #e0e0e0; border-radius: 3px; margin: 2px; font-size: 12px; }
.cl-variants-actions { margin-top: 15px; }
.cl-variants-actions .button { margin-left: 10px; }
.cl-no-attributes { padding: 15px; background: #fff3cd; border-radius: 4px; }
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle commerce fields
    $('input[name="cl_enabled"]').on('change', function() {
        $('.cl-commerce-fields').toggle(this.checked);
    });

    // Toggle variants config
    $('#cl_has_variants').on('change', function() {
        $('.cl-variants-config').toggle(this.checked);
    });

    // Delete variant
    $(document).on('click', '.cl-delete-variant', function() {
        if (confirm(clAdmin.strings.confirmDelete)) {
            var $row = $(this).closest('tr');
            $row.find('.cl-delete-flag').val('1');
            $row.hide();
        }
    });

    // Generate variants
    $('.cl-generate-variants').on('click', function() {
        var selectedAttrs = {};
        $('.cl-attributes-select input:checked').each(function() {
            var slug = $(this).val();
            var values = $(this).data('values');
            if (values && values.length) {
                selectedAttrs[slug] = values;
            }
        });

        if ($.isEmptyObject(selectedAttrs)) {
            alert('<?php esc_html_e( 'בחר מאפיינים לפני יצירת וריאציות', 'commerce-layer' ); ?>');
            return;
        }

        var basePrice = $('#cl_price').val() || 0;
        var combinations = generateCombinations(selectedAttrs);
        var template = $('#cl-variant-row-template').html();
        var $tbody = $('#cl-variants-body');
        var startIndex = $tbody.find('tr').length;

        combinations.forEach(function(combo, i) {
            var index = startIndex + i;
            var attrsInputs = '';
            var attrsDisplay = '';

            for (var slug in combo) {
                attrsInputs += '<input type="hidden" name="cl_variants[' + index + '][attributes][' + slug + ']" value="' + combo[slug] + '">';
                attrsDisplay += '<span class="cl-variant-attr">' + combo[slug] + '</span>';
            }

            var row = template
                .replace(/\{\{index\}\}/g, index)
                .replace('{{attributes_inputs}}', attrsInputs)
                .replace('{{attributes_display}}', attrsDisplay)
                .replace('{{base_price}}', basePrice);

            $tbody.append(row);
        });
    });

    function generateCombinations(attrs) {
        var keys = Object.keys(attrs);
        var result = [{}];

        keys.forEach(function(key) {
            var newResult = [];
            result.forEach(function(combo) {
                attrs[key].forEach(function(value) {
                    var newCombo = $.extend({}, combo);
                    newCombo[key] = value;
                    newResult.push(newCombo);
                });
            });
            result = newResult;
        });

        return result;
    }
});
</script>
