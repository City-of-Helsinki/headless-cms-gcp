<?php
/**
 * The class representation of the WordPress default post type 'attachment'.
 */

namespace Geniem\Theme\PostType;

use \Geniem\Theme\Interfaces\PostType;

/**
 * This class represents WordPress default post type 'attachment'.
 *
 * @package Geniem\Theme\PostType
 */
class Attachment implements PostType {
    /**
     * This defines the slug of this post type.
     */
    public const SLUG = 'attachment';
    /**
     * This is the GraphQl name used by the WP GraphQL Plugin.
     */
    public const GRAPHQL_NAME = 'mediaItem';

    /**
     * This is called in setup automatically.
     *
     * @return void
     */
    public function hooks() : void {
        add_action(
            'init',
            \Closure::fromCallable( [ $this, 'modify_supports' ] ),
            15
        );
        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_types' ] )
        );
    }

    /**
     * Modify supported features.
     *
     * @return void
     */
    private function modify_supports() : void {
        \remove_post_type_support( self::SLUG, 'comments' );
        \remove_post_type_support( self::SLUG, 'trackbacks' );
    }

    /**
     * Registers all GraphQL Fields.
     *
     * @return void
     */
    private function register_graphql_types() : void {
        \register_graphql_field( self::GRAPHQL_NAME, 'photographerName', [
            'type'        => 'String',
            'description' => __( 'Photographer Credit', 'hkih' ),
            'resolve'     => fn( $post ) => get_field( 'photographer_name', ( (array) $post )['ID'] ) ?? '',
        ] );
    }
}
