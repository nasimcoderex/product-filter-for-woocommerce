<?php
/**
 * PFW_Admin_Menu Class
 *
 * Handles the creation of the admin menu pages for the plugin.
 *
 * @package ProductFilterForWooCommerce/Admin
 * @version 0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Admin_Menu.
 */
class PFW_Admin_Menu {

    /**
     * Constructor.
     *
     * Hooks into WordPress to add the admin menu page.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
    }

    /**
     * Add admin menu page.
     *
     * Adds a top-level menu page for "Product Filters".
     */
    public function add_admin_menu_page() {
        add_menu_page(
            __( 'Product Filters', 'pfw-rewrite' ), // Page title
            __( 'Product Filters', 'pfw-rewrite' ), // Menu title
            'manage_options', // Capability
            'pfw-filter-presets', // Menu slug
            array( $this, 'render_filter_presets_page' ), // Callback function
            'dashicons-filter', // Icon URL
            58 // Position
        );
    }

    /**
     * Render Filter Presets page.
     *
     * Outputs the HTML for the main admin page for filter presets.
     */
    public function render_filter_presets_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Filter Presets', 'pfw-rewrite' ); ?></h1>
            <p><?php esc_html_e( 'Configuration for filter presets, including adding, editing, and managing filters within each preset, will be available here in future updates.', 'pfw-rewrite' ); ?></p>
            <?php // In the future, this page might list presets or offer a button to create a new one. ?>
        </div>
        <?php
    }
}

// Instantiate the class to register the menu.
if ( class_exists( 'PFW_Admin_Menu' ) ) {
    new PFW_Admin_Menu();
}
