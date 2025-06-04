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

        // Register "Enable Skip Cart" setting
        register_setting(
            'pfw_settings_group',
            'pfw_skip_cart_enabled',
            array( 'sanitize_callback' => 'rest_sanitize_boolean' )
        );

        add_settings_field(
            'pfw_skip_cart_field',
            __( 'Enable Skip Cart', 'product-filter-for-woocommerce' ),
            array( $this, 'render_skip_cart_field' ),
            'pfw-settings',
            'pfw_general_settings_section'
        );

        // Register "Checkout Template Select" setting
        register_setting(
            'pfw_settings_group',
            'pfw_checkout_template_select',
            array( 'sanitize_callback' => 'sanitize_text_field' )
        );

        add_settings_field(
            'pfw_checkout_template_select_field',
            __( 'Checkout Template', 'product-filter-for-woocommerce' ),
            array( $this, 'render_checkout_template_select_field' ),
            'pfw-settings',
            'pfw_general_settings_section'
        );

        // Google Autocomplete Enabled Setting
        register_setting(
            'pfw_settings_group',
            'pfw_google_autocomplete_enabled',
            array( 'sanitize_callback' => 'rest_sanitize_boolean' )
        );
        add_settings_field(
            'pfw_google_autocomplete_enabled_field',
            __( 'Enable Google Address Autocomplete', 'product-filter-for-woocommerce' ),
            array( $this, 'render_google_autocomplete_enabled_field' ),
            'pfw-settings',
            'pfw_general_settings_section'
        );

        // Google Maps API Key Setting
        register_setting(
            'pfw_settings_group',
            'pfw_google_maps_api_key',
            array( 'sanitize_callback' => 'sanitize_text_field' )
        );
        add_settings_field(
            'pfw_google_maps_api_key_field',
            __( 'Google Maps API Key', 'product-filter-for-woocommerce' ),
            array( $this, 'render_google_maps_api_key_field' ),
            'pfw-settings',
            'pfw_general_settings_section'
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
            <?php esc_html_e( 'Enable Sitewide Checkout Override.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'If checked, the plugin can override the checkout page based on the template selected below.', 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }

    /**
     * Render the skip cart checkbox field.
     */
    public function render_skip_cart_field() {
        $value = get_option( 'pfw_skip_cart_enabled', false );
        ?>
        <input type="checkbox" name="pfw_skip_cart_enabled" id="pfw_skip_cart_enabled" value="1" <?php checked( true, (bool) $value ); ?> />
        <label for="pfw_skip_cart_enabled">
            <?php esc_html_e( 'Redirect users directly to the checkout page after adding a product to the cart.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <?php
    }

    /**
     * Render the checkout template select field.
     */
    public function render_checkout_template_select_field() {
        $value = get_option( 'pfw_checkout_template_select', 'default' );
        ?>
        <select name="pfw_checkout_template_select" id="pfw_checkout_template_select">
            <option value="default" <?php selected( 'default', $value ); ?>><?php esc_html_e( 'Default WooCommerce Checkout', 'product-filter-for-woocommerce' ); ?></option>
            <option value="custom_pfw" <?php selected( 'custom_pfw', $value ); ?>><?php esc_html_e( 'Custom Product Filter Checkout', 'product-filter-for-woocommerce' ); ?></option>
        </select>
        <p class="description">
            <?php esc_html_e( 'Select the template to use for the checkout page when "Enable Sitewide Checkout Override" is active.', 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }

    /**
     * Render the Google Address Autocomplete enabled checkbox field.
     */
    public function render_google_autocomplete_enabled_field() {
        $value = get_option( 'pfw_google_autocomplete_enabled', false );
        ?>
        <input type="checkbox" name="pfw_google_autocomplete_enabled" id="pfw_google_autocomplete_enabled" value="1" <?php checked( true, (bool) $value ); ?> />
        <label for="pfw_google_autocomplete_enabled">
            <?php esc_html_e( 'Enable Google Address Autocomplete on checkout fields.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <?php
    }

    /**
     * Render the Google Maps API Key text input field.
     */
    public function render_google_maps_api_key_field() {
        $value = get_option( 'pfw_google_maps_api_key', '' );
        ?>
        <input type="text" name="pfw_google_maps_api_key" id="pfw_google_maps_api_key" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">
            <?php esc_html_e( "Enter your Google Maps API Key. Ensure it has 'Places API' enabled.", 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }

    /**
     * Render the Product Offer Enabled checkbox field.
     */
    public function render_product_offer_enabled_field() {
        $value = get_option( 'pfw_product_offer_enabled', false );
        ?>
        <input type="checkbox" name="pfw_product_offer_enabled" id="pfw_product_offer_enabled" value="1" <?php checked( true, (bool) $value ); ?> />
        <label for="pfw_product_offer_enabled">
            <?php esc_html_e( 'Enable Product Offer at Checkout.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <?php
    }

    /**
     * Render the Product Offer Product ID number input field.
     */
    public function render_product_offer_product_id_field() {
        $value = get_option( 'pfw_product_offer_product_id', '' );
        ?>
        <input type="number" name="pfw_product_offer_product_id" id="pfw_product_offer_product_id" value="<?php echo esc_attr( $value ); ?>" class="regular-text" min="1" step="1" />
        <p class="description">
            <?php esc_html_e( 'Enter the Product ID to offer at checkout. Ensure the product is purchasable.', 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }

    /**
     * Render the Product Offer Text input field.
     */
    public function render_product_offer_text_field() {
        $value = get_option( 'pfw_product_offer_text', '' );
        ?>
        <input type="text" name="pfw_product_offer_text" id="pfw_product_offer_text" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description">
            <?php esc_html_e( 'Enter the promotional text to display for the offer (e.g., "Special Offer! Add this product for just $X!").', 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }

    /**
     * Render the Custom Checkout CSS Enabled checkbox field.
     */
    public function render_custom_checkout_css_enabled_field() {
        $value = get_option( 'pfw_custom_checkout_css_enabled', false );
        ?>
        <input type="checkbox" name="pfw_custom_checkout_css_enabled" id="pfw_custom_checkout_css_enabled" value="1" <?php checked( true, (bool) $value ); ?> />
        <label for="pfw_custom_checkout_css_enabled">
            <?php esc_html_e( 'Enable Custom CSS for the Custom Product Filter Checkout template.', 'product-filter-for-woocommerce' ); ?>
        </label>
        <?php
    }

    /**
     * Render the Custom Checkout CSS textarea field.
     */
    public function render_custom_checkout_css_field() {
        $value = get_option( 'pfw_custom_checkout_css', '' );
        ?>
        <textarea name="pfw_custom_checkout_css" id="pfw_custom_checkout_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description">
            <?php esc_html_e( 'Enter custom CSS rules to be applied specifically to the "Custom Product Filter Checkout" template. This CSS will only be applied when that template is active.', 'product-filter-for-woocommerce' ); ?>
        </p>
        <?php
    }
}

// Instantiate the class.
new PFW_Admin_Settings();
