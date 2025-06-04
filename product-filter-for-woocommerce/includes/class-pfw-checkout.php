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

        // Hook for displaying product offer
        add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_checkout_product_offer' ) );

        // AJAX hooks for adding offer to cart
        add_action( 'wp_ajax_pfw_add_offer_to_cart', array( $this, 'ajax_add_offer_to_cart' ) );
        add_action( 'wp_ajax_nopriv_pfw_add_offer_to_cart', array( $this, 'ajax_add_offer_to_cart' ) );

        // Hook for injecting custom checkout CSS
        add_action( 'wp_head', array( $this, 'inject_custom_checkout_css' ) );
    }

    /**
     * Enqueue scripts for the checkout page.
     *
     * This includes Google Maps API for address autocomplete if enabled,
     * and scripts for the checkout product offer.
     */
    public function enqueue_checkout_scripts() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }

        // Google Maps Autocomplete
        if ( get_option( 'pfw_google_autocomplete_enabled', false ) ) {
            $api_key = get_option( 'pfw_google_maps_api_key' );
            if ( ! empty( $api_key ) ) {
                wp_enqueue_script(
                    'pfw-google-autocomplete',
                    PFW_PLUGIN_URL . 'public/js/pfw-google-autocomplete.js',
                    array( 'jquery' ),
                    PFW_VERSION,
                    true
                );
                wp_enqueue_script(
                    'google-maps-places',
                    'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places&callback=initPfwGooglePlacesApiCallback',
                    array(),
                    null,
                    true
                );
            }
        }

        // Checkout Product Offer
        if ( get_option( 'pfw_product_offer_enabled', false ) ) {
            $product_id = get_option( 'pfw_product_offer_product_id' );
            if ( ! empty( $product_id ) && wc_get_product( $product_id ) ) { // Check if product exists
                 wp_enqueue_script(
                    'pfw-checkout-offer',
                    PFW_PLUGIN_URL . 'public/js/pfw-checkout-offer.js',
                    array( 'jquery' ),
                    PFW_VERSION,
                    true
                );
                wp_localize_script(
                    'pfw-checkout-offer',
                    'pfw_offer_params',
                    array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce'    => wp_create_nonce( 'pfw_offer_nonce' ),
                    )
                );
            }
        }
    }

    /**
     * Injects custom CSS into the <head> on the custom checkout page.
     */
    public function inject_custom_checkout_css() {
        if ( get_option( 'pfw_custom_checkout_css_enabled', false ) &&
             function_exists( 'is_checkout' ) && is_checkout() &&
             get_option( 'pfw_checkout_override_enabled', false ) &&
             get_option( 'pfw_checkout_template_select', 'default' ) === 'custom_pfw' ) {

            $custom_css = get_option( 'pfw_custom_checkout_css' );
            if ( ! empty( $custom_css ) ) {
                // Sanitize CSS: wp_strip_all_tags is very basic. For more robust sanitization,
                // a library like Safecss would be needed, but that's an external dependency.
                // For admin-input CSS, this level of sanitization is often considered acceptable
                // as it prevents HTML/script injection.
                $sanitized_css = wp_strip_all_tags( $custom_css );
                // Alternatively, for allowing style tags but nothing else (more dangerous):
                // $sanitized_css = wp_kses( $custom_css, array( 'style' => array( 'type' => array() ) ) );
                // However, wp_strip_all_tags is safer if the input is purely CSS rules.
                // If the admin intends to write <style> themselves, then this will strip it.
                // Assuming admin provides raw CSS rules, not wrapped in <style> tags.

                echo "\n<!-- Product Filter for WooCommerce Custom Checkout CSS -->\n";
                echo '<style type="text/css" id="pfw-custom-checkout-styles">' . "\n";
                echo $sanitized_css; // Already sanitized by sanitize_textarea_field on save, and wp_strip_all_tags here for output.
                echo "\n</style>\n";
                echo "<!-- End PFW Custom Checkout CSS -->\n";
            }
        }
    }

    /**
     * Display the product offer on the checkout page.
     */
    public function display_checkout_product_offer() {
        if ( ! get_option( 'pfw_product_offer_enabled', false ) ) {
            return;
        }

        $product_id = get_option( 'pfw_product_offer_product_id' );
        $offer_text = get_option( 'pfw_product_offer_text', __( 'Special Offer!', 'product-filter-for-woocommerce' ) );

        if ( empty( $product_id ) ) {
            return;
        }

        $product = wc_get_product( $product_id );
        if ( ! $product || ! $product->is_purchasable() ) {
            return;
        }

        // Check if the offer product is already in the cart
        $is_offer_in_cart = false;
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( $cart_item['product_id'] == $product_id ) {
                $is_offer_in_cart = true;
                break;
            }
        }

        ?>
        <div id="pfw_checkout_offer" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">
            <h3><?php echo esc_html( $offer_text ); ?></h3>
            <p>
                <?php echo esc_html( $product->get_name() ); ?> -
                <strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
            </p>
            <p>
                <input type="checkbox" id="pfw_add_offer_product_checkbox" name="pfw_add_offer_product" value="<?php echo esc_attr( $product_id ); ?>" <?php checked($is_offer_in_cart, true); ?> <?php disabled($is_offer_in_cart, true); ?> />
                <label for="pfw_add_offer_product_checkbox"><?php esc_html_e( 'Yes, add this to my order!', 'product-filter-for-woocommerce' ); ?></label>
            </p>
            <div id="pfw_offer_feedback"></div>
        </div>
        <?php
    }

    /**
     * Handle AJAX request to add the offer product to the cart.
     */
    public function ajax_add_offer_to_cart() {
        check_ajax_referer( 'pfw_offer_nonce', 'nonce' );

        if ( ! isset( $_POST['product_id'] ) || empty( $_POST['product_id'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Product ID missing.', 'product-filter-for-woocommerce' ) ) );
        }

        $product_id = absint( $_POST['product_id'] );
        $product = wc_get_product( $product_id );

        if ( ! $product || ! $product->is_purchasable() ) {
            wp_send_json_error( array( 'message' => __( 'Product not valid or not purchasable.', 'product-filter-for-woocommerce' ) ) );
        }

        // Check if product already in cart to avoid duplicate messages or issues if JS allows re-adding
        $is_offer_in_cart = false;
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( $cart_item['product_id'] == $product_id ) {
                $is_offer_in_cart = true;
                break;
            }
        }

        if ($is_offer_in_cart) {
             wp_send_json_success( array( 'message' => __( 'Offer already in cart.', 'product-filter-for-woocommerce' ) ) );
        }

        try {
            $cart_item_key = WC()->cart->add_to_cart( $product_id, 1 );
            if ( $cart_item_key ) {
                WC()->cart->calculate_totals(); // Ensure totals are updated
                wp_send_json_success( array( 'message' => __( 'Offer product added to cart.', 'product-filter-for-woocommerce' ) ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Could not add product to cart. It might be out of stock or have other restrictions.', 'product-filter-for-woocommerce' ) ) );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
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
