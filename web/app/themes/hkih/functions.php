<?php
/**
 * Load the theme functionalities.
 */

// Require theme library autoloader.
require_once __DIR__ . '/lib/autoload.php';

if ( WP_ENV === 'development' ) {
    /**
     * Notice level errors trigger Whoops, which is bad because
     * usually the triggering code lives inside plugins.
     */
    error_reporting( E_ALL & ~E_NOTICE & ~E_USER_DEPRECATED & ~E_USER_NOTICE ); // phpcs:ignore
}

// Theme setup
Geniem\Theme\ThemeController::instance();

/**
 * Global helper function to fetch the ThemeController instance
 *
 * @return Geniem\Theme\ThemeController
 */
function theme_controller() : \Geniem\Theme\ThemeController {
    return Geniem\Theme\ThemeController::instance();
}
