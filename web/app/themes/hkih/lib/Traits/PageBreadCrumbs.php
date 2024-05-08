<?php
/**
 * This trait controls getting breadcrumbs on pages.
 */

namespace Geniem\Theme\Traits;

use \Geniem\Theme\Settings;
use \Geniem\Theme\PostType;
use \Geniem\Theme\Localization;

/**
 * Trait PageBreadCrumbs
 *
 * @package Geniem\Theme\Traits
 */
trait PageBreadCrumbs {

    /**
     * This gets the breadcrumbs for a given page id. If
     * there's more than two ancestors, a '...' separator is added before
     * the front page link.
     *
     * @param int $post_id Current page ID.
     *
     * @return array Breadcrumbs or an empty array.
     */
    protected function page_breadcrumbs( $post_id ) {

        // Bail early if no ID was given.
        if ( empty( $post_id ) ) {
            return [];
        }

        $lang         = \pll_get_post_language( $post_id );
        $frontpage_id = \pll_get_post( \get_option('page_on_front'), $lang );
        $hidden_pages = [];

        if ( \is_page( $post_id ) ) {
            $hidden_pages = Settings::get_setting( 'hidden_pages', $lang ) ?? [];
        }

        // Set frontpage as first element in to breadcrumbs.
        $breadcrumbs = [
            [
                'title' => html_entity_decode( \get_the_title( $frontpage_id ) ),
                'uri'   => self::get_path( \pll_home_url( $lang ) ),
            ],
        ];

        $ancestors = \get_ancestors( $post_id, PostType\Page::SLUG );

        // Flip the array so that it's easier to print in the template.
        $ancestors = array_reverse( $ancestors );

        /**
         * Add all page ancestors to breadcrumbs.
         */
        foreach ( $ancestors as $ancestor ) {
            // Skip hidden pages.
            if ( in_array( $ancestor, $hidden_pages ) ) {
                continue;
            }

            $breadcrumbs[] = [
                'title' => html_entity_decode( \get_the_title( $ancestor ) ),
                'uri'   => self::get_path( \get_permalink( $ancestor ) ),
            ];
        }

        unset( $ancestor );

        /**
         * Add current page.
         */
        $breadcrumbs[] = [
            'title' => html_entity_decode( \get_the_title( $post_id ) ),
            'uri'   => self::get_path( \get_permalink( $post_id ) ),
        ];

        return $breadcrumbs;
    }

    /**
     *
     * Returns path of the url.
     *
     * @param string $url Url.
     *
     * @return string Path of the url.
     */
    private static function get_path( $url ) {
        $path = parse_url( $url, PHP_URL_PATH );

        if ( empty( $path ) ) {
            return '';
        }

        return $path;
    }
}
