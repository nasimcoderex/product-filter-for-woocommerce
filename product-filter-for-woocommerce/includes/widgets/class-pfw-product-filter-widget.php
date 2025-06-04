<?php
/**
 * PFW_Product_Filter_Widget Class
 *
 * Adds a widget to display product filters.
 *
 * @package ProductFilterForWooCommerce/Includes/Widgets
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Product_Filter_Widget.
 */
class PFW_Product_Filter_Widget extends WP_Widget {

    /**
     * Constructor.
     *
     * Sets up the widget details.
     */
    public function __construct() {
        $widget_ops = array(
            'classname'                   => 'pfw_product_filter_widget',
            'description'                 => __( 'Displays product filters for WooCommerce shop.', 'product-filter-for-woocommerce' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct(
            'pfw_product_filter_widget', // Base ID
            __( 'Product Filter (PFW)', 'product-filter-for-woocommerce' ), // Name
            $widget_ops
        );
    }

    /**
     * Outputs the content of the widget.
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $title = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : __( 'Product Filters', 'product-filter-for-woocommerce' );

        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        // Category Filters
        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'orderby'    => 'name',
            'hide_empty' => true, // Consider making this an option later
        ) );

        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            echo '<h4>' . esc_html__( 'Categories', 'product-filter-for-woocommerce' ) . '</h4>';
            echo '<ul>';

            $shop_page_url    = get_permalink( wc_get_page_id( 'shop' ) );
            $current_cat_filter = isset( $_GET['filter_product_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) ) : '';

            foreach ( $categories as $category ) {
                $cat_url = add_query_arg( 'filter_product_cat', $category->slug, $shop_page_url );
                $is_active = ( $current_cat_filter === $category->slug );
                echo '<li><a href="' . esc_url( $cat_url ) . '" class="' . ( $is_active ? 'active' : '' ) . '">' . esc_html( $category->name ) . '</a></li>';
            }
            echo '</ul>';
        }

        // Price Range Filter
        echo '<h4 style="margin-top: 20px;">' . esc_html__( 'Price Range', 'product-filter-for-woocommerce' ) . '</h4>';
        // Using action="" submits to the current URL, preserving existing query parameters not part of the form.
        // We manually add hidden fields for our own filters if they are set from other interactions (like category links).
        ?>
        <form method="get" action="" class="pfw-price-filter-form">
            <?php
            // Preserve existing filter_product_cat if set by category link click
            if ( isset( $_GET['filter_product_cat'] ) ) {
                echo '<input type="hidden" name="filter_product_cat" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) ) ) . '" />';
            }
            // Preserve other relevant query args if necessary (e.g., s for search, post_type)
            if (isset($_GET['s'])) {
                 echo '<input type="hidden" name="s" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '" />';
            }
            if (isset($_GET['post_type'])) {
                 echo '<input type="hidden" name="post_type" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) . '" />';
            }

            $min_price_current = isset( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : '';
            $max_price_current = isset( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : '';
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'min_price' ) ); ?>"><?php esc_html_e( 'Min Price:', 'product-filter-for-woocommerce' ); ?></label>
                <input type="number" id="<?php echo esc_attr( $this->get_field_id( 'min_price' ) ); ?>" name="min_price" value="<?php echo esc_attr( $min_price_current ); ?>" placeholder="<?php esc_attr_e( 'Min', 'product-filter-for-woocommerce' ); ?>" step="any" style="width:100%;" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'max_price' ) ); ?>"><?php esc_html_e( 'Max Price:', 'product-filter-for-woocommerce' ); ?></label>
                <input type="number" id="<?php echo esc_attr( $this->get_field_id( 'max_price' ) ); ?>" name="max_price" value="<?php echo esc_attr( $max_price_current ); ?>" placeholder="<?php esc_attr_e( 'Max', 'product-filter-for-woocommerce' ); ?>" step="any" style="width:100%;" />
            </p>
            <p>
                <button type="submit"><?php esc_html_e( 'Filter', 'product-filter-for-woocommerce' ); ?></button>
            </p>
        </form>
        <?php

        // Clear Filters Link
        $shop_page_url    = get_permalink( wc_get_page_id( 'shop' ) ); // Base shop URL for clearing
        // Check if any of our filters are active to decide whether to show the clear link
        if ( ! empty( $current_cat_filter ) || ! empty( $min_price_current ) || ! empty( $max_price_current ) ) {
            echo '<p class="pfw-clear-filters" style="margin-top: 15px;"><a href="' . esc_url( $shop_page_url ) . '">' . esc_html__( 'Clear All Filters', 'product-filter-for-woocommerce' ) . '</a></p>';
        }


        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Outputs the settings update form.
     *
     * @param array $instance Current settings.
     * @return void
     */
    public function form( $instance ) {
        $title = isset( $instance['title'] ) ? $instance['title'] : __( 'Product Filters', 'product-filter-for-woocommerce' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'product-filter-for-woocommerce' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
        // Add more settings fields here later (e.g., select filter preset, show/hide specific filters)
    }

    /**
     * Handles updating settings for the current instance of the widget.
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Settings to save or bool false to cancel saving.
     */
    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        // Update other settings here

        return $instance;
    }
}
