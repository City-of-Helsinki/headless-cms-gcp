<?php
/**
 * Plugin Name: HKIH CPT Landing Page
 * Description: Landing page post type
 * Version: 1.0.0
 * Author: Geniem Oy
 * Author URI: https://geniem.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: hkih-cpt-landing-page
 * Domain Path: /languages
 */

use HKIH\CPT\LandingPage\LandingPagePlugin;

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
LandingPagePlugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'hkih_cpt_landing_page_plugin' ) ) {

    /**
     * Get the plugin instance.
     *
     * @return LandingPagePlugin
     */
    function hkih_cpt_landing_page_plugin() : LandingPagePlugin {
        return LandingPagePlugin::plugin();
    }
}
