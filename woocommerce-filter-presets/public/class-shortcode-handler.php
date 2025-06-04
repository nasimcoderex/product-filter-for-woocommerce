<?php
// File: public/class-shortcode-handler.php

if ( ! defined( 'ABSPATH' ) ) { // Or WPINC
    // exit; // Exit if accessed directly.
}

// Ensure dependent classes/functions are available (conceptual)
// require_once WOO_FILTER_PRESETS_PLUGIN_DIR . 'includes/helper-functions.php';
// require_once WOO_FILTER_PRESETS_PLUGIN_DIR . 'includes/class-product-filter-engine.php';

class WooFilterPreset_Shortcode_Handler {

    /**
     * Registers the shortcode.
     * In a real WP environment, this would be hooked into 'init'.
     */
    public static function register() {
        // add_shortcode( 'product_filter_preset', [ __CLASS__, 'render_preset_shortcode' ] );
        // For now, we'll just note this. In a real plugin, the line above would be active.
        // For testing outside WordPress, you might call render_preset_shortcode directly.
    }

    /**
     * Renders the filter preset based on the shortcode attributes.
     *
     * @param array $atts Shortcode attributes. Expects 'id' for the preset.
     * @return string HTML output for the filter preset.
     */
    public static function render_preset_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'id' => 0,
        ], $atts, 'product_filter_preset' );

        $preset_id = intval( $atts['id'] );

        if ( ! $preset_id ) {
            return '<p>' . esc_html__( 'Error: Filter Preset ID is missing or invalid.', 'woocommerce-filter-presets' ) . '</p>';
        }

        // Conceptual: Load preset definition and filter engine
        // These would typically be available via a service locator or global instance
        // For now, directly using helper and instantiating engine.
        if (!function_exists('woofp_get_preset_definition')) {
             if (file_exists(__DIR__ . '/../includes/helper-functions.php')) { // Relative path
                require_once __DIR__ . '/../includes/helper-functions.php';
            } else {
                return '<p>' . esc_html__( 'Error: Helper functions not found.', 'woocommerce-filter-presets' ) . '</p>';
            }
        }

        $preset = woofp_get_preset_definition( $preset_id );

        if ( ! $preset ) {
            return "<p>" . sprintf( esc_html__( "Error: Filter Preset with ID '%s' not found.", 'woocommerce-filter-presets' ), $preset_id ) . "</p>";
        }

        if (!class_exists('WooProductFilterEngine')) {
            if (file_exists(__DIR__ . '/../includes/class-product-filter-engine.php')) { // Relative path
                require_once __DIR__ . '/../includes/class-product-filter-engine.php';
            } else {
                 return '<p>' . esc_html__( 'Error: Filter engine not found.', 'woocommerce-filter-presets' ) . '</p>';
            }
        }
        $filter_engine = new WooProductFilterEngine();

        // Get available filter options (conceptual for now)
        // In a real scenario, $current_query_vars would come from the global $wp_query or page context
        $current_query_vars = [];
        $available_filters = $filter_engine->get_available_filter_options( $preset_id, $current_query_vars );

        // Basic HTML output
        $output = "<div class='woocommerce-filter-presets-shortcode-wrap' data-preset-id='{$preset_id}'>";
        $output .= "<h3>" . esc_html( $preset->name ) . " (" . esc_html__( 'Preset ID', 'woocommerce-filter-presets') . ": {$preset_id})</h3>";

        if ( empty( $available_filters ) ) {
            $output .= "<p>" . esc_html__( 'No filters available for this preset or current view.', 'woocommerce-filter-presets' ) . "</p>";
        } else {
            $output .= "<form class='woofp-filter-form'>";
            foreach ( $available_filters as $filter_data ) {
                $output .= "<div class='woofp-filter-block' id='woofp-filter-{$filter_data['filter_id']}'>";
                $output .= "<h4>" . esc_html( $filter_data['label'] ) . "</h4>";

                if ( empty( $filter_data['options'] ) ) {
                    $output .= "<p>" . esc_html__( 'No options for this filter.', 'woocommerce-filter-presets' ) . "</p>";
                    $output .= "</div>"; // close woofp-filter-block
                    continue;
                }

                // Render options based on render_type (simplified for now)
                $output .= "<ul>";
                if ($filter_data['type'] === 'price' && $filter_data['render_type'] === 'price_slider') {
                     $output .= "<li>" . sprintf( esc_html__( 'Price Slider (Min: %s, Max: %s)', 'woocommerce-filter-presets' ), $filter_data['options']['min'], $filter_data['options']['max'] ) . "</li>";
                } else {
                    foreach ($filter_data['options'] as $option) {
                        $output .= "<li>";
                        $option_id = "woofp-option-{$filter_data['filter_id']}-{$option['value']}";
                        $output .= "<input type='checkbox' id='{$option_id}' name='filter_{$filter_data['filter_id']}[]' value='{$option['value']}'" . ($option['disabled'] ? ' disabled' : '') . "> ";
                        $output .= "<label for='{$option_id}'>" . esc_html( $option['label'] ) . " ({$option['count']})</label>";
                        if(!empty($option['swatch'])) {
                            $output .= " <span style='display:inline-block;width:15px;height:15px;background-color:{$option['swatch']};border:1px solid #ccc;'></span>";
                        }
                        $output .= "</li>";
                    }
                }
                $output .= "</ul>";
                $output .= "</div>"; // close woofp-filter-block
            }
            $output .= "<button type='submit' class='woofp-apply-filters'>" . esc_html__( 'Apply Filters', 'woocommerce-filter-presets' ) . "</button>";
            $output .= "<button type='reset' class='woofp-reset-filters'>" . esc_html__( 'Reset All', 'woocommerce-filter-presets' ) . "</button>";
            $output .= "</form>";
        }
        $output .= "</div>"; // close woocommerce-filter-presets-shortcode-wrap

        return $output;
    }
}

// Example of how it might be registered (conceptual)
// if ( class_exists( 'WooFilterPreset_Shortcode_Handler' ) ) {
//     WooFilterPreset_Shortcode_Handler::register();
// }
?>
