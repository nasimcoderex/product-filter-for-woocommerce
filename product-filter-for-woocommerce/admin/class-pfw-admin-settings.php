<?php
/**
 * PFW_Admin_Settings Class
 *
 * Handles the plugin settings page.
 *
 * @package ProductFilterForWooCommerce/Admin
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class PFW_Admin_Settings.
 */
class PFW_Admin_Settings {

    /**
     * Constructor.
     *
     * Hooks into WordPress to add the settings page.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add settings page.
     *
     * Adds a submenu page under WooCommerce.
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce', // Parent slug
            __( 'Product Filter Settings', 'product-filter-for-woocommerce' ), // Page title
            __( 'Product Filter', 'product-filter-for-woocommerce' ), // Menu title
            'manage_woocommerce', // Capability
            'pfw-settings', // Menu slug
            array( $this, 'render_settings_page' ) // Callback function
        );
    }

    /**
     * Render settings page.
     *
     * Outputs the HTML for the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Settings for the Product Filter for WooCommerce plugin.', 'product-filter-for-woocommerce' ); ?></p>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'pfw_settings_group' );
                do_settings_sections( 'pfw-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register plugin settings, sections, and fields.
     */
    public function register_settings() {
        register_setting(
            'pfw_settings_group',                         // Option group
            'pfw_checkout_override_enabled',              // Option name
            array( 'sanitize_callback' => 'rest_sanitize_boolean' ) // Sanitize callback
        );

        add_settings_section(
            'pfw_general_settings_section',               // ID
            __( 'General Settings', 'product-filter-for-woocommerce' ), // Title
            null,                                         // Callback (optional)
            'pfw-settings'                                // Page slug
        );

        add_settings_field(
            'pfw_checkout_override_field',                // ID
            __( 'Enable Checkout Override', 'product-filter-for-woocommerce' ), // Title
            array( $this, 'render_checkout_override_field' ), // Callback to render the field
            'pfw-settings',                               // Page slug
            'pfw_general_settings_section'                // Section ID
        );
    }

    /**
     * Render the checkout override checkbox field.
     */
    public function render_checkout_override_field() {
        $value = get_option( 'pfw_checkout_override_enabled', false );
        ?>
        <input type="checkbox" name="pfw_checkout_override_enabled" id="pfw_checkout_override_enabled" value="1" <?php checked( true, (bool) $value ); ?> />
        <label for="pfw_checkout_override_enabled">
            <?php esc_html_e( 'Override the default WooCommerce checkout page with a custom template.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <?php
    }
}

// Instantiate the class.
new PFW_Admin_Settings();
