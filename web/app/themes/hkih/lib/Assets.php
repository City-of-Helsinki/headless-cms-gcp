<?php
/**
 * Theme setupping.
 */

namespace Geniem\Theme;

use \Geniem\Theme\PostType\Settings;

/**
 * Class Assets
 *
 * This class sets up the theme assets.
 */
class Assets implements Interfaces\Controller {

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        \add_action( 'enqueue_block_editor_assets', \Closure::fromCallable( [ $this, 'editor' ] ) );
        \add_action( 'admin_enqueue_scripts', \Closure::fromCallable( [ $this, 'enqueue_admin_scripts' ] ) );
        \add_action( 'admin_enqueue_scripts', \Closure::fromCallable( [ $this, 'enqueue_admin_styles' ] ) );
        \add_action( 'admin_enqueue_scripts', \Closure::fromCallable( [ $this, 'enqueue_css_editor' ] ) );
        \add_action( 'admin_head', \Closure::fromCallable( [ $this, 'custom_admin_css' ] ) );
    }


    /**
     * This adds assets (JS and CSS) to gutenberg in admin.
     *
     * @return void
     */
    public static function editor() {
        $js_mod_time = static::get_theme_asset_mod_time( 'editor.js' );

        \wp_enqueue_script(
            'editor-js',
            get_stylesheet_directory_uri() . '/assets/dist/editor.js',
            [
                'jquery',
                'wp-i18n',
                'wp-blocks',
                'wp-dom-ready',
                'wp-edit-post',
            ],
            $js_mod_time,
            true
        );
    }

    /**
     * Enqueue admin scripts
     *
     * @return void
     */
    public static function enqueue_admin_scripts() :void {
        global $post;

        $js_mod_time = static::get_theme_asset_mod_time( 'admin.js' );

        \wp_register_script(
            'admin-js',
            get_stylesheet_directory_uri() . '/assets/dist/admin.js',
            [ 'jquery', 'lodash' ],
            $js_mod_time,
            true
        );

        $client_url = \Geniem\Theme\Settings::get_setting( 'client_url' ) ?? null;
        $post_name  = $post->post_name ?? null;

        $external_url = empty( $client_url ) || empty( $post_name )
                        ? null
                        : trailingslashit( $client_url ) . $post_name;

        $search_results_link = \Geniem\Theme\Settings::get_setting( 'event_search_carousel_search_url' ) ?? null;
        $search_results_link = ! empty( $search_results_link )
                              ? trailingslashit( $search_results_link )
                              : $search_results_link;

        $data = [
            'externalUrl'       => $external_url,
            'searchResultsLink' => $search_results_link,
        ];

        \wp_localize_script( 'admin-js', 'adminData', $data );
        \wp_enqueue_script( 'admin-js' );
    }

    /**
     * Enqueue CSS-editor
     *
     * @return void
     */
    public static function enqueue_css_editor() :void {

        // Bail early.
        if( Settings::SLUG !== \get_post_type() ) {
            return;
        }

        // CSS-field in admin settings.
        $settings = \wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

        // Return if the editor was not enqueued.
        if ( $settings !== false ) {
            \wp_add_inline_script(
                'code-editor',
                sprintf(
                    'jQuery( function() { wp.codeEditor.initialize( "acf-fg_site_settings_admin_css", %s ); } );',
                    \wp_json_encode( $settings )
                )
            );
        }
    }

    /**
     * Enqueue admin styles
     *
     * @return void
     */
    public static function enqueue_admin_styles() : void {

        $css_mod_time = static::get_theme_asset_mod_time( 'admin.css' );

        \wp_enqueue_style(
            'admin-css',
            \get_stylesheet_directory_uri() . '/assets/stylesheets/admin.css',
            [],
            $css_mod_time,
            'all'
        );
    }

    /**
     * This enables cache busting for theme CSS and JS files by
     * returning a microtime timestamp for the given files.
     * If the file is not found for some reason, it uses the theme version.
     *
     * @param string $filename The file to check.
     *
     * @return int|string A microtime amount or the theme version.
     */
    private static function get_theme_asset_mod_time( $filename = '' ) {
        return file_exists( get_stylesheet_directory() . '/assets/dist/' . $filename )
            ? filemtime( get_stylesheet_directory() . '/assets/dist/' . $filename )
            : time();
    }

    /**
     * This prints out custom css code to admin header.
     *
     * @return void
     */
    private static function custom_admin_css() {
        $admin_css = \Geniem\Theme\Settings::get_setting( 'admin_css' ) ?? null;

        if ( ! empty ( $admin_css ) ) {
            echo sprintf( '<style>%s</style>', \esc_html( $admin_css ) );
        }
    }
}
