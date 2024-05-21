<?php
/**
 * Theme TinyMCEController settings file.
 */

namespace Geniem\Theme;

/**
 * Class TinyMCEController
 *
 * This class controls theme image handling.
 *
 * @package Geniem\Theme
 */
class TinyMCEController implements Interfaces\Controller {

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        \add_filter( 'tiny_mce_before_init', \Closure::fromCallable( [ $this,  'tinymce_customizations' ] ) );
        \add_filter( 'acf/fields/wysiwyg/toolbars' , \Closure::fromCallable( [ $this, 'register_light_toolbar' ] ) );
        \add_filter( 'acf/fields/wysiwyg/toolbars' , \Closure::fromCallable( [ $this, 'register_toolbars_fields' ] ) );
        \add_filter( 'mce_external_plugins', \Closure::fromCallable( [ $this, 'register_tinymce_table_plugin' ] ) );
        \add_action( 'admin_head', \Closure::fromCallable( [ $this, 'light_toolbar_styles' ] ) );
        \add_theme_support( 'editor-styles' );
        \add_editor_style( \get_template_directory_uri() . '/assets/stylesheets/editor-style.css' );
    }

    /**
     * TinyMCE customizations
     *
     * @param array $init Formats init.
     *
     * @return mixed
     */
    public static function tinymce_customizations( $init ) {
        // Add custom style formats to the editor.
        $style_formats = [
            [
                'title'   => __( 'Painike', 'hkih' ),
                'block'   => 'a',
                'classes' => 'wp-block-button__link wp-element-button',
                'wrapper' => true,
            ],
        ];

        $init['style_formats'] = \wp_json_encode( $style_formats );

        return $init;
    }

    /**
     * Register new Light ACF Wysiwyg toolbar.
     *
     * @param array $toolbars Toolbar settings.
     *
     * @return array
     */
    public static function register_light_toolbar( $toolbars ) {
        $toolbars['Light'][1][] = 'bold';
        $toolbars['Light'][1][] = 'italic';
        $toolbars['Light'][1][] = 'link';

        return $toolbars;
    }

    /**
     * Makes Light toolbar look more like input field.
     *
     * @return string
     */
    public static function light_toolbar_styles() {

        echo '<style>
                [data-name="step_title"] {
                    min-height: auto !important;
                }
                [data-toolbar="light"] div.mce-edit-area iframe  {
                    height: 50px !important;
                    min-height: 50px;
                }
            </style>';
    }

    /**
     * ACF Wysiwyg field customizations.
     *
     * @param array $toolbars Toolbar settings.
     *
     * @return array
     */
    public static function register_toolbars_fields( $toolbars ) {
        $toolbars['Basic'][1][] = 'separator';
        $toolbars['Basic'][1][] = 'styleselect';
        $toolbars['Basic'][1][] = 'separator';
        $toolbars['Basic'][1][] = 'table';

        return $toolbars;
    }

    /**
     * Add table plugin library.
     *
     * @return array
     */
    private function register_tinymce_table_plugin( $plugin_array ) {
        $plugin_array['table'] = \get_template_directory_uri() . '/assets/tinymce-plugins/table/plugin.min.js';

        return $plugin_array;
    }
}
