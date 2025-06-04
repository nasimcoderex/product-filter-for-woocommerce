<?php
/**
 * PFW_Assets Class
 *
 * Handles loading of public-facing assets (CSS, JS).
 *
 * @package ProductFilterForWooCommerce/Includes
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Assets.
 */
class PFW_Assets {

    /**
     * Constructor.
     *
     * Hooks into WordPress to enqueue styles and scripts.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) ); // If we have public JS files later
    }

    /**
     * Enqueue public-facing stylesheets.
     */
    public function enqueue_public_styles() {
        // Conditionally load widget styles:
        // 1. If the widget is active in any sidebar.
        // 2. Or if on a shop, product category, or product tag page (where filters are most relevant).
        // This ensures styles are loaded if shortcodes or other methods display filters on these pages,
        // even if the widget itself isn't in a sidebar for that specific page view.
        if ( is_active_widget( false, false, 'pfw_product_filter_widget', true ) ||
             (function_exists('is_shop') && is_shop()) ||
             (function_exists('is_product_taxonomy') && is_product_taxonomy()) ) {

            wp_enqueue_style(
                'pfw-filter-widget-styles', // Handle
                PFW_PLUGIN_URL . 'public/css/pfw-filter-widget.css', // Source URL
                array(), // Dependencies
                PFW_VERSION // Version
            );
        }
    }

    /**
     * Enqueue public-facing JavaScript files.
     * (Placeholder for future use if needed for non-checkout specific public JS)
     */
    // public function enqueue_public_scripts() {
    //     if ( is_active_widget( false, false, 'pfw_product_filter_widget', true ) || is_shop() || is_product_taxonomy() ) {
    //         // Example:
    //         // wp_enqueue_script(
    //         //     'pfw-public-script',
    //         //     PFW_PLUGIN_URL . 'public/js/pfw-public.js',
    //         //     array('jquery'),
    //         //     PFW_VERSION,
    //         //     true
    //         // );
    //     }
    // }
}

// Instantiate the class to hook its actions.
if ( class_exists( 'PFW_Assets' ) ) {
    new PFW_Assets();
}
