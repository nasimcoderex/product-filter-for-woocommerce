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
     * Hooks into WordPress to modify the checkout template.
     */
    public function __construct() {
        add_filter( 'template_include', array( $this, 'override_checkout_template' ) );
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
        $checkout_override_enabled = get_option( 'pfw_checkout_override_enabled', false );

        if ( $checkout_override_enabled ) {
            // Check if WooCommerce is defined and then if it's the checkout page.
            if ( function_exists( 'is_checkout' ) && is_checkout() ) {
                $new_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/pfw-checkout-template.php';
                if ( file_exists( $new_template ) ) {
                    return $new_template;
                }
            }
        }
        return $template;
    }
}

// Instantiate the class.
new PFW_Checkout();
