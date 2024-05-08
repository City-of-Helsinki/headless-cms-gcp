<?php
/**
 * Plugin Name: HKIH LinkedEvents
 * Plugin URI: https://github.com/devgeniem/wp-plugin-boilerplate
 * Description: HKIH LinkedEvents functionality
 * Version: 1.0.0
 * Requires PHP: 7.0
 * Author: Geniem Oy
 * Author URI: https://geniem.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: hkih-linked-events
 * Domain Path: /languages
 */

use HKIH\LinkedEvents\LinkedEventsPlugin;

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
LinkedEventsPlugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'hkih_linked_events' ) ) {
    /**
     * Get the {{plugin-name}} plugin instance.
     *
     * @return LinkedEventsPlugin
     */
    function hkih_linked_events() : LinkedEventsPlugin {
        return LinkedEventsPlugin::plugin();
    }
}
