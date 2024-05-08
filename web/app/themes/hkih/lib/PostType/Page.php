<?php
/**
 * The class representation of the WordPress default post type 'page'.
 */

namespace Geniem\Theme\PostType;

use \Geniem\Theme\Interfaces\PostType;
use Geniem\Theme\Utils;
use Geniem\Theme\Traits\PageBreadCrumbs;
use WPGraphQL\Model\Post as GraphQlPost;

/**
 * This class defines the post type.
 *
 * @package Geniem\Theme\PostType
 */
class Page implements PostType {

    use PageBreadCrumbs;

    /**
     * This defines the slug of this post type.
     */
    const SLUG = 'page';

    /**
     * Filter name to use when getting modules.
     */
    const REST_MODULES_FILTER = 'hkih_rest_acf_page_modules_layout';

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action(
            'init',
            \Closure::fromCallable( [ $this, 'modify_page_supports' ] ),
            15
        );

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_types' ] )
        );

        add_action(
            'rest_api_init',
            \Closure::fromCallable( [ $this, 'register_rest_fields' ] )
        );
    }

    /**
     * Modify page supported.
     *
     * @return void
     */
    private function modify_page_supports() : void {
        \remove_post_type_support( self::SLUG, 'comments' );
        \remove_post_type_support( self::SLUG, 'trackbacks' );
    }

    /**
     * Registers all GraphQL Fields.
     *
     * @return void
     */
    private function register_graphql_types() : void {
        // ACF Field Lead
        \register_graphql_field( self::SLUG, 'lead', [
            'type'        => 'String',
            'description' => __( 'Lead Paragraph', 'hkih' ),
            'resolve'     => fn( GraphQlPost $post ) => get_field( 'lead', $post->ID ) ?? '',
        ] );

        \register_graphql_field( self::SLUG, 'showChildPages', [
            'type'        => 'Boolean',
            'description' => __( 'Show Child Pages', 'hkih' ),
            'resolve'     => fn( GraphQlPost $post ) => get_field( 'show_child_pages', $post->ID ) ?? false,
        ] );

        \register_graphql_object_type( 'Hero', [
            'description' => __( 'Hero field', 'hkih' ),
            'fields'      => [
                'title' => [
                    'type'        => 'String',
                    'description' => __( 'The title of the hero', 'hkih' ),
                ],
                'description' => [
                    'type'        => 'String',
                    'description' => __( 'The desctiption of the hero', 'hkih' ),
                ],
                'background_color' => [
                    'type'        => 'String',
                    'description' => __( 'The background color of the hero', 'hkih' ),
                ],
                'background_image_url' => [
                    'type'        => 'String',
                    'description' => __( 'The background color of the hero', 'hkih' ),
                ],
                'link' => [
                    'type'        => 'Link',
                    'description' => __( 'The title of the hero link', 'hkih' ),
                ],
                'wave_motif' => [
                    'type'        => 'String',
                    'description' => __( 'The wave motif of the hero', 'hkih' ),
                ],
            ],
        ] );

        \register_graphql_field( self::SLUG, 'hero', [
            'type'        => 'Hero',
            'description' => __( 'Hero fields', 'hkih' ),
            'resolve'     => function( GraphQlPost $post ) {
                return [
                    'title'                => \get_field( 'hero_title', $post->ID ) ?? false,
                    'description'          => \get_field( 'hero_description', $post->ID ) ?? false,
                    'background_color'     => \get_field( 'hero_bg_color', $post->ID ) ?? false,
                    'background_image_url' => \get_the_post_thumbnail_url( $post->ID, 'full' ),
                    'link'                 => \get_field( 'hero_link', $post->ID ) ?? false,
                    'wave_motif'           => \get_field( 'hero_wave_motif', $post->ID ) ?? false,
                ];
            },
        ] );

        \register_graphql_object_type( 'Breadcrumb', [
            'description' => __( 'Breadcumb field', 'hkih' ),
            'fields'      => [
                'title' => [
                    'type'        => 'String',
                    'description' => __( 'The title of the page', 'hkih' ),
                ],
                'uri' => [
                    'type'        => 'String',
                    'description' => __( 'The link of the page.', 'hkih' ),
                ],
            ],
        ] );

        \register_graphql_field( self::SLUG, 'breadcrumbs', [
            'type'        => [ 'list_of' => 'Breadcrumb' ],
            'description' => __( 'Breadcrumb fields', 'hkih' ),
            'resolve'     => function( GraphQlPost $post ) {
                return $this->page_breadcrumbs( $post->ID );
            },
        ] );

        try {
            $graphql_modules = \apply_filters( 'hkih_posttype_page_graphql_modules', [] );
            $graphql_layouts = \apply_filters( 'hkih_posttype_page_graphql_layouts', [] );

            Utils::register_modules_and_union_types(
                self::SLUG,
                $graphql_modules,
                $graphql_layouts,
                fn( $post ) => Utils::get_modules( $post->ID, self::REST_MODULES_FILTER )
            );

            $graphql_sidebar_modules = \apply_filters( 'hkih_posttype_page_sidebar_graphql_modules', [] );
            $graphql_sidebar_layouts = \apply_filters( 'hkih_posttype_page_sidebar_graphql_layouts', [] );

            Utils::register_modules_and_union_types(
                self::SLUG,
                $graphql_sidebar_modules,
                $graphql_sidebar_layouts,
                fn( $post ) => Utils::get_modules( $post->ID, self::REST_MODULES_FILTER, 'sidebar' ),
                'sidebar'
            );
        }
        catch ( \Exception $e ) {
            ( new \Geniem\Theme\Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() {
        register_rest_field(
            [ self::SLUG ],
            'lead',
            [
                'get_callback' => fn( $object ) => get_field( 'lead', $object['id'] ),
            ]
        );

        register_rest_field(
            [ self::SLUG ],
            'show_child_pages',
            [
                'get_callback' => fn( $object ) => get_field( 'show_child_pages', $object['id'] ) ?? false,
            ]
        );

        register_rest_field(
            [ self::SLUG ],
            'modules',
            [
                'get_callback' => fn( $object ) => Utils::get_modules( $object['id'], self::REST_MODULES_FILTER ),
            ]
        );
    }
}
