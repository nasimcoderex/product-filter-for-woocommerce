jQuery(document).ready(function($) {
    $('#pfw_add_offer_product_checkbox').on('change', function() {
        var productId = $(this).val();
        var isChecked = $(this).is(':checked');

        // For this basic version, we only handle adding the product.
        // A more advanced version would handle removal if unchecked, or prevent unchecking.
        if (!isChecked) {
            // Optional: Add logic here if you want to handle unchecking (e.g., remove product via AJAX)
            // For now, we do nothing if unchecked, or disable unchecking.
            // $(this).prop('disabled', true); // Example: disable after checking once
            console.log('Product offer unchecking not handled in this version.');
            return;
        }

        // Prevent multiple additions if user clicks rapidly or re-checks
        if ($(this).data('adding') === true || $(this).data('added') === true) {
            return;
        }
        $(this).data('adding', true);


        $.ajax({
            url: pfw_offer_params.ajax_url,
            type: 'POST',
            data: {
                action: 'pfw_add_offer_to_cart',
                nonce: pfw_offer_params.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    // Trigger WooCommerce to update the cart and checkout totals
                    $(document.body).trigger('update_checkout');
                    // Optionally, provide feedback to the user
                    $('#pfw_offer_feedback').html('<p style="color:green;">Offer product added successfully!</p>').show();
                    // Prevent further interaction with the checkbox if desired
                    $('#pfw_add_offer_product_checkbox').prop('disabled', true).data('added', true);
                } else {
                    $('#pfw_offer_feedback').html('<p style="color:red;">Error: ' + (response.data ? response.data.message : 'Could not add offer product.') + '</p>').show();
                    $('#pfw_add_offer_product_checkbox').prop('checked', false); // Revert check
                }
            },
            error: function(errorThrown) {
                $('#pfw_offer_feedback').html('<p style="color:red;">AJAX error. Please try again.</p>').show();
                $('#pfw_add_offer_product_checkbox').prop('checked', false); // Revert check
                console.error('PFW Offer AJAX Error:', errorThrown);
            },
            complete: function() {
                $('#pfw_add_offer_product_checkbox').data('adding', false);
            }
        });
    });
});
