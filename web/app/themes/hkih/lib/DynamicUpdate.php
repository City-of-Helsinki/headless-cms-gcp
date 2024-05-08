<?php
/**
 * Dynamic update functionality
 */

namespace Geniem\Theme;
use Geniem\Theme\PostType\Page;
use Geniem\Theme\PostType\Post;
use Geniem\Theme\Localization;
use Geniem\Theme\Settings;

/**
 * Class DynamicUpdate
 *
 * @package Geniem\Theme
 */
class DynamicUpdate implements Interfaces\Controller {

    /**
     * Hooks
     */
    public function hooks() : void {
        \add_filter( 'save_post', [ $this, 'revalidation_request' ], 10, 3 );
    }

    /**
     * Revalidation request.
     *
     * @param $post_id int Post ID.
     * @param $post WP_Post Post object.
     * @param $update bool Whether this is an existing post being updated.
     *
     * @return void
     */
    public function revalidation_request(int $post_id, \WP_Post $post, bool $update) {
        // Bail early.
        if ( ! in_array( $post->post_type, [ Page::SLUG, Post::SLUG ] ) ) {
            return;
        }

        // Bail early on auto-draft
        if ( $post->post_status === 'auto-draft' ) {
            return;
        }

        // Bail early on autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Bail early on revision
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $current_page_id = \get_the_id();

        // Polylang triggers all languages.
        if ( $current_page_id !== $post->ID ) {
            return;
        }

        $urls = self::get_revalidation_urls_to_run();

        if ( empty( $urls ) ) {
            return;
        }

        // Modify permalink to uri without trailing slash.
        $parsed_url = parse_url( \get_permalink( $current_page_id ) );
        $uri        = rtrim( $parsed_url['path'], '/' );

        foreach ( $urls as $url ) {
            $token = self::get_revalidation_token_by_enviroment();

            if ( empty ( $token ) ) {
                ( new Logger() )->debug( 'No token found for ' . \get_site_url(), true );
                continue;
            }

            $args = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => \wp_json_encode( [
                    'secret' => $token,
                    'uri'    => $uri,
                ] ),
            ];

            $response = \wp_remote_post( $url, $args );

            if ( 200 !== \wp_remote_retrieve_response_code( $response ) ) {
                ( new Logger() )->error( print_r( $response, true ) ); // phpcs:ignore

                continue;
            }

            $body = \json_decode( wp_remote_retrieve_body( $response ) );

            ( new Logger() )->debug( print_r( $body, true ) );
        }
    }

    /**
     * Get revalidation urls from theme settings.
     *
     * @return array
     */
    public static function get_revalidation_urls_to_run() {
        $urls_to_run = [];
        $lang        = Localization::get_default_language();

        if ( in_array( WP_ENV, [ 'development', 'staging' ] ) ) {
            $urls_to_run[] = Settings::get_setting( 'revalidate_url_testing', $lang ) ?? '';
        }

        if ( in_array( WP_ENV, [ 'production' ] ) ) {
            $urls_to_run[] = Settings::get_setting( 'revalidate_url_production', $lang ) ?? '';
            $urls_to_run[] = Settings::get_setting( 'revalidate_url_staging', $lang ) ?? '';
        }

        $urls_to_run = array_filter( $urls_to_run );

        return $urls_to_run;
    }

    /**
     * Get revalidation token by enviroment.
     *
     * @return string
     */
    public static function get_revalidation_token_by_enviroment() {

        $enviroments = [
            'https://liikunta.client-hkih.test'       => REVALIDATE_TOKEN_TESTING,
            'https://liikunta.hkih.stage.geniem.io'   => REVALIDATE_TOKEN_TESTING,
            'https://liikunta.content.api.hel.fi'     => REVALIDATE_TOKEN_PRODUCTION_LIIKUNTA,
            'https://liikunta2.content.api.hel.fi'    => REVALIDATE_TOKEN_PRODUCTION_LIIKUNTA,
            'https://tapahtumat.client-hkih.test'     => REVALIDATE_TOKEN_TESTING,
            'https://tapahtumat.hkih.stage.geniem.io' => REVALIDATE_TOKEN_TESTING,
            'https://tapahtumat.content.api.hel.fi'   => REVALIDATE_TOKEN_PRODUCTION_TAPAHTUMAT,
            'https://harrastus.client-hkih.test'      => REVALIDATE_TOKEN_TESTING,
            'https://harrastus.hkih.stage.geniem.io'  => REVALIDATE_TOKEN_TESTING,
            'https://harrastus.content.api.hel.fi'    => REVALIDATE_TOKEN_PRODUCTION_HARRASTUKSET,
        ];

        return $enviroments[ \get_site_url() ] ?? '';
    }
}
