<?php
/**
 * Plugin Name:       Product Filter for WooCommerce (Rewrite)
 * Plugin URI:        https://example.com/product-filter-woocommerce
 * Description:       Advanced product filtering for WooCommerce. (Based on new PRD)
 * Version:           0.1.0
 * Author:            Your Name / Company
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pfw-rewrite
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires at least: 5.8
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'PFW_PLUGIN_FILE', __FILE__ );
define( 'PFW_PLUGIN_DIR', plugin_dir_path( PFW_PLUGIN_FILE ) );
define( 'PFW_PLUGIN_URL', plugin_dir_url( PFW_PLUGIN_FILE ) );
define( 'PFW_VERSION', '0.1.0' ); // Keep in sync with plugin header version

// Placeholder for activation/deactivation hooks
function pfw_activate_plugin_rewrite() {
    // Placeholder for activation tasks
    // e.g., flush rewrite rules, set default options
}
register_activation_hook( PFW_PLUGIN_FILE, 'pfw_activate_plugin_rewrite' );

function pfw_deactivate_plugin_rewrite() {
    // Placeholder for deactivation tasks
}
register_deactivation_hook( PFW_PLUGIN_FILE, 'pfw_deactivate_plugin_rewrite' );

// Load admin-specific code
if ( is_admin() ) {
    require_once PFW_PLUGIN_DIR . 'admin/class-pfw-admin-menu.php';
    // Future admin classes will be included here
}

// Include widget classes
require_once PFW_PLUGIN_DIR . 'includes/widgets/class-pfw-category-filter-widget.php';
// Future widgets will be included here.

// Include core filtering logic
require_once PFW_PLUGIN_DIR . 'includes/class-pfw-filtering-logic.php';

// Handle Public Assets (CSS, JS)
require_once PFW_PLUGIN_DIR . 'includes/class-pfw-public-assets.php';

/**
 * Register Widgets.
 */
function pfw_rewrite_register_widgets() {
    register_widget( 'PFW_Category_Filter_Widget' );
    // Future widgets will be registered here
}
add_action( 'widgets_init', 'pfw_rewrite_register_widgets' );

// Placeholder for loading main plugin class or functions
// require_once PFW_PLUGIN_DIR . 'includes/class-pfw-main.php';
// function pfw_run_plugin() {
//     PFW_Main::instance();
// }
// add_action( 'plugins_loaded', 'pfw_run_plugin' );
?>
