<?php
/**
 * Plugin Name:       WooCommerce Filter Presets
 * Plugin URI:        https://example.com/plugins/woocommerce-filter-presets/
 * Description:       Allows merchants to build unlimited filter presets for WooCommerce products.
 * Version:           1.0.0
 * Author:            Your Name or Company
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woocommerce-filter-presets
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'WOO_FILTER_PRESETS_VERSION', '1.0.0' );

// TODO: Define plugin constants for paths and URLs if needed.
// define( 'WOO_FILTER_PRESETS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// define( 'WOO_FILTER_PRESETS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin textdomain for translation.
 */
function woofp_load_textdomain() {
    // load_plugin_textdomain(
    //     'woocommerce-filter-presets',
    //     false,
    //     dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    // );
    // error_log('Conceptual: woocommerce-filter-presets textdomain loaded.');
}
// add_action( 'plugins_loaded', 'woofp_load_textdomain' );

?>
