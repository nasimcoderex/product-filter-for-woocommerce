<?php
// File: includes/class-filter-preset.php

if ( ! defined( 'ABSPATH' ) ) { // Or WPINC if ABSPATH is not defined yet
    // exit; // Exit if accessed directly.
}

/**
 * Conceptual class to represent a Filter Preset.
 * In a real WordPress plugin, this might map to a Custom Post Type.
 */
class WooFilterPreset {
    /**
     * @var int Preset ID.
     */
    public $id;

    /**
     * @var string Preset Name.
     */
    public $name;

    /**
     * @var array Array of WooIndividualFilter objects/arrays.
     */
    public $filters;

    /**
     * @var array Global settings for this preset.
     *            e.g., ['ajax_enabled' => true, 'permalink_mode' => 'default', 'lazy_load' => false]
     */
    public $global_settings;

    /**
     * Constructor.
     *
     * @param int    $id   Preset ID.
     * @param string $name Preset Name.
     */
    public function __construct( $id = 0, $name = '' ) {
        $this->id = $id;
        $this->name = $name;
        $this->filters = array();
        $this->global_settings = array(
            'ajax_enabled'   => true,
            'permalink_mode' => 'default', // 'default', 'append_terms', 'pretty'
            'lazy_load'      => false,
            'custom_loader_url' => '' // URL to a custom AJAX loader image
        );
    }

    /**
     * Adds an individual filter to this preset.
     *
     * @param WooIndividualFilter $filter The filter to add.
     */
    public function add_filter( $filter ) {
        // In a real scenario, ensure $filter is of the correct type.
        $this->filters[] = $filter;
    }

    /**
     * Sets a global setting for the preset.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     */
    public function set_global_setting( $key, $value ) {
        $this->global_settings[ $key ] = $value;
    }

    /**
     * Gets a global setting for the preset.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value if not set.
     * @return mixed Setting value.
     */
    public function get_global_setting( $key, $default = null ) {
        return isset( $this->global_settings[ $key ] ) ? $this->global_settings[ $key ] : $default;
    }
}
?>
