<?php
/**
 * Plugin Name: HKIH CPT Collection
 * Description: Collection Post Type
 * Version: 1.0.0
 * Author: Geniem Oy
 * Author URI: https://geniem.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: hkih-cpt-collection
 * Domain Path: /languages
 */

use HKIH\CPT\Collection\CollectionPlugin;

// Check if Composer has been initialized in this directory.
// Otherwise we just use global composer autoloading.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Get the plugin version.
$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' );
$plugin_version = $plugin_data['Version'];

$plugin_path = __DIR__;

// Initialize the plugin.
CollectionPlugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'hkih_cpt_collection_plugin' ) ) {

    /**
     * Get the plugin instance.
     *
     * @return CollectionPlugin
     */
    function hkih_cpt_collection_plugin() : CollectionPlugin {
        return CollectionPlugin::plugin();
    }
}
