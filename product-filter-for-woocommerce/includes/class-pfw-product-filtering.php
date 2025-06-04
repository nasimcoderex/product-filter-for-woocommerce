<?php
/**
 * PFW_Product_Filtering Class
 *
 * Handles the actual filtering of products based on URL parameters.
 *
 * @package ProductFilterForWooCommerce/Includes
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Product_Filtering.
 */
class PFW_Product_Filtering {

    /**
     * Constructor.
     *
     * Hooks into WordPress/WooCommerce to modify product queries.
     */
    public function __construct() {
        add_action( 'woocommerce_product_query', array( $this, 'filter_product_query_by_url' ) );
    }

    /**
     * Modify the product query based on URL parameters.
     *
     * @param WP_Query $q The main WooCommerce query object.
     */
    public function filter_product_query_by_url( $q ) {
        // Ensure this is the main query and not an admin query.
        if ( ! $q->is_main_query() || is_admin() ) {
            return;
        }

        // Category Filtering
        if ( isset( $_GET['filter_product_cat'] ) && ! empty( $_GET['filter_product_cat'] ) ) {
            $category_slug = sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) );

            $tax_query = (array) $q->get( 'tax_query' );
            if ( empty( $tax_query ) ) {
                $tax_query = array();
            }
            // Ensure 'relation' is set if multiple tax queries are added.
            // For now, we're just adding one, but good practice if other plugins add tax_queries.
            if ( count( $tax_query ) > 1 && !isset( $tax_query['relation'])) {
                 $tax_query['relation'] = 'AND';
            }


            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
            );
            $q->set( 'tax_query', $tax_query );
        }

        // Price Filtering
        $min_price_param = isset( $_GET['min_price'] ) ? wp_unslash( $_GET['min_price'] ) : null;
        $max_price_param = isset( $_GET['max_price'] ) ? wp_unslash( $_GET['max_price'] ) : null;

        $min_price = ( $min_price_param !== null && $min_price_param !== '' ) ? floatval( sanitize_text_field( $min_price_param ) ) : null;
        $max_price = ( $max_price_param !== null && $max_price_param !== '' ) ? floatval( sanitize_text_field( $max_price_param ) ) : null;

        if ( $min_price !== null || $max_price !== null ) {
            $meta_query = (array) $q->get( 'meta_query' );
            if ( empty( $meta_query ) ) {
                $meta_query = array();
            }
            // Ensure 'relation' is set if multiple meta queries are added.
             if ( count( $meta_query ) > 1 && !isset( $meta_query['relation'])) {
                 $meta_query['relation'] = 'AND';
            }

            $price_meta_query = array(
                'key'     => '_price',
                'type'    => 'NUMERIC',
                'compare' => '', // Set based on conditions
            );

            if ( $min_price !== null && $max_price !== null && $min_price <= $max_price ) {
                $price_meta_query['value']   = array( $min_price, $max_price );
                $price_meta_query['compare'] = 'BETWEEN';
            } elseif ( $min_price !== null ) {
                $price_meta_query['value']   = $min_price;
                $price_meta_query['compare'] = '>=';
            } elseif ( $max_price !== null ) {
                $price_meta_query['value']   = $max_price;
                $price_meta_query['compare'] = '<=';
            }

            if ( ! empty( $price_meta_query['compare'] ) ) {
                $meta_query['price_filter'] = $price_meta_query; // Use a key to prevent just appending
                $q->set( 'meta_query', $meta_query );
            }
        }
    }
}

// Instantiate the class to hook its actions.
if ( class_exists( 'PFW_Product_Filtering' ) ) {
    new PFW_Product_Filtering();
}
