<?php
/**
 * Products List View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap cl-products-list-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'ניהול מוצרים', 'commerce-layer' ); ?></h1>

    <?php if ( empty( $enabled_post_types ) ) : ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e( 'לא נבחרו סוגי תוכן. עבור להגדרות כדי לבחור סוגי תוכן שיאופשר עבורם קומרס.', 'commerce-layer' ); ?></p>
            <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-settings' ) ); ?>" class="button"><?php esc_html_e( 'עבור להגדרות', 'commerce-layer' ); ?></a></p>
        </div>
    <?php else : ?>

        <!-- Filters Bar -->
        <div class="cl-products-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="cl-products">

                <?php if ( count( $post_type_objects ) > 1 ) : ?>
                    <select name="post_type_filter" id="cl-post-type-filter">
                        <option value=""><?php esc_html_e( 'כל סוגי התוכן', 'commerce-layer' ); ?></option>
                        <?php foreach ( $post_type_objects as $pt_slug => $pt_obj ) : ?>
                            <option value="<?php echo esc_attr( $pt_slug ); ?>" <?php selected( $current_post_type, $pt_slug ); ?>>
                                <?php echo esc_html( $pt_obj->label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'חיפוש...', 'commerce-layer' ); ?>">
                <button type="submit" class="button"><?php esc_html_e( 'סנן', 'commerce-layer' ); ?></button>
            </form>

            <div class="cl-products-actions">
                <button type="button" class="button button-primary" id="cl-save-all-products">
                    <?php esc_html_e( 'שמור הכל', 'commerce-layer' ); ?>
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <form id="cl-products-form">
            <table class="wp-list-table widefat fixed striped cl-products-table">
                <thead>
                    <tr>
                        <th class="cl-col-thumb"><?php esc_html_e( 'תמונה', 'commerce-layer' ); ?></th>
                        <th class="cl-col-title"><?php esc_html_e( 'כותרת', 'commerce-layer' ); ?></th>
                        <?php if ( count( $post_type_objects ) > 1 ) : ?>
                            <th class="cl-col-type"><?php esc_html_e( 'סוג', 'commerce-layer' ); ?></th>
                        <?php endif; ?>
                        <th class="cl-col-enabled"><?php esc_html_e( 'מופעל', 'commerce-layer' ); ?></th>
                        <th class="cl-col-price"><?php esc_html_e( 'מחיר', 'commerce-layer' ); ?></th>
                        <th class="cl-col-sale-price"><?php esc_html_e( 'מחיר מבצע', 'commerce-layer' ); ?></th>
                        <th class="cl-col-mode"><?php esc_html_e( 'מצב רכישה', 'commerce-layer' ); ?></th>
                        <th class="cl-col-min-qty"><?php esc_html_e( 'כמות מינ׳', 'commerce-layer' ); ?></th>
                        <th class="cl-col-max-qty"><?php esc_html_e( 'כמות מקס׳', 'commerce-layer' ); ?></th>
                        <th class="cl-col-variants"><?php esc_html_e( 'וריאציות', 'commerce-layer' ); ?></th>
                        <th class="cl-col-actions"><?php esc_html_e( 'פעולות', 'commerce-layer' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $products ) ) : ?>
                        <tr>
                            <td colspan="<?php echo count( $post_type_objects ) > 1 ? '11' : '10'; ?>">
                                <?php esc_html_e( 'לא נמצאו מוצרים.', 'commerce-layer' ); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $products as $item ) :
                            $post = $item['post'];
                            $product = $item['product'];
                            $post_type_obj = get_post_type_object( $post->post_type );
                            $thumbnail = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
                            $edit_url = get_edit_post_link( $post->ID );
                            $variants = $product->get_variants();
                            $variants_count = count( $variants );
                        ?>
                            <tr class="cl-product-row" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                <td class="cl-col-thumb">
                                    <?php if ( $thumbnail ) : ?>
                                        <img src="<?php echo esc_url( $thumbnail ); ?>" alt="" class="cl-product-thumb">
                                    <?php else : ?>
                                        <span class="cl-no-thumb">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cl-col-title">
                                    <strong>
                                        <a href="<?php echo esc_url( $edit_url ); ?>" target="_blank">
                                            <?php echo esc_html( $post->post_title ); ?>
                                        </a>
                                    </strong>
                                </td>
                                <?php if ( count( $post_type_objects ) > 1 ) : ?>
                                    <td class="cl-col-type">
                                        <span class="cl-post-type-badge"><?php echo esc_html( $post_type_obj->labels->singular_name ); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td class="cl-col-enabled">
                                    <label class="cl-toggle">
                                        <input type="checkbox" name="products[<?php echo esc_attr( $post->ID ); ?>][enabled]" value="yes" <?php checked( $product->is_commerce_enabled(), true ); ?>>
                                        <span class="cl-toggle-slider"></span>
                                    </label>
                                </td>
                                <td class="cl-col-price">
                                    <input type="number" step="0.01" min="0"
                                           name="products[<?php echo esc_attr( $post->ID ); ?>][price]"
                                           value="<?php echo esc_attr( $product->get_regular_price() ); ?>"
                                           class="cl-price-input"
                                           <?php echo $product->has_variants() ? 'disabled title="' . esc_attr__( 'מוגדר בוריאציות', 'commerce-layer' ) . '"' : ''; ?>>
                                </td>
                                <td class="cl-col-sale-price">
                                    <input type="number" step="0.01" min="0"
                                           name="products[<?php echo esc_attr( $post->ID ); ?>][sale_price]"
                                           value="<?php echo esc_attr( $product->get_sale_price() ); ?>"
                                           class="cl-price-input"
                                           <?php echo $product->has_variants() ? 'disabled title="' . esc_attr__( 'מוגדר בוריאציות', 'commerce-layer' ) . '"' : ''; ?>>
                                </td>
                                <td class="cl-col-mode">
                                    <select name="products[<?php echo esc_attr( $post->ID ); ?>][purchase_mode]" class="cl-mode-select">
                                        <option value="both" <?php selected( $product->get_purchase_mode(), 'both' ); ?>><?php esc_html_e( 'שניהם', 'commerce-layer' ); ?></option>
                                        <option value="cart" <?php selected( $product->get_purchase_mode(), 'cart' ); ?>><?php esc_html_e( 'סל בלבד', 'commerce-layer' ); ?></option>
                                        <option value="buy_now" <?php selected( $product->get_purchase_mode(), 'buy_now' ); ?>><?php esc_html_e( 'קנה עכשיו', 'commerce-layer' ); ?></option>
                                    </select>
                                </td>
                                <td class="cl-col-min-qty">
                                    <input type="number" min="1"
                                           name="products[<?php echo esc_attr( $post->ID ); ?>][min_quantity]"
                                           value="<?php echo esc_attr( $product->get_min_quantity() ); ?>"
                                           class="cl-qty-input">
                                </td>
                                <td class="cl-col-max-qty">
                                    <input type="number" min="0"
                                           name="products[<?php echo esc_attr( $post->ID ); ?>][max_quantity]"
                                           value="<?php echo esc_attr( $product->get_max_quantity() ); ?>"
                                           class="cl-qty-input"
                                           placeholder="0 = ללא הגבלה">
                                </td>
                                <td class="cl-col-variants">
                                    <?php if ( $variants_count > 0 ) : ?>
                                        <span class="cl-variants-badge"><?php echo esc_html( $variants_count ); ?></span>
                                        <a href="<?php echo esc_url( $edit_url . '#cl-variants-section' ); ?>" target="_blank" class="cl-edit-variants">
                                            <?php esc_html_e( 'ערוך', 'commerce-layer' ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="cl-no-variants">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cl-col-actions">
                                    <button type="button" class="button cl-save-row" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                        <?php esc_html_e( 'שמור', 'commerce-layer' ); ?>
                                    </button>
                                    <span class="cl-row-status"></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <!-- Pagination -->
        <?php if ( $pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf( esc_html__( '%s פריטים', 'commerce-layer' ), number_format_i18n( $total ) ); ?>
                    </span>
                    <span class="pagination-links">
                        <?php
                        $base_url = admin_url( 'admin.php?page=cl-products' );
                        if ( $current_post_type ) {
                            $base_url = add_query_arg( 'post_type_filter', $current_post_type, $base_url );
                        }
                        if ( $search ) {
                            $base_url = add_query_arg( 's', $search, $base_url );
                        }

                        // First page
                        if ( $current_page > 1 ) : ?>
                            <a class="first-page button" href="<?php echo esc_url( add_query_arg( 'paged', 1, $base_url ) ); ?>">
                                <span class="screen-reader-text"><?php esc_html_e( 'עמוד ראשון', 'commerce-layer' ); ?></span>
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                            <a class="prev-page button" href="<?php echo esc_url( add_query_arg( 'paged', $current_page - 1, $base_url ) ); ?>">
                                <span class="screen-reader-text"><?php esc_html_e( 'עמוד קודם', 'commerce-layer' ); ?></span>
                                <span aria-hidden="true">&lsaquo;</span>
                            </a>
                        <?php else : ?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                        <?php endif; ?>

                        <span class="paging-input">
                            <span class="tablenav-paging-text">
                                <?php echo esc_html( $current_page ); ?> <?php esc_html_e( 'מתוך', 'commerce-layer' ); ?> <span class="total-pages"><?php echo esc_html( $pages ); ?></span>
                            </span>
                        </span>

                        <?php if ( $current_page < $pages ) : ?>
                            <a class="next-page button" href="<?php echo esc_url( add_query_arg( 'paged', $current_page + 1, $base_url ) ); ?>">
                                <span class="screen-reader-text"><?php esc_html_e( 'עמוד הבא', 'commerce-layer' ); ?></span>
                                <span aria-hidden="true">&rsaquo;</span>
                            </a>
                            <a class="last-page button" href="<?php echo esc_url( add_query_arg( 'paged', $pages, $base_url ) ); ?>">
                                <span class="screen-reader-text"><?php esc_html_e( 'עמוד אחרון', 'commerce-layer' ); ?></span>
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        <?php else : ?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<style>
.cl-products-list-wrap {
    margin-top: 20px;
}

.cl-products-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 15px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.cl-products-filters form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.cl-products-filters select,
.cl-products-filters input[type="search"] {
    min-width: 150px;
}

.cl-products-table {
    background: #fff;
}

.cl-products-table th {
    font-weight: 600;
    white-space: nowrap;
}

.cl-col-thumb {
    width: 60px;
}

.cl-col-title {
    width: 200px;
}

.cl-col-type {
    width: 100px;
}

.cl-col-enabled {
    width: 70px;
    text-align: center;
}

.cl-col-price,
.cl-col-sale-price {
    width: 100px;
}

.cl-col-mode {
    width: 120px;
}

.cl-col-min-qty,
.cl-col-max-qty {
    width: 90px;
}

.cl-col-variants {
    width: 90px;
    text-align: center;
}

.cl-col-actions {
    width: 120px;
}

.cl-product-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.cl-no-thumb {
    display: block;
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    background: #f0f0f1;
    border-radius: 4px;
    color: #999;
}

.cl-price-input,
.cl-qty-input {
    width: 80px !important;
}

.cl-mode-select {
    width: 100% !important;
}

.cl-post-type-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #e5e5e5;
    border-radius: 3px;
    font-size: 11px;
}

.cl-variants-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #2563eb;
    color: #fff;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
}

.cl-edit-variants {
    font-size: 11px;
}

.cl-no-variants {
    color: #999;
}

/* Toggle Switch */
.cl-toggle {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
}

