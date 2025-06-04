// public/js/pfw-google-autocomplete.js
var pfwBillingAutocomplete, pfwShippingAutocomplete;

// Define the address components mapping
// See https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
var pfwComponentForm = {
    street_number: { selector: '_address_1', type: 'short_name', is_primary_address_part: true },
    route: { selector: '_address_1', type: 'long_name', is_primary_address_part: true },
    locality: { selector: '_city', type: 'long_name' },
    administrative_area_level_1: { selector: '_state', type: 'short_name' },
    country: { selector: '_country', type: 'short_name' },
    postal_code: { selector: '_postcode', type: 'short_name' }
};

function pfwInitAutocomplete() {
    const billingAddress1 = document.getElementById('billing_address_1');
    const shippingAddress1 = document.getElementById('shipping_address_1');

    if (billingAddress1) {
        pfwBillingAutocomplete = new google.maps.places.Autocomplete(
            billingAddress1,
            { types: ['address'] }
        );
        pfwBillingAutocomplete.addListener('place_changed', function() {
            pfwFillInAddress('billing', pfwBillingAutocomplete);
        });
    }

    // Also initialize for shipping fields if they are present and different from billing
    // This condition checks if the shipping address fields are visible (common WooCommerce behavior)
    const shippingAddressFields = document.querySelector('.woocommerce-shipping-fields');
    if (shippingAddress1 && shippingAddressFields && (shippingAddressFields.style.display !== 'none' || !document.getElementById('ship-to-different-address-checkbox')?.checked)) {
        pfwShippingAutocomplete = new google.maps.places.Autocomplete(
            shippingAddress1,
            { types: ['address'] }
        );
        pfwShippingAutocomplete.addListener('place_changed', function() {
            pfwFillInAddress('shipping', pfwShippingAutocomplete);
        });
    }
}

function pfwFillInAddress(prefix, autocompleteInstance) {
    const place = autocompleteInstance.getPlace();

    if (!place || !place.address_components) {
        // User entered the name of a Place that was not suggested and
        // pressed the Enter key, or the Place Details request failed.
        // Optionally, you could alert the user or log this.
        // window.alert("No details available for input: '" + place.name + "'");
        return;
    }

    // Clear existing address fields for this prefix
    for (const componentKey in pfwComponentForm) {
        const selectorSuffix = pfwComponentForm[componentKey].selector;
        const element = document.getElementById(prefix + selectorSuffix);
        if (element && !pfwComponentForm[componentKey].is_primary_address_part) { // Don't clear address_1 initially
             element.value = '';
        }
    }
    // Clear the primary address field (#billing_address_1 or #shipping_address_1)
    // as it will be reconstructed from components.
    const primaryAddressField = document.getElementById(prefix + '_address_1');
    if (primaryAddressField) {
        primaryAddressField.value = '';
    }


    let streetNumber = '';
    let route = '';

    // Get each component of the address from the place details,
    // and then fill-in the corresponding field on the form.
    for (const component of place.address_components) {
        const addressType = component.types[0];

        if (addressType === 'street_number') {
            streetNumber = component.long_name;
        } else if (addressType === 'route') {
            route = component.short_name; // Or long_name, depending on preference
        } else if (pfwComponentForm[addressType]) {
            const val = component[pfwComponentForm[addressType].type];
            const element = document.getElementById(prefix + pfwComponentForm[addressType].selector);
            if (element) {
                element.value = val;
                // Dispatch 'change' event for WooCommerce compatibility (e.g., state/country updates)
                var event = new Event('change', { bubbles: true });
                element.dispatchEvent(event);
            }
        }
    }

    // Combine street number and route for the main address line
    if (primaryAddressField) {
        let fullAddress = streetNumber;
        if (route) {
            fullAddress = (streetNumber ? streetNumber + " " : "") + route;
        }
        primaryAddressField.value = fullAddress.trim();
        var event = new Event('change', { bubbles: true });
        primaryAddressField.dispatchEvent(event);
    }

    // Trigger jQuery events for WooCommerce state/country updates and general checkout update
    if (typeof jQuery !== 'undefined') {
        // Ensures WooCommerce updates based on new country/state
        if (document.getElementById(prefix + '_country')) {
             jQuery('#' + prefix + '_country').trigger('change');
        }
        if (document.getElementById(prefix + '_state')) {
            // WooCommerce might need a slight delay or specific sequence for state updates after country
            setTimeout(function() {
                jQuery('#' + prefix + '_state').trigger('change');
                jQuery(document.body).trigger('update_checkout');
            }, 100);
        } else {
            jQuery(document.body).trigger('update_checkout');
        }
    }
}

/**
 * This global function will be called by the Google Maps API script
 * once it has loaded.
 */
function initPfwGooglePlacesApiCallback() {
    pfwInitAutocomplete();
}
