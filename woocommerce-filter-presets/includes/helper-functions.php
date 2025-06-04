<?php
// File: includes/helper-functions.php

if ( ! defined( 'ABSPATH' ) ) { // Or WPINC
    // exit; // Exit if accessed directly.
}

// Ensure dependent classes are available (conceptual)
// These would be included by the main plugin file.
// require_once __DIR__ . '/class-filter-preset.php';
// require_once __DIR__ . '/class-individual-filter.php';

/**
 * Retrieves a filter preset definition.
 * (Placeholder - in a real plugin, this would load from DB / CPT).
 *
 * @param int $preset_id The ID of the preset to retrieve.
 * @return WooFilterPreset|null The preset object or null if not found.
 */
function woofp_get_preset_definition( $preset_id ) {
    // Ensure class definitions are loaded if not autoloaded
    // This is more for direct testing of this file if needed.
    if (!class_exists('WooFilterPreset')) {
        if (file_exists(__DIR__ . '/class-filter-preset.php')) {
            require_once __DIR__ . '/class-filter-preset.php';
        } else {
            // Fallback or error if class file doesn't exist
            error_log('WooFilterPreset class definition not found in helper-functions.php');
            return null;
        }
    }
    if (!class_exists('WooIndividualFilter')) {
         if (file_exists(__DIR__ . '/class-individual-filter.php')) {
            require_once __DIR__ . '/class-individual-filter.php';
        } else {
             error_log('WooIndividualFilter class definition not found in helper-functions.php');
            return null;
        }
    }

    // Dummy data for now:
    $presets = [];

    // Preset 1
    $preset1 = new WooFilterPreset(1, 'Primary Sidebar Filters');
    $preset1->set_global_setting('ajax_enabled', true);

    $filter1_1 = new WooIndividualFilter('cat_filter', 'category', 'product_cat', 'Categories');
    $filter1_1->render_type = 'checkboxes';
    $filter1_1->show_product_counts = true;
    $preset1->add_filter($filter1_1);

    $filter1_2 = new WooIndividualFilter('color_filter', 'attribute', 'pa_color', 'Colors');
    $filter1_2->render_type = 'color_swatches';
    $filter1_2->set_setting('swatch_shape', 'square');
    $preset1->add_filter($filter1_2);

    $filter1_3 = new WooIndividualFilter('price_filter', 'price', '_price', 'Price Range');
    $filter1_3->render_type = 'price_slider';
    $preset1->add_filter($filter1_3);

    $presets[1] = $preset1;

    // Preset 2
    $preset2 = new WooFilterPreset(2, 'Mobile Product Filters');
    $preset2->set_global_setting('ajax_enabled', 'apply_button');
    $preset2->set_global_setting('permalink_mode', 'append_terms');

    $filter2_1 = new WooIndividualFilter('tag_filter', 'tag', 'product_tag', 'Tags');
    $filter2_1->render_type = 'select';
    $preset2->add_filter($filter2_1);

    $filter2_2 = new WooIndividualFilter('stock_filter', 'stock', '_stock_status', 'Availability');
    $filter2_2->render_type = 'radio_buttons';
    // Conceptual options for stock might be: 'instock', 'outofstock'
    // This would be handled by get_available_filter_options later
    $preset2->add_filter($filter2_2);


    $presets[2] = $preset2;

    return isset( $presets[ $preset_id ] ) ? $presets[ $preset_id ] : null;
}
?>
