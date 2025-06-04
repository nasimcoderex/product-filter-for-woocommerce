<?php
/**
 * Plugin Name:       Product Filter for WooCommerce
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       A product filter plugin for WooCommerce.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       product-filter-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Check if WooCommerce is active.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', 'pfw_woocommerce_not_active_notice' );
    add_action( 'admin_init', 'pfw_deactivate_plugin' );
    return;
}

function pfw_woocommerce_not_active_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'Product Filter for WooCommerce requires WooCommerce to be active. Please activate WooCommerce.', 'product-filter-for-woocommerce' ); ?></p>
    </div>
    <?php
}

function pfw_deactivate_plugin() {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    if ( isset( $_GET['activate'] ) ) {
        unset( $_GET['activate'] );
    }
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRODUCT_FILTER_FOR_WOOCOMMERCE_VERSION', '1.0.0' );

// Define PFW_VERSION for consistency if used elsewhere, though PRODUCT_FILTER_FOR_WOOCOMMERCE_VERSION is primary.
if ( ! defined( 'PFW_VERSION' ) ) {
    define( 'PFW_VERSION', PRODUCT_FILTER_FOR_WOOCOMMERCE_VERSION );
}

// Define plugin URL for easy access to assets.
if ( ! defined( 'PFW_PLUGIN_URL' ) ) {
    define( 'PFW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Define constants for plugin features.
 */
// TODO: PFW_CHECKOUT_OVERRIDE_ENABLED is now a setting, consider removing if no other constants are needed.

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-product-filter-for-woocommerce-activator.php
 */
function pfw_activate_plugin() {
    // Activation tasks: set default options, flush rewrite rules, etc.
    // For now, we might just use the existing Activator class if it's suitable,
    // or add direct code here.
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-filter-for-woocommerce-activator.php';
    Product_Filter_For_WooCommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-product-filter-for-woocommerce-deactivator.php
 */
function pfw_deactivate_plugin_on_deactivation() {
    // Deactivation tasks: clean up options, remove custom tables, etc.
    // For now, we might just use the existing Deactivator class if it's suitable,
    // or add direct code here.
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-filter-for-woocommerce-deactivator.php';
    Product_Filter_For_WooCommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pfw_activate_plugin' );
register_deactivation_hook( __FILE__, 'pfw_deactivate_plugin_on_deactivation' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-product-filter-for-woocommerce.php';

/**
 * The class responsible for checkout modifications.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pfw-checkout.php';

/**
 * The class responsible for administrative settings.
 */
if ( is_admin() ) {
    require plugin_dir_path( __FILE__ ) . 'admin/class-pfw-admin-settings.php';
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_product_filter_for_woocommerce() {

    $plugin = new Product_Filter_For_WooCommerce();
    $plugin->run();

}
run_product_filter_for_woocommerce();
