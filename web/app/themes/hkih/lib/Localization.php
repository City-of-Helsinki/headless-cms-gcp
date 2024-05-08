<?php
/**
 * Localization related functionality
 */

namespace Geniem\Theme;

use PLL_Language;
use WP_Post;
use function pll_current_language;
use function pll_default_language;

/**
 * Class Localization
 *
 * @package Geniem\Theme
 */
class Localization implements Interfaces\Controller {

    /**
     * Hooks
     */
    public function hooks() : void {
        add_action( 'save_post', [ $this, 'sync_post_status' ], 10, 2 );

        add_filter(
            'pll_get_post_types',
            \Closure::fromCallable( [ $this, 'add_to_polylang' ] )
        );
    }

    /**
     * This returns the current language either by using
     * PLL or WP's locale.
     *
     * @return string|bool The language slug or false if used in
     *                     admin and 'all languages' are chosen from PLL top bar filter.
     */
    public static function get_current_language() {
        if ( function_exists( 'pll_current_language' ) ) {
            return pll_current_language() ?? get_locale();
        }

        return get_locale();
    }

    /**
     * Get default language.
     * Returns Polylang's default language or current WP locale.
     *
     * @return bool|PLL_Language|string
     */
    public static function get_default_language() {
        if ( function_exists( 'pll_default_language' ) ) {
            return pll_default_language() ?? get_locale();
        }

        return get_locale();
    }

    /**
     * Keep post status in sync between translations
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Instance of WP_Post.
     */
    public function sync_post_status( int $post_id, WP_Post $post ) {
        remove_action( 'save_post', [ $this, 'sync_post_status' ], 10 );

        if ( ! function_exists( 'pll_get_post_language' ) ) {
            return;
        }

        $post_lang = pll_get_post_language( $post_id );
        $languages = pll_languages_list();
        $languages = array_filter( $languages, fn( $lang ) => $lang !== $post_lang );

        // maybe unset autodescription data in order to prevent
        // seo metadata from being synced
        if ( ! empty( $_POST['autodescription'] ) ) { // phpcs:ignore
            unset( $_POST['autodescription'] ); // phpcs:ignore
        }

        foreach ( $languages as $lang ) {
            $translation = pll_get_post( $post_id, $lang );

            if ( $translation ) {
                wp_update_post( [
                    'ID'          => $translation,
                    'post_status' => $post->post_status,
                ] );
            }
        }
    }

    /**
     * Add the CPTs to Polylang translation.
     *
     * @param array $post_types The post type array.
     *
     * @return array The modified post_types array.
     */
    protected function add_to_polylang( array $post_types ) {
        $post_types[ Settings::POST_TYPE ] = Settings::POST_TYPE;

        return $post_types;
    }
}
