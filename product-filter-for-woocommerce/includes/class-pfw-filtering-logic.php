<?php
/**
 * PFW_Filtering_Logic Class
 *
 * Handles the actual filtering of products based on URL parameters for the new PRD.
 *
 * @package ProductFilterForWooCommerce/Includes
 * @version 0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Filtering_Logic.
 */
class PFW_Filtering_Logic {

    /**
     * Constructor.
     *
     * Hooks into WordPress/WooCommerce to modify product queries.
     */
    public function __construct() {
        add_action( 'woocommerce_product_query', array( $this, 'modify_product_query' ) );
    }

    /**
     * Modify the product query based on URL parameters.
     *
     * @param WP_Query $q The main WooCommerce query object.
     */
    public function modify_product_query( $q ) {
        // Ensure this is the main query and not an admin query.
        if ( ! $q->is_main_query() || is_admin() ) {
            return;
        }

        $meta_query = (array) $q->get( 'meta_query' );
        $tax_query  = (array) $q->get( 'tax_query' );

        // Ensure 'relation' is set for queries if we add multiple conditions.
        if ( count( $tax_query ) > 0 && !isset( $tax_query['relation'])) {
            // If there's only one existing tax query, and we add another, it needs 'AND'
            // Or if multiple exist without relation, default to AND
             $tax_query['relation'] = 'AND';
        }
         if ( count( $meta_query ) > 0 && !isset( $meta_query['relation'])) {
             $meta_query['relation'] = 'AND';
        }


        // Category Filtering from 'filter_cat' parameter
        if ( isset( $_GET['filter_cat'] ) && ! empty( $_GET['filter_cat'] ) ) {
            $category_slug = sanitize_key( $_GET['filter_cat'] );
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
            );
        }

        // Example: Price Filtering from 'min_price' and 'max_price' (if these were added to this class)
        // if ( (isset( $_GET['min_price'] ) && $_GET['min_price'] !== '') || (isset( $_GET['max_price'] ) && $_GET['max_price'] !== '') ) {
        //     $min_price = isset( $_GET['min_price'] ) && $_GET['min_price'] !== '' ? floatval( sanitize_text_field( $_GET['min_price'] ) ) : null;
        //     $max_price = isset( $_GET['max_price'] ) && $_GET['max_price'] !== '' ? floatval( sanitize_text_field( $_GET['max_price'] ) ) : null;
        //     $current_price_meta_query = array('key' => '_price', 'type' => 'NUMERIC');
        //     if ( $min_price !== null && $max_price !== null && $min_price <= $max_price ) {
        //         $current_price_meta_query['value'] = array( $min_price, $max_price );
        //         $current_price_meta_query['compare'] = 'BETWEEN';
        //     } elseif ( $min_price !== null ) {
        //         $current_price_meta_query['value'] = $min_price;
        //         $current_price_meta_query['compare'] = '>=';
        //     } elseif ( $max_price !== null ) {
        //         $current_price_meta_query['value'] = $max_price;
        //         $current_price_meta_query['compare'] = '<=';
        //     }
        //     if (!empty($current_price_meta_query['compare'])) {
        //        $meta_query['price_filter'] = $current_price_meta_query; // Use a key
        //     }
        // }


        if ( ! empty( $tax_query ) ) {
            $q->set( 'tax_query', $tax_query );
        }
        if ( ! empty( $meta_query ) && isset($meta_query['price_filter']) ) { // Only set if price_filter was actually added
            $q->set( 'meta_query', $meta_query );
        }
    }
}

// Instantiate the class to hook its actions.
if ( class_exists( 'PFW_Filtering_Logic' ) ) {
    new PFW_Filtering_Logic();
}
