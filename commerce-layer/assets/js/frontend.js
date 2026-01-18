/**
 * Frontend JavaScript
 * Commerce Layer Frontend Scripts
 */

(function($) {
    'use strict';

    // Initialize
    $(document).ready(function() {
        console.log('[Commerce Layer] Initializing frontend...');
        initQuantityButtons();
        initVariantSelection();
        initAddToCart();
        initBuyNow();
        initCartPage();
        initFloatingBar();
        initSideCart();
        initUrgencyTimer();
        console.log('[Commerce Layer] Frontend initialized. Side cart element:', document.getElementById('cl-side-cart'));
    });

    /**
     * Quantity buttons (+/-)
     */
    function initQuantityButtons() {
        $(document).on('click', '.cl-qty-minus', function(e) {
            e.preventDefault();
            var $input = $(this).siblings('.cl-quantity, .cl-cart-quantity');
            var val = parseInt($input.val()) || 1;
            var min = parseInt($input.attr('min')) || 1;

            if (val > min) {
                $input.val(val - 1).trigger('change');
            }
        });

        $(document).on('click', '.cl-qty-plus', function(e) {
            e.preventDefault();
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
        var $variantsData = $form.find('.cl-variants-data');
        if (!$variantsData.length) return;

        var variantsData = $variantsData.text();
        if (!variantsData) return;

        try {
            var variants = JSON.parse(variantsData);
        } catch (e) {
            return;
        }

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
        return clFrontend.currency + price;
    }

    /**
     * Get post ID from element context
     */
    function getPostId($element) {
        // Try to get from element itself first (for standalone buttons)
        if ($element.data('post-id')) {
            return $element.data('post-id');
        }

        // Try to get from shortcode wrapper
        var $wrap = $element.closest('.cl-shortcode-btn-wrap');
        if ($wrap.length && $wrap.data('post-id')) {
            return $wrap.data('post-id');
        }

        // Try to get from parent form
        var $form = $element.closest('.cl-purchase-form');
        if ($form.length && $form.data('post-id')) {
            return $form.data('post-id');
        }

        // Try to get from parent card
        var $card = $element.closest('.cl-purchase-card');
        if ($card.length && $card.data('post-id')) {
            return $card.data('post-id');
        }

        // Try to get from floating bar
        var $floatingBar = $element.closest('.cl-floating-bar');
        if ($floatingBar.length && $floatingBar.data('post-id')) {
            return $floatingBar.data('post-id');
        }

        return 0;
    }

    /**
     * Get form context for element
     */
    function getFormContext($element) {
        var $form = $element.closest('.cl-purchase-form');
        if ($form.length) {
            return $form;
        }

        var $card = $element.closest('.cl-purchase-card');
        if ($card.length) {
            return $card.find('.cl-purchase-form');
        }

        return null;
    }

    /**
     * Add to Cart
     */
    function initAddToCart() {
        $(document).on('click', '.cl-add-to-cart-btn, .cl-floating-add-to-cart', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var postId = getPostId($btn);
            var $form = getFormContext($btn);

            // If from floating bar, find main form
            if ($btn.hasClass('cl-floating-add-to-cart') && postId) {
                var $mainForm = $('.cl-purchase-form[data-post-id="' + postId + '"]');
                if ($mainForm.length) {
                    $form = $mainForm;
                }
            }

            // Check if we have a valid post ID
            if (!postId) {
                showNotice(clFrontend.strings.error, 'error');
                return;
            }

            // Check variant selection if applicable
            var variantId = 0;
            if ($form && $form.length) {
                var $variantId = $form.find('.cl-variant-id');
                if ($variantId.length) {
                    var hasVariants = $form.find('.cl-variant-dropdown').length > 0;
                    if (hasVariants && !$variantId.val()) {
                        showNotice(clFrontend.strings.selectVariant, 'error');
                        return;
                    }
                    variantId = $variantId.val() || 0;
                }
            }

            // Get quantity
            var quantity = 1;
            if ($form && $form.length) {
                quantity = $form.find('.cl-quantity').val() || 1;
            }

            var data = {
                action: 'cl_add_to_cart',
                nonce: clFrontend.nonce,
                post_id: postId,
                quantity: quantity,
                variant_id: variantId
            };

            $btn.prop('disabled', true).addClass('cl-loading');

            console.log('[Commerce Layer] Add to cart request:', data);

            $.post(clFrontend.ajaxUrl, data, function(response) {
                console.log('[Commerce Layer] Add to cart response:', response);
                if (response.success) {
                    // Update cart count
                    updateCartCount(response.data.items_count);

                    // Show success notification
                    showNotice(response.data.message, 'success');

                    // Update and open side cart
                    console.log('[Commerce Layer] Updating and opening side cart...');
                    updateSideCart(function() {
                        console.log('[Commerce Layer] Side cart updated, now opening...');
                        openSideCart();
                    });
                } else {
                    showNotice(response.data.message || clFrontend.strings.error, 'error');
                }
            }).fail(function(xhr, status, error) {
                console.error('[Commerce Layer] Add to cart failed:', status, error);
                showNotice(clFrontend.strings.error, 'error');
            }).always(function() {
                $btn.prop('disabled', false).removeClass('cl-loading');
            });
        });
    }

    function showNotice(message, type) {
        // Remove existing notices
        $('.cl-toast-notice').remove();

        var $notice = $('<div class="cl-toast-notice cl-toast-' + type + '">' + message + '</div>');
        $('body').append($notice);

        // Animate in
        setTimeout(function() {
            $notice.addClass('cl-toast-visible');
        }, 10);

        // Remove after delay
        setTimeout(function() {
            $notice.removeClass('cl-toast-visible');
            setTimeout(function() {
                $notice.remove();
            }, 300);
        }, 3000);
    }

    function updateCartCount(count) {
        $('.cl-cart-count').text(count);
        $('.cl-side-cart-count').text('(' + count + ')');
    }

    /**
     * Buy Now
     */
    function initBuyNow() {
        $(document).on('click', '.cl-buy-now-btn, .cl-floating-buy-now', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var postId = getPostId($btn);
            var $form = getFormContext($btn);

            // Check if we have a valid post ID
            if (!postId) {
                showNotice(clFrontend.strings.error, 'error');
                return;
            }

            // Check variant selection if applicable
            var variantId = 0;
            if ($form && $form.length) {
                var $variantId = $form.find('.cl-variant-id');
                if ($variantId.length) {
                    var hasVariants = $form.find('.cl-variant-dropdown').length > 0;
                    if (hasVariants && !$variantId.val()) {
                        showNotice(clFrontend.strings.selectVariant, 'error');
                        return;
                    }
                    variantId = $variantId.val() || 0;
                }
            }

            // Get quantity
            var quantity = 1;
            if ($form && $form.length) {
                quantity = $form.find('.cl-quantity').val() || 1;
            }

            var data = {
                action: 'cl_add_to_cart',
                nonce: clFrontend.nonce,
                post_id: postId,
                quantity: quantity,
                variant_id: variantId
            };

            $btn.prop('disabled', true).addClass('cl-loading');

            $.post(clFrontend.ajaxUrl, data, function(response) {
                if (response.success) {
                    // Redirect to checkout
                    window.location.href = clFrontend.checkoutUrl;
                } else {
                    showNotice(response.data.message || clFrontend.strings.error, 'error');
                    $btn.prop('disabled', false).removeClass('cl-loading');
                }
            }).fail(function() {
                showNotice(clFrontend.strings.error, 'error');
                $btn.prop('disabled', false).removeClass('cl-loading');
            });
        });
    }

    /**
     * Side Cart
     */
    function initSideCart() {
        console.log('[Commerce Layer] initSideCart called');
        var $sideCart = $('#cl-side-cart');
        console.log('[Commerce Layer] Side cart element exists on init:', $sideCart.length > 0);

        // Close button
        $(document).on('click', '.cl-side-cart-close, .cl-side-cart-overlay', function(e) {
            e.preventDefault();
            closeSideCart();
        });

        // ESC key to close
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#cl-side-cart').hasClass('cl-side-cart-open')) {
                closeSideCart();
            }
        });

        // Quantity change in side cart
        $(document).on('change', '.cl-side-cart-item .cl-cart-quantity', function() {
            var $input = $(this);
            var cartKey = $input.data('cart-key');
            var quantity = parseInt($input.val()) || 1;

            updateSideCartItem(cartKey, quantity);
        });

        // Remove item from side cart
        $(document).on('click', '.cl-side-cart-remove', function(e) {
            e.preventDefault();
            var cartKey = $(this).data('cart-key');
            removeSideCartItem(cartKey);
        });

        // Open cart icon click
        $(document).on('click', '.cl-mini-cart-link, .cl-open-side-cart', function(e) {
            e.preventDefault();
            openSideCart();
        });
    }

    function openSideCart() {
        var $sideCart = $('#cl-side-cart');
        console.log('[Commerce Layer] openSideCart called, element found:', $sideCart.length > 0);
        if ($sideCart.length) {
            $sideCart.addClass('cl-side-cart-open');
            $('body').css('overflow', 'hidden');
            console.log('[Commerce Layer] Side cart classes:', $sideCart.attr('class'));
        } else {
            console.error('[Commerce Layer] Side cart element #cl-side-cart not found!');
        }
    }

    function closeSideCart() {
        $('#cl-side-cart').removeClass('cl-side-cart-open');
        $('body').css('overflow', '');
    }

    function updateSideCart(callback) {
        console.log('[Commerce Layer] updateSideCart called');
        $.post(clFrontend.ajaxUrl, {
            action: 'cl_get_side_cart',
            nonce: clFrontend.nonce
        }, function(response) {
            console.log('[Commerce Layer] Side cart AJAX response:', response);
            if (response.success) {
                // Replace side cart HTML
                var $newCart = $(response.data.html);
                $('#cl-side-cart').replaceWith($newCart);
                console.log('[Commerce Layer] Side cart HTML replaced');

                // Update cart count
                updateCartCount(response.data.items_count);

                if (typeof callback === 'function') {
                    callback();
                }
            } else {
                console.error('[Commerce Layer] Side cart AJAX failed:', response);
            }
        }).fail(function(xhr, status, error) {
            console.error('[Commerce Layer] Side cart AJAX error:', status, error);
        });
    }

    function updateSideCartItem(cartKey, quantity) {
        $.post(clFrontend.ajaxUrl, {
            action: 'cl_update_cart',
            nonce: clFrontend.nonce,
            cart_key: cartKey,
            quantity: quantity
        }, function(response) {
            if (response.success) {
                // Update totals
                $('.cl-subtotal-amount').text(response.data.formatted.total);
                updateCartCount(response.data.items_count);

                // Update shipping progress if exists
                updateShippingProgress(response.data.totals.subtotal);

                if (response.data.is_empty) {
                    updateSideCart();
                }
            }
        });
    }

    function removeSideCartItem(cartKey) {
        var $item = $('.cl-side-cart-item[data-cart-key="' + cartKey + '"]');

        $item.css({
            opacity: 0,
            transform: 'translateX(-100%)'
        });

        setTimeout(function() {
            $.post(clFrontend.ajaxUrl, {
                action: 'cl_remove_from_cart',
                nonce: clFrontend.nonce,
                cart_key: cartKey
            }, function(response) {
                if (response.success) {
                    $item.slideUp(200, function() {
                        $(this).remove();
                    });

                    // Update totals
                    $('.cl-subtotal-amount').text(response.data.formatted.total);
                    updateCartCount(response.data.items_count);

                    // Update shipping progress
                    updateShippingProgress(response.data.totals.subtotal);

                    if (response.data.is_empty) {
                        updateSideCart();
                    }
                }
            });
        }, 200);
    }

    function updateShippingProgress(subtotal) {
        var threshold = parseFloat(clFrontend.freeShippingThreshold) || 0;
        if (threshold <= 0) return;

        var progress = Math.min(100, (subtotal / threshold) * 100);
        var remaining = Math.max(0, threshold - subtotal);

        $('.cl-shipping-bar-fill').css('width', progress + '%');

        if (remaining > 0) {
            $('.cl-shipping-text').html(
                clFrontend.strings.addMore.replace('%s', '<strong>' + formatPrice(remaining) + '</strong>')
            );
            $('.cl-shipping-text').removeClass('cl-shipping-free');
        } else {
            $('.cl-shipping-text').html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> ' + clFrontend.strings.freeShipping);
            $('.cl-shipping-text').addClass('cl-shipping-free');
        }
    }

    /**
     * Cart Page
     */
    function initCartPage() {
        // Update quantity
        $(document).on('change', '.cl-cart-table .cl-cart-quantity', function() {
            var $input = $(this);
            var cartKey = $input.data('cart-key');
            var quantity = parseInt($input.val()) || 1;

            updateCartPageItem(cartKey, quantity);
        });

        // Remove item
        $(document).on('click', '.cl-cart-table .cl-remove-item', function(e) {
            e.preventDefault();
            var cartKey = $(this).data('cart-key');
            removeCartPageItem(cartKey);
        });
    }

    function updateCartPageItem(cartKey, quantity) {
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
                var price = parseFloat($row.data('price')) || 0;
                var lineTotal = formatPrice(price * quantity);
                $row.find('.cl-line-total').text(lineTotal);

                updateCartCount(response.data.items_count);

                if (response.data.is_empty) {
                    location.reload();
                }
            }
        });
    }

    function removeCartPageItem(cartKey) {
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

    /**
     * Urgency Timer
     */
    function initUrgencyTimer() {
        var $timer = $('.cl-urgency-timer');
        if (!$timer.length) return;

        var minutes = parseInt($timer.data('minutes')) || 15;
        var seconds = minutes * 60;

        function updateTimer() {
            var mins = Math.floor(seconds / 60);
            var secs = seconds % 60;
            $timer.text(pad(mins) + ':' + pad(secs));

            if (seconds > 0) {
                seconds--;
                setTimeout(updateTimer, 1000);
            }
        }

        function pad(num) {
            return num < 10 ? '0' + num : num;
        }

        updateTimer();
    }

})(jQuery);
