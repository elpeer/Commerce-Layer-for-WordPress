<?php
/**
 * Attributes View
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$attributes_table = $wpdb->prefix . 'cl_attributes';
$values_table = $wpdb->prefix . 'cl_attribute_values';

$attributes = $wpdb->get_results( "SELECT * FROM $attributes_table ORDER BY sort_order ASC", ARRAY_A );
foreach ( $attributes as &$attr ) {
    $attr['values'] = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM $values_table WHERE attribute_id = %d ORDER BY sort_order ASC", $attr['id'] ),
        ARRAY_A
    );
}
?>
<div class="wrap cl-attributes-page">
    <h1>
        <?php esc_html_e( 'מאפיינים', 'commerce-layer' ); ?>
        <button type="button" class="page-title-action cl-add-attribute"><?php esc_html_e( 'הוסף מאפיין', 'commerce-layer' ); ?></button>
    </h1>

    <p class="description">
        <?php esc_html_e( 'מאפיינים משמשים ליצירת וריאציות למוצרים. לדוגמה: צבע, מידה, חומר.', 'commerce-layer' ); ?>
    </p>

    <div class="cl-attributes-grid">
        <!-- Attributes List -->
        <div class="cl-attributes-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'שם מאפיין', 'commerce-layer' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'commerce-layer' ); ?></th>
                        <th><?php esc_html_e( 'ערכים', 'commerce-layer' ); ?></th>
                        <th><?php esc_html_e( 'פעולות', 'commerce-layer' ); ?></th>
                    </tr>
                </thead>
                <tbody id="cl-attributes-list">
                    <?php if ( empty( $attributes ) ) : ?>
                        <tr class="cl-no-attributes-row">
                            <td colspan="4"><?php esc_html_e( 'לא נמצאו מאפיינים', 'commerce-layer' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $attributes as $attr ) : ?>
                            <tr data-id="<?php echo esc_attr( $attr['id'] ); ?>">
                                <td><strong><?php echo esc_html( $attr['name'] ); ?></strong></td>
                                <td><code><?php echo esc_html( $attr['slug'] ); ?></code></td>
                                <td>
                                    <?php
                                    $values = wp_list_pluck( $attr['values'], 'value' );
                                    echo esc_html( implode( ', ', $values ) );
                                    ?>
                                </td>
                                <td>
                                    <button type="button" class="button button-small cl-edit-attribute"
                                            data-id="<?php echo esc_attr( $attr['id'] ); ?>"
                                            data-name="<?php echo esc_attr( $attr['name'] ); ?>"
                                            data-slug="<?php echo esc_attr( $attr['slug'] ); ?>"
                                            data-values='<?php echo esc_attr( json_encode( $values ) ); ?>'>
                                        <?php esc_html_e( 'ערוך', 'commerce-layer' ); ?>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete cl-delete-attribute"
                                            data-id="<?php echo esc_attr( $attr['id'] ); ?>">
                                        <?php esc_html_e( 'מחק', 'commerce-layer' ); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Form -->
        <div class="cl-attribute-form-wrap">
            <div class="cl-attribute-form">
                <h3 id="cl-form-title"><?php esc_html_e( 'הוסף מאפיין חדש', 'commerce-layer' ); ?></h3>

                <form id="cl-attribute-form">
                    <input type="hidden" name="attribute_id" id="cl-attr-id" value="">

                    <div class="cl-form-field">
                        <label for="cl-attr-name"><?php esc_html_e( 'שם מאפיין', 'commerce-layer' ); ?></label>
                        <input type="text" name="name" id="cl-attr-name" class="regular-text" required>
                        <p class="description"><?php esc_html_e( 'לדוגמה: צבע, מידה', 'commerce-layer' ); ?></p>
                    </div>

                    <div class="cl-form-field">
                        <label for="cl-attr-slug"><?php esc_html_e( 'Slug', 'commerce-layer' ); ?></label>
                        <input type="text" name="slug" id="cl-attr-slug" class="regular-text" dir="ltr">
                        <p class="description"><?php esc_html_e( 'מזהה ייחודי באנגלית. יווצר אוטומטית אם ריק.', 'commerce-layer' ); ?></p>
                    </div>

                    <div class="cl-form-field">
                        <label><?php esc_html_e( 'ערכים', 'commerce-layer' ); ?></label>
                        <div id="cl-values-list" class="cl-values-list">
                            <!-- Values will be added here -->
                        </div>
                        <button type="button" class="button cl-add-value"><?php esc_html_e( '+ הוסף ערך', 'commerce-layer' ); ?></button>
                        <p class="description"><?php esc_html_e( 'לדוגמה: אדום, כחול, ירוק או S, M, L, XL', 'commerce-layer' ); ?></p>
                    </div>

                    <div class="cl-form-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'שמור', 'commerce-layer' ); ?></button>
                        <button type="button" class="button cl-cancel-edit" style="display:none;"><?php esc_html_e( 'ביטול', 'commerce-layer' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.cl-attributes-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px; }
.cl-attribute-form { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; }
.cl-form-field { margin-bottom: 20px; }
.cl-form-field label { display: block; margin-bottom: 5px; font-weight: 600; }
.cl-values-list { margin-bottom: 10px; }
.cl-value-row { display: flex; gap: 10px; margin-bottom: 8px; }
.cl-value-row input { flex: 1; }
.cl-form-actions { padding-top: 15px; border-top: 1px solid #ddd; }
.cl-form-actions .button { margin-left: 10px; }

@media (max-width: 960px) {
    .cl-attributes-grid { grid-template-columns: 1fr; }
}
</style>

<script>
jQuery(document).ready(function($) {
    var $form = $('#cl-attribute-form');
    var $valuesList = $('#cl-values-list');

    // Add value row
    function addValueRow(value) {
        var $row = $('<div class="cl-value-row">' +
            '<input type="text" name="values[]" value="' + (value || '') + '" class="regular-text">' +
            '<button type="button" class="button cl-remove-value">&times;</button>' +
            '</div>');
        $valuesList.append($row);
    }

    // Initialize with one empty row
    addValueRow('');

    // Add value button
    $('.cl-add-value').on('click', function() {
        addValueRow('');
    });

    // Remove value
    $(document).on('click', '.cl-remove-value', function() {
        $(this).closest('.cl-value-row').remove();
        if ($valuesList.children().length === 0) {
            addValueRow('');
        }
    });

    // Add new attribute button
    $('.cl-add-attribute').on('click', function() {
        resetForm();
    });

    // Edit attribute
    $(document).on('click', '.cl-edit-attribute', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var slug = $(this).data('slug');
        var values = $(this).data('values');

        $('#cl-attr-id').val(id);
        $('#cl-attr-name').val(name);
        $('#cl-attr-slug').val(slug);

        $valuesList.empty();
        if (values && values.length) {
            values.forEach(function(v) {
                addValueRow(v);
            });
        } else {
            addValueRow('');
        }

        $('#cl-form-title').text('<?php esc_html_e( 'ערוך מאפיין', 'commerce-layer' ); ?>');
        $('.cl-cancel-edit').show();
    });

    // Cancel edit
    $('.cl-cancel-edit').on('click', function() {
        resetForm();
    });

    function resetForm() {
        $form[0].reset();
        $('#cl-attr-id').val('');
        $valuesList.empty();
        addValueRow('');
        $('#cl-form-title').text('<?php esc_html_e( 'הוסף מאפיין חדש', 'commerce-layer' ); ?>');
        $('.cl-cancel-edit').hide();
    }

    // Delete attribute
    $(document).on('click', '.cl-delete-attribute', function() {
        if (!confirm('<?php esc_html_e( 'האם למחוק מאפיין זה?', 'commerce-layer' ); ?>')) {
            return;
        }

        var $row = $(this).closest('tr');
        var id = $(this).data('id');

        $.post(ajaxurl, {
            action: 'cl_delete_attribute',
            nonce: clAdmin.nonce,
            attribute_id: id
        }, function(response) {
            if (response.success) {
                $row.fadeOut(function() { $(this).remove(); });
            } else {
                alert(response.data.message || clAdmin.strings.error);
            }
        });
    });

    // Save attribute
    $form.on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=cl_save_attribute&nonce=' + clAdmin.nonce;

        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || clAdmin.strings.error);
            }
        });
    });
});
</script>
