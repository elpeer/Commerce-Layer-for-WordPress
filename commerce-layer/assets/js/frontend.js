/**
 * Frontend JavaScript
 * Commerce Layer Frontend Scripts
 */

(function($) {
    'use strict';

    // Initialize
    $(document).ready(function() {
        initQuantityButtons();
        initVariantSelection();
        initAddToCart();
        initBuyNow();
        initCartPage();
        initFloatingBar();
    });

    /**
     * Quantity buttons (+/-)
     */
    function initQuantityButtons() {
        $(document).on('click', '.cl-qty-minus', function() {
            var $input = $(this).siblings('.cl-quantity, .cl-cart-quantity');
            var val = parseInt($input.val()) || 1;
            var min = parseInt($input.attr('min')) || 1;

            if (val > min) {
                $input.val(val - 1).trigger('change');
            }
        });

        $(document).on('click', '.cl-qty-plus', function() {
            var $input = $(this).siblings('.cl-quantity, .cl-cart-quantity');
            var val = parseInt($input.val()) || 1;
            var max = parseInt($input.attr('max')) || 999;

            if (val < max) {
                $input.val(val + 1).trigger('change');
            }
        });
    }

    /**
     * Variant selection
     */
    function initVariantSelection() {
        $(document).on('change', '.cl-variant-dropdown', function() {
            var $form = $(this).closest('.cl-purchase-form');
            updateSelectedVariant($form);
        });
    }

    function updateSelectedVariant($form) {
        var variantsData = $form.find('.cl-variants-data').text();
        if (!variantsData) return;

        var variants = JSON.parse(variantsData);
        var selectedAttrs = {};

        $form.find('.cl-variant-dropdown').each(function() {
            var attr = $(this).closest('.cl-variant-select').data('attribute');
            var val = $(this).val();
            if (val) {
                selectedAttrs[attr] = val;
            }
        });

        // Find matching variant
        var matchingVariant = null;
        for (var i = 0; i < variants.length; i++) {
            var variant = variants[i];
            var matches = true;

            for (var attr in selectedAttrs) {
                if (variant.attributes[attr] !== selectedAttrs[attr]) {
                    matches = false;
                    break;
                }
            }

            if (matches && Object.keys(selectedAttrs).length === Object.keys(variant.attributes).length) {
                matchingVariant = variant;
                break;
            }
        }

        if (matchingVariant) {
            $form.find('.cl-variant-id').val(matchingVariant.id);

            // Update price display
            var $card = $form.closest('.cl-purchase-card');
            if ($card.length && matchingVariant.price) {
                var priceHtml = formatPrice(matchingVariant.price);
                if (matchingVariant.on_sale && matchingVariant.regular_price) {
                    priceHtml = '<del>' + formatPrice(matchingVariant.regular_price) + '</del> ' +
                               '<ins>' + formatPrice(matchingVariant.price) + '</ins>';
                }
                $card.find('.cl-price-section .cl-price').html(priceHtml);
            }

            // Update stock status
            if (!matchingVariant.in_stock) {
                $form.find('.cl-btn').prop('disabled', true);
            } else {
                $form.find('.cl-btn').prop('disabled', false);
            }
        } else {
            $form.find('.cl-variant-id').val('');
        }
    }

    function formatPrice(price) {
        price = parseFloat(price).toFixed(2);
        return price + clFrontend.currency;
    }

    /**
     * Add to Cart
     */
    function initAddToCart() {
        $(document).on('click', '.cl-add-to-cart-btn, .cl-floating-add-to-cart', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $form = $btn.closest('.cl-purchase-form, .cl-floating-bar');
            var postId = $form.data('post-id') || $form.find('[data-post-id]').data('post-id');

            // If from floating bar without variants, use main form
            if ($btn.hasClass('cl-floating-add-to-cart')) {
                var $mainForm = $('.cl-purchase-form[data-post-id="' + postId + '"]');
                if ($mainForm.length) {
                    $form = $mainForm;
                }
            }

            // Check variant selection
            var $variantId = $form.find('.cl-variant-id');
            if ($variantId.length && !$variantId.val()) {
                var hasVariants = $form.find('.cl-variant-dropdown').length > 0;
                if (hasVariants) {
                    alert(clFrontend.strings.selectVariant);
                    return;
                }
            }

            var data = {
                action: 'cl_add_to_cart',
                nonce: clFrontend.nonce,
                post_id: postId,
                quantity: $form.find('.cl-quantity').val() || 1,
                variant_id: $variantId.val() || 0
            };

            $btn.prop('disabled', true);

            $.post(clFrontend.ajaxUrl, data, function(response) {
                if (response.success) {
                    showAddedMessage($form);
                    updateCartCount(response.data.items_count);
                } else {
                    alert(response.data.message || clFrontend.strings.error);
                }
            }).fail(function() {
                alert(clFrontend.strings.error);
            }).always(function() {
                $btn.prop('disabled', false);
            });
        });
    }

    function showAddedMessage($form) {
        var $card = $form.closest('.cl-purchase-card');
        if ($card.length) {
            $card.find('.cl-added-message').slideDown();
        }
    }

    $(document).on('click', '.cl-continue-shopping', function() {
        $(this).closest('.cl-added-message').slideUp();
    });

    function updateCartCount(count) {
        $('.cl-cart-count').text(count);
    }

    /**
     * Buy Now
     */
    function initBuyNow() {
        $(document).on('click', '.cl-buy-now-btn, .cl-floating-buy-now', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $form = $btn.closest('.cl-purchase-form, .cl-floating-bar');
            var postId = $form.data('post-id') || $form.find('[data-post-id]').data('post-id');

            // Check variant selection
            var $variantId = $form.find('.cl-variant-id');
            if ($variantId.length && !$variantId.val()) {
                var hasVariants = $form.find('.cl-variant-dropdown').length > 0;
                if (hasVariants) {
                    alert(clFrontend.strings.selectVariant);
                    return;
                }
            }

            var data = {
                action: 'cl_add_to_cart',
                nonce: clFrontend.nonce,
                post_id: postId,
                quantity: $form.find('.cl-quantity').val() || 1,
                variant_id: $variantId.val() || 0
            };

            $btn.prop('disabled', true);

            $.post(clFrontend.ajaxUrl, data, function(response) {
                if (response.success) {
                    // Redirect to checkout
                    window.location.href = clFrontend.checkoutUrl;
                } else {
                    alert(response.data.message || clFrontend.strings.error);
                    $btn.prop('disabled', false);
                }
            }).fail(function() {
                alert(clFrontend.strings.error);
                $btn.prop('disabled', false);
            });
        });
    }

    /**
     * Cart Page
     */
    function initCartPage() {
        // Update quantity
        $(document).on('change', '.cl-cart-quantity', function() {
            var $input = $(this);
            var cartKey = $input.data('cart-key');
            var quantity = parseInt($input.val()) || 1;

            updateCartItem(cartKey, quantity);
        });

        // Remove item
        $(document).on('click', '.cl-remove-item', function() {
            var cartKey = $(this).data('cart-key');
            removeCartItem(cartKey);
        });
    }

    function updateCartItem(cartKey, quantity) {
        $.post(clFrontend.ajaxUrl, {
            action: 'cl_update_cart',
            nonce: clFrontend.nonce,
            cart_key: cartKey,
            quantity: quantity
        }, function(response) {
            if (response.success) {
                // Update totals
                $('.cl-subtotal-amount').text(response.data.formatted.subtotal);
                $('.cl-total-amount').text(response.data.formatted.total);

                // Update line total
                var $row = $('.cl-cart-item[data-cart-key="' + cartKey + '"]');
                var price = parseFloat($row.find('.cl-col-price').text().replace(/[^\d.]/g, ''));
                var lineTotal = (price * quantity).toFixed(2) + clFrontend.currency;
                $row.find('.cl-line-total').text(lineTotal);

                updateCartCount(response.data.items_count);

                if (response.data.is_empty) {
                    location.reload();
                }
            }
        });
    }

    function removeCartItem(cartKey) {
        $.post(clFrontend.ajaxUrl, {
            action: 'cl_remove_from_cart',
            nonce: clFrontend.nonce,
            cart_key: cartKey
        }, function(response) {
            if (response.success) {
                var $row = $('.cl-cart-item[data-cart-key="' + cartKey + '"]');
                $row.fadeOut(function() {
                    $(this).remove();
                });

                // Update totals
                $('.cl-subtotal-amount').text(response.data.formatted.subtotal);
                $('.cl-total-amount').text(response.data.formatted.total);

                updateCartCount(response.data.items_count);

                if (response.data.is_empty) {
                    location.reload();
                }
            }
        });
    }

    /**
     * Floating Bar
     */
    function initFloatingBar() {
        // Open variants modal
        $(document).on('click', '.cl-open-variants', function() {
            $(this).closest('.cl-floating-bar').find('.cl-variants-modal').show();
        });

        // Close modal
        $(document).on('click', '.cl-modal-close, .cl-modal-overlay', function() {
            $(this).closest('.cl-variants-modal').hide();
        });
    }

})(jQuery);
