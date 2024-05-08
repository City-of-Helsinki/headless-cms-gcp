<?php
/**
 * This file controls what the theme supports.
 */

namespace Geniem\Theme;

/**
 * Define the controller class.
 */
class ThemeSupports implements Interfaces\Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        // Make theme available for translation
        \load_theme_textdomain( 'hkih', get_template_directory() . '/lang' );

        // Add supported functionality.
        \add_action(
            'after_setup_theme',
            \Closure::fromCallable( [ $this, 'add_supported_functionality' ] )
        );

        \add_action(
            'after_setup_theme',
            \Closure::fromCallable( [ $this, 'register_navigations' ] )
        );

        \add_action(
            'after_setup_theme',
            \Closure::fromCallable( [ $this, 'set_error_levels' ] )
        );

        // Disable block patterns
        \remove_theme_support( 'core-block-patterns' );
    }

    /**
     * This adds all functionality.
     *
     * @return void
     */
    private function add_supported_functionality() : void {
        // Enable post thumbnails
        // http://codex.wordpress.org/Post_Thumbnails
        \add_theme_support( 'post-thumbnails' );
    }

    /**
     * If we are in development mode, and WP_DEBUG is on, silence stupid errors.
     *
     * @return void
     */
    private function set_error_levels() : void {
        if (
            defined( 'WP_ENV' ) && WP_ENV === 'development' &&
            defined( 'WP_DEBUG' ) && WP_DEBUG
        ) {
            error_reporting( E_ALL & ~E_NOTICE & ~E_USER_DEPRECATED & ~E_USER_NOTICE ); // phpcs:ignore
        }
    }

    /**
     * Register navigations
     */
    private function register_navigations() : void {
        register_nav_menu( 'primary', __( 'Primary navigation', 'hkih' ) );
        register_nav_menu( 'secondary', __( 'Secondary navigation', 'hkih' ) );
        register_nav_menu( 'tertiary', __( 'Tertiary navigation', 'hkih' ) );
    }
}