.cl-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.cl-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 22px;
}

.cl-toggle-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

.cl-toggle input:checked + .cl-toggle-slider {
    background-color: #2563eb;
}

.cl-toggle input:checked + .cl-toggle-slider:before {
    transform: translateX(18px);
}

/* Row status */
.cl-row-status {
    display: inline-block;
    margin-right: 5px;
    font-size: 12px;
}

.cl-row-status.saving {
    color: #999;
}

.cl-row-status.saved {
    color: #10b981;
}

.cl-row-status.error {
    color: #ef4444;
}

.cl-product-row.changed {
    background-color: #fffbeb !important;
}

.cl-product-row input:disabled,
.cl-product-row select:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Track changes
    $('.cl-product-row input, .cl-product-row select').on('change', function() {
        $(this).closest('.cl-product-row').addClass('changed');
    });

    // Save single row
    $('.cl-save-row').on('click', function() {
        var $btn = $(this);
        var $row = $btn.closest('.cl-product-row');
        var postId = $row.data('post-id');
        var $status = $row.find('.cl-row-status');

        // Get row data
        var data = {
            action: 'cl_save_product_row',
            nonce: clAdmin.nonce,
            post_id: postId,
            enabled: $row.find('input[name*="[enabled]"]').is(':checked') ? 'yes' : 'no',
            price: $row.find('input[name*="[price]"]').val(),
            sale_price: $row.find('input[name*="[sale_price]"]').val(),
            purchase_mode: $row.find('select[name*="[purchase_mode]"]').val(),
            min_quantity: $row.find('input[name*="[min_quantity]"]').val(),
            max_quantity: $row.find('input[name*="[max_quantity]"]').val()
        };

        $btn.prop('disabled', true);
        $status.removeClass('saved error').addClass('saving').text('<?php esc_html_e( 'שומר...', 'commerce-layer' ); ?>');

        $.post(clAdmin.ajaxUrl, data, function(response) {
            $btn.prop('disabled', false);
            $status.removeClass('saving');

            if (response.success) {
                $status.addClass('saved').text('<?php esc_html_e( 'נשמר', 'commerce-layer' ); ?>');
                $row.removeClass('changed');
                setTimeout(function() {
                    $status.text('');
                }, 2000);
            } else {
                $status.addClass('error').text(response.data.message || '<?php esc_html_e( 'שגיאה', 'commerce-layer' ); ?>');
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $status.removeClass('saving').addClass('error').text('<?php esc_html_e( 'שגיאה', 'commerce-layer' ); ?>');
        });
    });

    // Save all products
    $('#cl-save-all-products').on('click', function() {
        var $btn = $(this);
        var $changedRows = $('.cl-product-row.changed');

        if ($changedRows.length === 0) {
            alert('<?php esc_html_e( 'אין שינויים לשמירה', 'commerce-layer' ); ?>');
            return;
        }

        var products = [];
        $changedRows.each(function() {
            var $row = $(this);
            products.push({
                post_id: $row.data('post-id'),
                enabled: $row.find('input[name*="[enabled]"]').is(':checked') ? 'yes' : 'no',
                price: $row.find('input[name*="[price]"]').val(),
                sale_price: $row.find('input[name*="[sale_price]"]').val(),
                purchase_mode: $row.find('select[name*="[purchase_mode]"]').val(),
                min_quantity: $row.find('input[name*="[min_quantity]"]').val(),
                max_quantity: $row.find('input[name*="[max_quantity]"]').val()
            });
        });

        $btn.prop('disabled', true).text('<?php esc_html_e( 'שומר...', 'commerce-layer' ); ?>');

        $.post(clAdmin.ajaxUrl, {
            action: 'cl_bulk_save_products',
            nonce: clAdmin.nonce,
            products: products
        }, function(response) {
            $btn.prop('disabled', false).text('<?php esc_html_e( 'שמור הכל', 'commerce-layer' ); ?>');

            if (response.success) {
                $changedRows.removeClass('changed');
                $changedRows.find('.cl-row-status').addClass('saved').text('<?php esc_html_e( 'נשמר', 'commerce-layer' ); ?>');
                setTimeout(function() {
                    $('.cl-row-status').text('');
                }, 2000);
                alert(response.data.message);
            } else {
                alert(response.data.message || '<?php esc_html_e( 'שגיאה בשמירה', 'commerce-layer' ); ?>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('<?php esc_html_e( 'שמור הכל', 'commerce-layer' ); ?>');
            alert('<?php esc_html_e( 'שגיאה בשמירה', 'commerce-layer' ); ?>');
        });
    });

    // Auto-filter on post type change
    $('#cl-post-type-filter').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
