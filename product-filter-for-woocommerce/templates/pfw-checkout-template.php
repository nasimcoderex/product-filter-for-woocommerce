<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <div class="pfw-custom-checkout-page">
        <h1><?php esc_html_e( 'Custom Checkout - Product Filter for WooCommerce', 'product-filter-for-woocommerce' ); ?></h1>

        <?php
        // Display the standard WooCommerce checkout form
        // This allows styling and minor structural changes via this template,
        // while still using the core WooCommerce checkout flow.
        echo do_shortcode('[woocommerce_checkout]');
        ?>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
