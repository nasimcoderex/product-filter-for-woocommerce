<?php
/**
 * PFW_Public_Assets Class
 *
 * Handles loading of public-facing assets (CSS, JS) for the PFW Rewrite.
 *
 * @package ProductFilterForWooCommerce/Includes
 * @version 0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Public_Assets.
 */
class PFW_Public_Assets {

    /**
     * Constructor.
     *
     * Hooks into WordPress to enqueue styles and scripts.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) ); // For future JS
    }

    /**
     * Enqueue public-facing stylesheets.
     */
    public function enqueue_styles() {
        // Conditionally load category filter widget styles
        if ( is_active_widget( false, false, 'pfw_category_filter_widget', true ) ||
             (function_exists('is_shop') && is_shop()) ||
             (function_exists('is_product_taxonomy') && is_product_taxonomy()) ) {

            wp_enqueue_style(
                'pfw-category-filter-style', // Handle
                PFW_PLUGIN_URL . 'public/css/pfw-category-filter-widget.css', // Source URL
                array(), // Dependencies
                PFW_VERSION // Version from main plugin file
            );
        }

        // Example for other styles:
        // if ( some_other_condition ) {
        //     wp_enqueue_style(
        //         'pfw-another-style',
        //         PFW_PLUGIN_URL . 'public/css/another-style.css',
        //         array(),
        //         PFW_VERSION
        //     );
        // }
    }

    /**
     * Enqueue public-facing JavaScript files.
     * (Placeholder for future use)
     */
    // public function enqueue_scripts() {
    //     if ( is_active_widget( false, false, 'pfw_category_filter_widget', true ) || is_shop() || is_product_taxonomy() ) {
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
if ( class_exists( 'PFW_Public_Assets' ) ) {
    new PFW_Public_Assets();
}
