<?php
/**
 * Plugin Name:       WooCommerce Filter Presets
 * Plugin URI:        https://example.com/plugins/woocommerce-filter-presets/
 * Description:       Allows merchants to build unlimited filter presets for WooCommerce products.
 * Version:           0.1.0
 * Author:            Your Name
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
 * Registers the custom post type for filter presets.
 *
 * @return void
 */
function wcfp_register_preset_post_type() {
	$labels = array(
		'name'               => _x( 'Filter Presets', 'post type general name', 'woocommerce-filter-presets' ),
		'singular_name'      => _x( 'Filter Preset', 'post type singular name', 'woocommerce-filter-presets' ),
		'menu_name'          => _x( 'Filter Presets', 'admin menu', 'woocommerce-filter-presets' ),
		'name_admin_bar'     => _x( 'Filter Preset', 'add new on admin bar', 'woocommerce-filter-presets' ),
		'add_new'            => _x( 'Add New', 'filter preset', 'woocommerce-filter-presets' ),
		'add_new_item'       => __( 'Add New Filter Preset', 'woocommerce-filter-presets' ),
		'new_item'           => __( 'New Filter Preset', 'woocommerce-filter-presets' ),
		'edit_item'          => __( 'Edit Filter Preset', 'woocommerce-filter-presets' ),
		'view_item'          => __( 'View Filter Preset', 'woocommerce-filter-presets' ),
		'all_items'          => __( 'All Filter Presets', 'woocommerce-filter-presets' ),
		'search_items'       => __( 'Search Filter Presets', 'woocommerce-filter-presets' ),
		'parent_item_colon'  => __( 'Parent Filter Presets:', 'woocommerce-filter-presets' ),
		'not_found'          => __( 'No filter presets found.', 'woocommerce-filter-presets' ),
		'not_found_in_trash' => __( 'No filter presets found in Trash.', 'woocommerce-filter-presets' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false, // Not publicly queryable
		'show_ui'            => true,  // Show in admin UI
		'show_in_menu'       => 'wcfp-filter-presets', // Slug of the new submenu page
		// 'menu_position'      => 20, // Below "Pages"
		'menu_icon'          => 'dashicons-filter', // Optional: Dashicon
		'supports'           => array( 'title' ), // Only title is needed for presets
		'hierarchical'       => false,
		'capability_type'    => 'post',
		'rewrite'            => false, // No frontend permalinks needed
		// 'show_in_rest'       => true, // Optional: enable Gutenberg editor if needed later
	);

	register_post_type( 'wcfp_preset', $args );
}
add_action( 'init', 'wcfp_register_preset_post_type' );

/**
 * Renders the admin page for filter presets.
 *
 * For now, it just displays a placeholder message.
 *
 * @return void
 */
function wcfp_render_admin_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'Manage your WooCommerce product filter presets here.', 'woocommerce-filter-presets' ); ?></p>
		<?php
		// The list table for wcfp_preset CPT will be shown below if the CPT's show_in_menu is this page's slug.
		// We might add more settings or overview information here in the future.
		?>
	</div>
	<?php
}

/**
 * Adds the "Product Filters" submenu under the WooCommerce admin menu.
 *
 * @return void
 */
function wcfp_admin_menu() {
	add_submenu_page(
		'woocommerce',                                  // Parent slug
		__( 'Product Filters', 'woocommerce-filter-presets' ), // Page title
		__( 'Product Filters', 'woocommerce-filter-presets' ), // Menu title
		'manage_woocommerce',                           // Capability
		'wcfp-filter-presets',                          // Menu slug
		'wcfp_render_admin_page'                        // Callback function
	);
}
add_action( 'admin_menu', 'wcfp_admin_menu' );

/**
 * Retrieves the filter configurations for a given preset.
 *
 * @param int $post_id The ID of the 'wcfp_preset' post.
 * @return array An array of filter configurations, or an empty array if not found or on error.
 */
function wcfp_get_preset_filters( $post_id ) {
	// Ensure post_id is a valid positive integer.
	if ( ! absint( $post_id ) > 0 ) {
		return array();
	}

	// Get the post type.
	$post_type = get_post_type( $post_id );

	// Check if the post is a 'wcfp_preset'.
	if ( 'wcfp_preset' !== $post_type ) {
		// Optionally, log an error or show a notice if the wrong post type is passed.
		// error_log( "wcfp_get_preset_filters called with incorrect post type for post ID: " . $post_id );
		return array();
	}

	$filters = get_post_meta( $post_id, '_wcfp_filters', true );

	// Ensure $filters is an array; otherwise, return an empty array.
	if ( ! is_array( $filters ) ) {
		return array();
	}

	return $filters;
}

/**
 * Saves the filter configurations for a given preset.
 *
 * @param int   $post_id The ID of the 'wcfp_preset' post.
 * @param array $filters An array of filter configurations to save.
 * @return bool True on success, false on failure.
 */
function wcfp_save_preset_filters( $post_id, $filters ) {
	// Ensure post_id is a valid positive integer.
	if ( ! absint( $post_id ) > 0 ) {
		return false;
	}

	// Ensure $filters is an array.
	if ( ! is_array( $filters ) ) {
		// Optionally, log an error or handle this case more specifically.
		// error_log( "wcfp_save_preset_filters called with non-array filters for post ID: " . $post_id );
		return false;
	}

	// Get the post type.
	$post_type = get_post_type( $post_id );

	// Check if the post is a 'wcfp_preset'.
	if ( 'wcfp_preset' !== $post_type ) {
		// Optionally, log an error or show a notice.
		// error_log( "wcfp_save_preset_filters called with incorrect post type for post ID: " . $post_id );
		return false;
	}

	// At this stage, $filters is expected to be an array.
	// Further sanitization should be done on the contents of the $filters array
	// depending on the structure of each filter configuration.
	// For now, we are saving it as is, assuming it's structured correctly.
	// Example: array_map( 'sanitize_text_field', $filters_array_values ) if they were simple text fields.

	return update_post_meta( $post_id, '_wcfp_filters', $filters );
}
