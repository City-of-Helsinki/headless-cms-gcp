<?php
/**
 * This file controls what the theme supports.
 */

namespace Geniem\Theme;
use WPGraphQL\JWT_Authentication\Auth;
use \Geniem\Theme\Localization;

/**
 * Define the controller class.
 */
class PreviewController implements Interfaces\Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        \add_action(
            'wp_ajax_create_preview_request',
            \Closure::fromCallable( [ $this, 'create_preview_request' ] )
        );

        \add_filter( 'graphql_jwt_auth_expire',
            \Closure::fromCallable( [ $this, 'custom_jwt_expiration' ] ),
            10
        );
    }

    /**
     * This adds all functionality.
     *
     * @return void
     */
    private function create_preview_request() {
        if ( ! class_exists( '\WPGraphQL\JWT_Authentication\Auth' ) ) {
            return \wp_send_json_error( 'Activate WPGraphQL JWT Authentication plugin.' );
        }
        $post_id     = ( int ) $_GET['pll_post_id'];
        $uri         = self::get_permalink( $post_id );

        if ( empty( $uri ) ) {
            return \wp_send_json_error( 'Page not found.' );
        }

        $preview_url = \Geniem\Theme\Settings::get_setting( 'preview_url' , Localization::get_default_language() ) ?? '';

        if ( empty( $preview_url ) ) {
            return \wp_send_json_error( 'Preview url has not been set in the settings.' );
        }

        $preview_action = \add_query_arg( [
            'uri'    => $uri,
            'secret' => Auth::get_token( \wp_get_current_user() )
        ], $preview_url );

        $response = sprintf( '<form action="%s" method="post" target="_blank"></form>', $preview_action );

        return \wp_send_json_success( $response );
    }

    /**
     * Change Auth Token expiration.
     *
     * @param int $expiration Expiration time in seconds.
     * @return int
     */
    private function custom_jwt_expiration( $expiration ) : int {
        return MINUTE_IN_SECONDS * 10;
    }

    /**
     * Get permalink based on post status.
     *
     * @param int $post_id Post id.
     * @return string
     */
    private function get_permalink( $post_id ) : string {
        $post_status = \get_post_status( $post_id );
        $uri         = '';

        if ( $post_status === 'draft' ) {
            $sample_permalink = \get_sample_permalink( $post_id );
            $uri              = \str_replace( ['%postname%', '%pagename%'], $sample_permalink[1], $sample_permalink[0] );
        } else {
            $uri = \get_permalink( $post_id );
        }

        return parse_url( $uri, PHP_URL_PATH );
    }
}
