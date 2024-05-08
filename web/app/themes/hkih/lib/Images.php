<?php
/**
 * Theme image settings file.
 */

namespace Geniem\Theme;

/**
 * Class Images
 *
 * This class controls theme image handling.
 *
 * @package Geniem\Theme
 */
class Images implements Interfaces\Controller {

    /**
     * Update this version number if you need to update image sizes.
     *
     * @var integer
     */
    private const VERSION = '1';

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        \add_action( 'after_setup_theme', \Closure::fromCallable( [ $this, 'image_sizes' ] ) );
        \add_filter( 'intermediate_image_sizes', \Closure::fromCallable( [ $this, 'filter_sizes' ] ) );
    }

    /**
     * Add and update image sizes for theme images.
     *
     * @return void
     */
    private function image_sizes() : void {
        $version_from_db = \get_option( 'images_version_number' );

        // Only update options if version is changed manually.
        // This prevents unnecessary database queries.
        if ( $version_from_db !== self::VERSION ) {

            // Update version number with new value.
            \update_option( 'images_version_number', self::VERSION );

            // Update thumbnail size.
            \update_option( 'thumbnail_size_w', 150 );
            \update_option( 'thumbnail_size_h', 150 );
            \update_option( 'thumbnail_crop', 1 );

            // Update medium size.
            \update_option( 'medium_size_w', 320 );
            \update_option( 'medium_size_h', 9999 );

            // Update medium_large size.
            \update_option( 'medium_large_size_w', 768 );
            \update_option( 'medium_large_size_h', 9999 );

            // Update large size.
            \update_option( 'large_size_w', 1024 );
            \update_option( 'large_size_h', 9999 );
        }

        // Add custom mini image size.
        \add_image_size( 'mini', 70, 70, true );

        // Add custom xlarge image size.
        \add_image_size( 'xlarge', 1600, 9999 );

        // Add custom full-hd image size.
        \add_image_size( 'fullhd', 1920, 9999 );

        // Remove these lines if you want to use the new WP default image sizes.
        \remove_image_size( '1536x1536' );
        \remove_image_size( '2048x2048' );
    }

    /**
     * This filters out unnecessary image sizes.
     *
     * @param array $sizes The filterable sizes array.
     *
     * @return array The filtered sizes array.
     */
    private function filter_sizes( $sizes = [] ) : array {
        // If you want to use the new WP default image sizes, add them to this array.
        $sizes = [ 'thumbnail', 'medium', 'medium_large', 'large', 'xlarge', 'fullhd' ];

        return $sizes;
    }
}
