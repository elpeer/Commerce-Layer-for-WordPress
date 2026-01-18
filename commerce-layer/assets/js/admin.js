/**
 * Admin JavaScript
 * Commerce Layer Admin Scripts
 */

(function($) {
    'use strict';

    // Delete attribute
    $(document).on('click', '.cl-delete-attribute', function(e) {
        e.preventDefault();

        if (!confirm(clAdmin.strings.confirmDelete)) {
            return;
        }

        var $btn = $(this);
        var attrId = $btn.data('id');

        $.post(clAdmin.ajaxUrl, {
            action: 'cl_delete_attribute',
            nonce: clAdmin.nonce,
            attribute_id: attrId
        }, function(response) {
            if (response.success) {
                $btn.closest('tr').fadeOut(function() {
                    $(this).remove();
                });
            } else {
                alert(response.data.message || clAdmin.strings.error);
            }
        });
    });

})(jQuery);
