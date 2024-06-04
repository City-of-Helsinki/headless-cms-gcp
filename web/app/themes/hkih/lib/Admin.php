<?php
/**
 * This file controls what modifications are done to the site admin.
 */

namespace Geniem\Theme;

/**
 * Define the controller class.
 */
class Admin implements Interfaces\Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        // Add current post template to body class.
        \add_action(
            'admin_body_class',
            \Closure::fromCallable( [ $this, 'add_template_slug_to_body_class' ] )
        );

        // Disabling editor full screen mode by default.
        \add_action(
            'enqueue_block_editor_assets',
            \Closure::fromCallable( [ $this, 'disable_editor_fullscreen_by_default' ] )
        );

        // Remove FileBird Pro review begging nag screen, if it exists.
        if (
            class_exists( \FileBird\Classes\Review::class ) &&
            method_exists( \FileBird\Classes\Review::class, 'getInstance' )
        ) {
            \remove_action(
                'admin_notices',
                [ \FileBird\Classes\Review::getInstance(), 'give_review' ]
            );
        }

        // Replace default upload directory.
        \add_filter( 'upload_dir', function ( array $uploads ) {
            $replace = '/sites/' . get_current_blog_id();

            $uploads['path']    = str_replace( $replace, '', $uploads['path'] );
            $uploads['url']     = str_replace( $replace, '', $uploads['url'] );
            $uploads['basedir'] = str_replace( $replace, '', $uploads['basedir'] );
            $uploads['baseurl'] = str_replace( $replace, '', $uploads['baseurl'] );

            return $uploads;
        });
    }

    /**
     * Disable editor full screen mode by default.
     */
    private function disable_editor_fullscreen_by_default() {

        $script = "
window.onload = function() {
    if ( wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ) ) {
        wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' );
    }
}";

        \wp_add_inline_script( 'wp-blocks', $script );
    }

    /**
     * This adds a class to the body class list. The class is determined
     * by the template of the edited page. Only for pages.
     *
     * @param string $classes The original body class string.
     *
     * @return string $class The possibly modified body class string.
     */
    private function add_template_slug_to_body_class( $classes = '' ) {

        // Global object containing current admin page
        global $pagenow;

        // We should check against nonce, but we wont, so ignore recommendation.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( 'post.php' === $pagenow && ! empty( $_GET['post'] ) ) {

            $id   = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
            $type = \get_post_type( $id );

            if ( $type === 'page' ) {

                $template = \get_page_template_slug( $id );

                // If template is empty, we are editing the default template.
                $file_name = 'page-default';

                // If not empty use the template name in the class string.
                if ( $template !== '' ) {
                    $file_name_with_suffix = substr( $template, ( strpos( $template, '/' ) + 1 ) );
                    $file_name             = substr( $file_name_with_suffix, 0, strpos( $file_name_with_suffix, '.' ) );
                }

                $classes .= " geniem-${file_name}";
            }
        }

        return $classes;
    }
}
