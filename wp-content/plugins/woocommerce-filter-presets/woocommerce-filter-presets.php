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
		'menu_icon'          => 'dashicons-filter',
		'supports'           => array( 'title' ),
		'hierarchical'       => false,
		'capability_type'    => 'post',
		'rewrite'            => false,
	);

	register_post_type( 'wcfp_preset', $args );
}
add_action( 'init', 'wcfp_register_preset_post_type' );

/**
 * Renders the admin page for filter presets (list table).
 */
function wcfp_render_admin_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'Manage your WooCommerce product filter presets here.', 'woocommerce-filter-presets' ); ?></p>
	</div>
	<?php
}

/**
 * Adds the "Product Filters" submenu under the WooCommerce admin menu.
 */
function wcfp_admin_menu() {
	add_submenu_page(
		'woocommerce',
		__( 'Product Filters', 'woocommerce-filter-presets' ),
		__( 'Product Filters', 'woocommerce-filter-presets' ),
		'manage_woocommerce',
		'wcfp-filter-presets',
		'wcfp_render_admin_page'
	);
}
add_action( 'admin_menu', 'wcfp_admin_menu' );

/**
 * Adds the meta box for the Preset Builder UI.
 */
function wcfp_add_preset_builder_meta_box() {
	add_meta_box(
		'wcfp_preset_builder_mb',
		__( 'Preset Builder', 'woocommerce-filter-presets' ),
		'wcfp_render_preset_builder_meta_box_content',
		'wcfp_preset',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_wcfp_preset', 'wcfp_add_preset_builder_meta_box' );

/**
 * Renders the content for the Preset Builder meta box.
 *
 * @param WP_Post $post The current post object.
 */
function wcfp_render_preset_builder_meta_box_content( $post ) {
	// Add a nonce field for security
	wp_nonce_field( 'wcfp_save_preset_data', 'wcfp_preset_nonce' );

	// Hidden field to store the structured filter data for submission
	echo '<input type="hidden" name="wcfp_filters_data" id="wcfp_filters_data_input">';

	// Get saved global options
	$preset_options = get_post_meta( $post->ID, '_wcfp_preset_options', true );
	if ( ! is_array( $preset_options ) ) {
		$preset_options = array(); // Initialize if not set
	}
	// Defaults for options
	$ajax_mode      = isset( $preset_options['ajax_mode'] ) ? $preset_options['ajax_mode'] : 'ajax';
	$permalink_mode = isset( $preset_options['permalink_mode'] ) ? $preset_options['permalink_mode'] : 'keep';
	$lazy_load      = ! empty( $preset_options['lazy_load'] );

	?>
	<div id="wcfp-preset-builder-main">
		<div id="wcfp-global-settings-area" class="postbox" style="margin-bottom: 20px;">
			<div class="postbox-header"><h2 class="hndle"><span><?php esc_html_e( 'Global Preset Settings', 'woocommerce-filter-presets' ); ?></span></h2></div>
			<div class="inside">
				<p><em><?php esc_html_e('Global settings for this preset. These apply to all filters within this preset.', 'woocommerce-filter-presets'); ?></em></p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="wcfp_ajax_mode"><?php esc_html_e( 'Filtering Mode', 'woocommerce-filter-presets' ); ?></label>
							<span class="wcfp-tooltip" title="<?php esc_attr_e('AJAX updates products instantly without a full page reload. Full Page Reload behaves like standard WooCommerce filtering.', 'woocommerce-filter-presets'); ?>">[?]</span>
						</th>
						<td>
							<select name="wcfp_preset_options[ajax_mode]" id="wcfp_ajax_mode">
								<option value="ajax" <?php selected( $ajax_mode, 'ajax' ); ?>><?php esc_html_e( 'AJAX (Instant)', 'woocommerce-filter-presets' ); ?></option>
								<option value="reload" <?php selected( $ajax_mode, 'reload' ); ?>><?php esc_html_e( 'Full Page Reload', 'woocommerce-filter-presets' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Choose how filters are applied.', 'woocommerce-filter-presets' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wcfp_permalink_mode"><?php esc_html_e( 'Permalink Mode', 'woocommerce-filter-presets' ); ?></label></th>
						<td>
							<select name="wcfp_preset_options[permalink_mode]" id="wcfp_permalink_mode">
								<option value="keep" <?php selected( $permalink_mode, 'keep' ); ?>><?php esc_html_e( 'Keep original permalink', 'woocommerce-filter-presets' ); ?></option>
								<option value="append" <?php selected( $permalink_mode, 'append' ); ?>><?php esc_html_e( 'Append terms (WC style)', 'woocommerce-filter-presets' ); ?></option>
								<option value="pretty" <?php selected( $permalink_mode, 'pretty' ); ?>><?php esc_html_e( 'Custom short permalink', 'woocommerce-filter-presets' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Select how permalinks are handled.', 'woocommerce-filter-presets' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wcfp_lazy_load"><?php esc_html_e( 'Lazy Load Filters', 'woocommerce-filter-presets' ); ?></label></th>
						<td>
							<input type="checkbox" name="wcfp_preset_options[lazy_load]" id="wcfp_lazy_load" value="1" <?php checked( $lazy_load, true ); ?>>
							<label for="wcfp_lazy_load"><?php esc_html_e( 'Enable lazy loading for filter terms', 'woocommerce-filter-presets' ); ?></label>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div id="wcfp-filters-editor-area" class="postbox" style="margin-bottom: 20px;">
			 <div class="postbox-header"><h2 class="hndle"><span><?php esc_html_e( 'Filters', 'woocommerce-filter-presets' ); ?></span></h2></div>
			 <div class="inside">
				 <p class="description" style="margin-bottom: 15px;"><?php esc_html_e('Build your filter group by adding and configuring filters below. Drag to reorder.', 'woocommerce-filter-presets'); ?></p>
				 <ul id="wcfp-filter-list">
					<?php
					$saved_filters = wcfp_get_preset_filters( $post->ID );
					if ( ! empty( $saved_filters ) && is_array( $saved_filters ) ) {
						foreach ( $saved_filters as $filter_item_data ) {
							$filter_id    = isset( $filter_item_data['id'] ) ? esc_attr( $filter_item_data['id'] ) : 'filter_' . esc_attr( uniqid() );
							$filter_type  = isset( $filter_item_data['type'] ) ? esc_attr( $filter_item_data['type'] ) : 'category';
							$filter_name  = isset( $filter_item_data['name'] ) && !empty($filter_item_data['name']) ? esc_html( $filter_item_data['name'] ) : 'Filter (' . esc_html(ucfirst($filter_type)) . ')';
							$filter_settings_json = isset( $filter_item_data['settings'] ) ? esc_attr( wp_json_encode( $filter_item_data['settings'] ) ) : '{}';

							echo '<li class="wcfp-filter-item" data-filter-id="' . $filter_id . '" data-filter-type="' . $filter_type . '" data-filter-settings="' . $filter_settings_json .'" data-filter-name="' . esc_attr($filter_name) . '">';
							echo '<span class="wcfp-filter-name">' . $filter_name . '</span>';
							echo '<span class="wcfp-filter-type-indicator">(Type: ' . esc_html( ucfirst( $filter_type ) ) . ')</span>';
							echo '<span class="wcfp-filter-controls">';
							echo '<button type="button" class="button button-small wcfp-edit-filter-button">' . esc_html__( 'Edit', 'woocommerce-filter-presets' ) . '</button>';
							echo '<button type="button" class="button button-small button-link-delete wcfp-remove-filter-button">' . esc_html__( 'Remove', 'woocommerce-filter-presets' ) . '</button>';
							echo '</span>';
							echo '</li>';
						}
					} else {
						echo '<li class="wcfp-no-filters-message">' . esc_html__( 'No filters added yet.', 'woocommerce-filter-presets' ) . '</li>';
					}
					?>
				 </ul>
				 <p>
					 <button type="button" id="wcfp-add-filter-button" class="button button-primary">
						 <?php esc_html_e( 'Add New Filter', 'woocommerce-filter-presets' ); ?>
					 </button>
				 </p>
			 </div>
		</div>

		<div id="wcfp-filter-config-panel-container" class="postbox" style="margin-bottom: 20px;">
			<h2 class="hndle"><span><?php esc_html_e( 'Selected Filter Configuration', 'woocommerce-filter-presets' ); ?></span></h2>
			<div class="inside">
				<div id="wcfp-filter-config-panel">
					<p class="wcfp-panel-placeholder"><?php esc_html_e( 'Select a filter from the list to configure its specific settings here. Each filter type will have different options.', 'woocommerce-filter-presets' ); ?></p>
				</div>
			</div>
		</div>

		<div id="wcfp-live-preview-box" class="postbox">
			<div class="postbox-header"><h2 class="hndle"><span><?php esc_html_e( 'Live Preview', 'woocommerce-filter-presets' ); ?></span></h2></div>
			<div class="inside">
				<p style="padding-bottom: 5px;"><em><?php esc_html_e( 'Frontend preview will appear here.', 'woocommerce-filter-presets' ); ?></em></p>
				<div id="wcfp-live-preview-content">
					<p><?php esc_html_e( 'Preview Area', 'woocommerce-filter-presets' ); ?></p>
				</div>
				<div id="wcfp-preview-controls">
					 <button type="button" class="button"><?php esc_html_e( 'Desktop', 'woocommerce-filter-presets' ); ?></button>
					 <button type="button" class="button"><?php esc_html_e( 'Mobile', 'woocommerce-filter-presets' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
	// The JS `update_hidden_filter_data()` is called on DOM ready, which should pick up these PHP-rendered items.
	?>
	<style>
		/* Main builder layout */
		/* #wcfp-preset-builder-main { display: flex; flex-wrap: wrap; gap: 20px; } */
		/* #wcfp-global-settings-area { flex-basis: 100%; } */
		/* #wcfp-filters-editor-area { flex: 2; min-width: 300px; } */
		/* #wcfp-filter-config-panel-container { flex: 1; min-width: 250px; } */

		/* General postbox styling is handled by WordPress */
		#wcfp-filter-list { padding: 0; margin-bottom: 10px; min-height: 20px; }
		.wcfp-filter-item { background: #fff; border: 1px solid #ccd0d4; padding: 8px 12px; margin-bottom: 6px; list-style: none; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; cursor: move; }
		.wcfp-filter-name { font-weight: bold; flex-grow: 1; }
		.wcfp-filter-type-indicator { font-size: 0.9em; color: #50575e; margin-left: 8px; margin-right: auto; padding-right: 10px; }
		.wcfp-filter-controls .button { margin-left: 8px; vertical-align: middle; }
		.wcfp-no-filters-message, .wcfp-panel-placeholder { padding: 10px 0; color: #50575e; font-style: italic; text-align: center; }
		#wcfp-filter-config-panel { min-height: 50px; }
		#wcfp-filter-config-panel h3 { margin-top: 0; font-size: 1em; font-weight: 600; padding: 8px 12px; margin: -12px -12px 10px -12px; background: #f0f0f1; border-bottom: 1px solid #ccd0d4; }
		#wcfp-filter-config-panel p { margin-bottom: 15px; }
		.wcfp-sortable-placeholder { border: 1px dashed #ccd0d4; background-color: #f0f0f1; height: 38px; margin-bottom: 6px; display: block; }

		.wcfp-tooltip {
			display: inline-block;
			margin-left: 5px;
			padding: 0px 6px; /* Adjusted padding */
			border: 1px solid #ccd0d4; /* WP standard border */
			border-radius: 50%;
			background-color: #f0f0f1; /* Light gray */
			color: #2c3338; /* WP text color */
			font-size: 11px; /* Smaller font */
			line-height: 1.5; /* Adjust line height for vertical centering if needed */
			font-weight: bold;
			cursor: help;
			position: relative;
		}

		/* Live Preview Specific Styles */
		#wcfp-live-preview-content {
			border: 1px dashed #ccd0d4;
			min-height: 200px;
			padding: 10px;
			background-color: #fff;
			position: relative;
		}
		#wcfp-live-preview-content > p {
			text-align: center;
			color: #999;
			padding-top: 80px;
		}
		#wcfp-preview-controls {
			margin-top: 10px;
			text-align: right;
			padding: 10px;
			border-top: 1px solid #eee;
		}
		/* Ensure postbox headers are consistent if WP version < 5.5 style is different */
		.postbox-header {
			/* border-bottom: 1px solid #ccd0d4; */ /* Default WP style */
		}
		.postbox-header h2.hndle {
			font-size: 14px; /* Default WP style */
			padding: 8px 12px; /* Default WP style */
			margin: 0; /* Default WP style */
			line-height: 1.4; /* Default WP style */
		}
	</style>
	<?php
}

/**
 * Enqueues scripts and styles for the preset builder admin page.
 */
function wcfp_enqueue_preset_builder_scripts( $hook_suffix ) {
	$current_screen = get_current_screen();
	if ( $current_screen && $current_screen->post_type === 'wcfp_preset' && ( $hook_suffix === 'post.php' || $hook_suffix === 'post-new.php' ) ) {
		$plugin_url = plugin_dir_url( __FILE__ );
		wp_enqueue_script(
			'wcfp-preset-builder',
			$plugin_url . 'admin/js/preset-builder.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			'0.1.0',
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wcfp_enqueue_preset_builder_scripts' );

/**
 * Saves the custom meta box data for `wcfp_preset` CPT.
 */
function wcfp_save_preset_meta_box_data( $post_id ) {
	if ( ! isset( $_POST['wcfp_preset_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['wcfp_preset_nonce'] ), 'wcfp_save_preset_data' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['wcfp_filters_data'] ) ) {
		wcfp_save_preset_filters( $post_id, array() ); // Clear if not set
		return;
	}

	$json_data = stripslashes( sanitize_text_field( wp_unslash( $_POST['wcfp_filters_data'] ) ) ); // Basic sanitization for JSON string
	$filters_array = json_decode( $json_data, true );

	if ( ! is_array( $filters_array ) ) {
		$filters_array = array(); // Default to empty array on error
	}

	// Deeper sanitization of the array structure
	$sanitized_filters = array();
	foreach ( $filters_array as $filter_item ) {
		$s_item = array();
		$s_item['id']       = isset($filter_item['id']) ? sanitize_text_field( $filter_item['id'] ) : 'filter_' . uniqid();
		$s_item['type']     = isset($filter_item['type']) ? sanitize_key( $filter_item['type'] ) : 'category';
		$s_item['name']     = isset($filter_item['name']) ? sanitize_text_field( $filter_item['name'] ) : 'Filter';
		// Settings sanitization would be recursive and type-dependent. For now, assume settings are simple key/value.
		$s_item['settings'] = isset($filter_item['settings']) && is_array($filter_item['settings']) ?
								array_map( 'sanitize_text_field', $filter_item['settings'] ) : array();
		$sanitized_filters[] = $s_item;
	}

	// Save the sanitized filters list
	wcfp_save_preset_filters( $post_id, $sanitized_filters );

	// Save Global Preset Options
	if ( isset( $_POST['wcfp_preset_options'] ) && is_array( $_POST['wcfp_preset_options'] ) ) {
		// Ensure $_POST['wcfp_preset_options'] is cast to an array, just in case.
		$raw_options = (array) $_POST['wcfp_preset_options'];
		$sanitized_options = array();

		// Sanitize ajax_mode
		if ( isset( $raw_options['ajax_mode'] ) && in_array( $raw_options['ajax_mode'], array( 'ajax', 'reload' ), true ) ) {
			$sanitized_options['ajax_mode'] = $raw_options['ajax_mode'];
		} else {
			$sanitized_options['ajax_mode'] = 'ajax'; // Default
		}

		// Sanitize permalink_mode
		if ( isset( $raw_options['permalink_mode'] ) && in_array( $raw_options['permalink_mode'], array( 'keep', 'append', 'pretty' ), true ) ) {
			$sanitized_options['permalink_mode'] = $raw_options['permalink_mode'];
		} else {
			$sanitized_options['permalink_mode'] = 'keep'; // Default
		}

		// Sanitize lazy_load (treat as boolean, store as '1' or '0')
		$sanitized_options['lazy_load'] = ! empty( $raw_options['lazy_load'] ) ? '1' : '0';

		update_post_meta( $post_id, '_wcfp_preset_options', $sanitized_options );
	} else {
		// If no options submitted, save default values or clear existing meta.
		// Forcing defaults ensures the meta field exists with expected structure.
		update_post_meta( $post_id, '_wcfp_preset_options', array(
			'ajax_mode'      => 'ajax',
			'permalink_mode' => 'keep',
			'lazy_load'      => '0',
		) );
	}
}
add_action( 'save_post_wcfp_preset', 'wcfp_save_preset_meta_box_data' );

/**
 * Retrieves the filter configurations for a given preset.
 */
function wcfp_get_preset_filters( $post_id ) {
	if ( ! absint( $post_id ) > 0 ) {
		return array();
	}
	$post_type = get_post_type( $post_id );
	if ( 'wcfp_preset' !== $post_type ) {
		return array();
	}
	$filters = get_post_meta( $post_id, '_wcfp_filters', true );
	if ( ! is_array( $filters ) ) {
		return array();
	}
	return $filters;
}

/**
 * Saves the filter configurations for a given preset.
 */
function wcfp_save_preset_filters( $post_id, $filters ) {
	if ( ! absint( $post_id ) > 0 ) {
		return false;
	}
	if ( ! is_array( $filters ) ) {
		return false;
	}
	$post_type = get_post_type( $post_id );
	if ( 'wcfp_preset' !== $post_type ) {
		return false;
	}
	return update_post_meta( $post_id, '_wcfp_filters', $filters );
}
