<?php
// File: includes/class-individual-filter.php

if ( ! defined( 'ABSPATH' ) ) { // Or WPINC
    // exit; // Exit if accessed directly.
}

/**
 * Conceptual class to represent an Individual Filter within a Preset.
 */
class WooIndividualFilter {
    /**
     * @var string Filter ID (unique within its preset).
     */
    public $id;

    /**
     * @var string Filter Type (e.g., 'category', 'attribute', 'price').
     */
    public $type;

    /**
     * @var string Data source (e.g., 'product_cat', 'pa_color', '_price').
     */
    public $source;

    /**
     * @var string Admin label for the filter.
     */
    public $admin_label;

    /**
     * @var string Frontend label/title for the filter block.
     */
    public $frontend_label;

    /**
     * @var string How to render options (e.g., 'checkboxes', 'radio_buttons', 'select').
     */
    public $render_type;

    /**
     * @var string Option ordering (e.g., 'alphabetical', 'term_order', 'product_count').
     */
    public $option_ordering;

    /**
     * @var bool Show hierarchy (for hierarchical taxonomies).
     */
    public $show_hierarchy;

    /**
     * @var bool Show product counts next to options.
     */
    public $show_product_counts;

    /**
     * @var string Behavior for unavailable terms ('hide', 'grey_out').
     */
    public $unavailable_terms_behavior;

    /**
     * @var bool Allow multiple selections.
     */
    public $multi_select;

    /**
     * @var string Relation for multi-select ('AND', 'OR').
     */
    public $multi_select_relation; // 'AND' or 'OR'

    /**
     * @var bool Is the filter block toggled open by default.
     */
    public $default_open;

    /**
     * @var array Specific settings for this filter type.
     *            e.g., for color_swatches: ['swatch_shape' => 'square', 'swatch_size' => '30px']
     *            e.g., for price_slider: ['min_price' => 0, 'max_price' => 1000, 'step' => 10]
     */
    public $settings;

    /**
     * Constructor.
     *
     * @param string $id          Unique ID for the filter.
     * @param string $type        Filter type.
     * @param string $source      Data source.
     * @param string $admin_label Admin label.
     */
    public function __construct( $id = '', $type = '', $source = '', $admin_label = '' ) {
        $this->id            = $id ?: uniqid('filter_');
        $this->type          = $type;
        $this->source        = $source;
        $this->admin_label   = $admin_label ?: ucfirst(str_replace('_', ' ', $type));
        $this->frontend_label = $this->admin_label; // Default frontend label to admin label

        // Default values
        $this->render_type      = 'checkboxes';
        $this->option_ordering  = 'term_order';
        $this->show_hierarchy   = true;
        $this->show_product_counts = true;
        $this->unavailable_terms_behavior = 'grey_out';
        $this->multi_select     = false;
        $this->multi_select_relation = 'OR';
        $this->default_open     = true;
        $this->settings         = array();
    }

    /**
     * Sets a specific setting for the filter.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     */
    public function set_setting( $key, $value ) {
        $this->settings[ $key ] = $value;
    }

    /**
     * Gets a specific setting for the filter.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value if not set.
     * @return mixed Setting value.
     */
    public function get_setting( $key, $default = null ) {
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
    }
}
?>
