<?php
/**
 * The class representation of the WordPress default post type 'post'
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
class Post implements PostType {

    use PageBreadCrumbs;

    /**
     * This defines the slug of this post type.
     */
    const SLUG = 'post';

    /**
     * Filter name to use when getting modules.
     */
    const REST_MODULES_FILTER = 'hkih_rest_acf_post_modules_layout';

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action(
            'init',
            \Closure::fromCallable( [ $this, 'modify_post_supports' ] ),
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
     * Post type support
     */
    public function modify_post_supports() {
        remove_post_type_support( static::SLUG, 'excerpt' );
        remove_post_type_support( static::SLUG, 'comments' );
        remove_post_type_support( static::SLUG, 'trackbacks' );
    }

    /**
     * Registers all GraphQL Fields.
     *
     * @return void
     */
    private function register_graphql_types() : void {
        \register_graphql_field( self::SLUG, 'lead', [
            'type'        => 'String',
            'description' => __( 'Lead Paragraph', 'hkih' ),
            'resolve'     => function ( \WPGraphQL\Model\Post $post ) {
                return get_field( 'lead', $post->ID ) ?? '';
            },
        ] );

        \register_graphql_field( self::SLUG, 'hidePublishedDate', [
            'type'        => 'Boolean',
            'description' => __( 'Hide Published Date', 'hkih' ),
            'resolve'     => function ( \WPGraphQL\Model\Post $post ) {
                return get_field( 'hide_published_date', $post->ID ) ?? false;
            },
        ] );

        \register_graphql_field( self::SLUG, 'breadcrumbs', [
            'type'        => [ 'list_of' => 'Breadcrumb' ],
            'description' => __( 'Breadcrumb fields', 'hkih' ),
            'resolve'     => function( GraphQlPost $post ) {
                return $this->page_breadcrumbs( $post->ID );
            },
        ] );

        try {
            $graphql_modules = \apply_filters( 'hkih_posttype_post_graphql_modules', [] );
            $graphql_layouts = \apply_filters( 'hkih_posttype_post_graphql_layouts', [] );

            Utils::register_modules_and_union_types(
                self::SLUG,
                $graphql_modules,
                $graphql_layouts,
                fn( \WPGraphQL\Model\Post $post ) => Utils::get_modules( $post->ID, self::REST_MODULES_FILTER ) ?? []
            );

            $graphql_sidebar_modules = \apply_filters( 'hkih_posttype_post_sidebar_graphql_modules', [] );
            $graphql_sidebar_layouts = \apply_filters( 'hkih_posttype_post_sidebar_graphql_layouts', [] );

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
            'hide_published_date',
            [
                'get_callback' => fn( $object ) => get_field( 'hide_published_date', $object['id'] ) ?? false,
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
