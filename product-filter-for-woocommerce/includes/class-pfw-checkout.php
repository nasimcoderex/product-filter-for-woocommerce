<?php
/**
 * PFW_Checkout Class
 *
 * Handles the checkout page override.
 *
 * @package ProductFilterForWooCommerce/Includes
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class PFW_Checkout.
 */
class PFW_Checkout {

    /**
     * Constructor.
     *
     * Hooks into WordPress to modify the checkout template and handle skip cart functionality.
     */
    public function __construct() {
        add_filter( 'template_include', array( $this, 'override_checkout_template' ) );

        if ( get_option( 'pfw_skip_cart_enabled', false ) ) {
            add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'skip_cart_redirect' ) );
        }

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_scripts' ) );
    }

    /**
     * Enqueue scripts for the checkout page.
     *
     * This includes Google Maps API for address autocomplete if enabled.
     */
    public function enqueue_checkout_scripts() {
        if ( function_exists('is_checkout') && is_checkout() && get_option( 'pfw_google_autocomplete_enabled', false ) ) {
            $api_key = get_option( 'pfw_google_maps_api_key' );
            if ( ! empty( $api_key ) ) {
                // Enqueue our local script that contains the callback function and autocomplete logic
                wp_enqueue_script(
                    'pfw-google-autocomplete',
                    PFW_PLUGIN_URL . 'public/js/pfw-google-autocomplete.js',
                    array( 'jquery' ), // Depends on jQuery
                    PFW_VERSION,
                    true // Load in footer
                );

                // Enqueue Google Maps API and specify our callback function
                // This script should be loaded after pfw-google-autocomplete.js or ensure the callback is globally available before this tries to call it.
                // WordPress script loader typically handles dependencies well if 'pfw-google-autocomplete' was a dep for 'google-maps-places',
                // but the callback= mechanism is a bit different. The global function initPfwGooglePlacesApiCallback must be defined when Google's script executes.
                // Loading our script first (without it being a dep of google maps) is safer.
                wp_enqueue_script(
                    'google-maps-places',
                    'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places&callback=initPfwGooglePlacesApiCallback',
                    array(), // No direct WordPress script dependencies for the Google Maps API itself.
                    null,    // No version number for external script.
                    true     // Load in footer.
                );
            }
        }
    }

    /**
     * Override checkout template.
     *
     * Checks if the checkout override is enabled and if the current page is the checkout page.
     * If so, it loads the custom checkout template.
     *
     * @param string $template The path to the template file.
     * @return string The path to the new template file if conditions are met, otherwise the original template.
     */
    public function override_checkout_template( $template ) {
        $override_enabled = get_option( 'pfw_checkout_override_enabled', false );
        $selected_template = get_option( 'pfw_checkout_template_select', 'default' );

        if ( $override_enabled && function_exists( 'is_checkout' ) && is_checkout() && $selected_template === 'custom_pfw' ) {
            $new_template_path = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/pfw-checkout-template.php';
            if ( file_exists( $new_template_path ) ) {
                return $new_template_path;
            }
        }
        return $template;
    }

    /**
     * Redirect to checkout after adding a product to the cart.
     *
     * @param string $url The original redirect URL.
     * @return string The new redirect URL (checkout page).
     */
    public function skip_cart_redirect( $url ) {
        // Ensure WooCommerce functions are available
        if ( function_exists( 'wc_get_checkout_url' ) ) {
            return wc_get_checkout_url();
        }
        return $url; // Fallback to original URL if WC function not found
    }
}

// Instantiate the class.
new PFW_Checkout();
