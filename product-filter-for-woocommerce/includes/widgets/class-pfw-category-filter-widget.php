<?php
/**
 * PFW_Category_Filter_Widget Class
 *
 * Adds a widget to display a product category filter.
 *
 * @package ProductFilterForWooCommerce/Includes/Widgets
 * @version 0.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class PFW_Category_Filter_Widget.
 */
class PFW_Category_Filter_Widget extends WP_Widget {

    /**
     * Constructor.
     *
     * Sets up the widget details.
     */
    public function __construct() {
        $widget_ops = array(
            'classname'                   => 'pfw_category_filter_widget',
            'description'                 => __( 'Displays a product category filter for WooCommerce shop (non-AJAX).', 'pfw-rewrite' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct(
            'pfw_category_filter_widget', // Base ID
            __( 'Category Filter (PFW Rewrite)', 'pfw-rewrite' ), // Name
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

        $title = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : __( 'Filter by Category', 'pfw-rewrite' );

        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'orderby'    => 'name',
            'hide_empty' => true,
        ) );

        $current_filter_cat = isset( $_GET['filter_cat'] ) ? sanitize_key( $_GET['filter_cat'] ) : '';
        $shop_page_url    = get_permalink( wc_get_page_id( 'shop' ) );

        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            echo '<ul>';
            foreach ( $categories as $category ) {
                $filter_url = add_query_arg( 'filter_cat', $category->slug, $shop_page_url );
                // Preserve other filters if they exist (e.g. min_price, max_price)
                if (isset($_GET['min_price'])) {
                    $filter_url = add_query_arg('min_price', sanitize_text_field($_GET['min_price']), $filter_url);
                }
                if (isset($_GET['max_price'])) {
                    $filter_url = add_query_arg('max_price', sanitize_text_field($_GET['max_price']), $filter_url);
                }
                // Add other existing filters here if needed

                $is_active = ( $current_filter_cat === $category->slug );
                echo '<li><a href="' . esc_url( $filter_url ) . '" class="' . ( $is_active ? 'active' : '' ) . '">' . esc_html( $category->name ) . '</a></li>';
            }
            echo '</ul>';
        }

        if ( ! empty( $current_filter_cat ) ) {
            $clear_filter_url = $shop_page_url;
             // Preserve other filters when clearing this specific one
            if (isset($_GET['min_price'])) {
                $clear_filter_url = add_query_arg('min_price', sanitize_text_field($_GET['min_price']), $clear_filter_url);
            }
            if (isset($_GET['max_price'])) {
                $clear_filter_url = add_query_arg('max_price', sanitize_text_field($_GET['max_price']), $clear_filter_url);
            }
            // Add other existing filters here if needed

            echo '<p class="pfw-clear-filter-link"><a href="' . esc_url( $clear_filter_url ) . '">' . esc_html__( 'Clear Category Filter', 'pfw-rewrite' ) . '</a></p>';
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
        $title = isset( $instance['title'] ) ? $instance['title'] : __( 'Filter by Category', 'pfw-rewrite' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'pfw-rewrite' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
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
        return $instance;
    }
}
