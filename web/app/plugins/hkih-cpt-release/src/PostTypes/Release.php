<?php
/**
 * Post type definition for Release
 */

namespace HKIH\CPT\Release\PostTypes;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\Field;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;
use HKIH\CPT\Collection\PostTypes\Collection;
use WPGraphQL\Model\Post;
use function __;
use function _x;
use function register_post_type;

/**
 * Class Release
 *
 * @package HKIH\CPT\Release\PostTypes
 */
class Release {

    /**
     * Post type slug
     *
     * @var string
     */
    protected static $slug = 'release-cpt';

    /**
     * Graphql single name
     *
     * @var string
     */
    protected static $graphql_single_name = 'release';

    /**
     * Get the post type slug.
     *
     * @return string
     */
    public static function get_post_type() : string {
        return static::$slug;
    }

    /**
     * Get the post type graphql slug.
     *
     * @return string
     */
    public static function get_graphql_single_name() : string {
        return static::$graphql_single_name;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action(
            'init',
            Closure::fromCallable( [ $this, 'register' ] ),
            100,
            0
        );

        add_filter( 'hkih_expirator_post_types', function ( $types ) {
            $types[ self::$slug ] = self::$slug;

            return $types;
        }, 100, 1 );

        add_filter(
            'allowed_block_types',
            Closure::fromCallable( [ $this, 'allowed_block_types' ] ), 50, 2
        );

        static::$slug = apply_filters( 'hkih_posttype_release_slug', static::$slug );
    }

    /**
     * Register the post type
     *
     * @return void
     */
    protected function register() : void {
        $labels = [
            'name'                  => _x( 'Releases', 'Post Type General Name', 'hkih-cpt-release' ),
            'singular_name'         => _x( 'Release', 'Post Type Singular Name', 'hkih-cpt-release' ),
            'menu_name'             => __( 'Releases', 'hkih-cpt-release' ),
            'name_admin_bar'        => __( 'Release', 'hkih-cpt-release' ),
            'archives'              => __( 'Releases', 'hkih-cpt-release' ),
            'parent_item_colon'     => __( 'Releases', 'hkih-cpt-release' ),
            'all_items'             => __( 'All releases', 'hkih-cpt-release' ),
            'add_new_item'          => __( 'Add new release', 'hkih-cpt-release' ),
            'add_new'               => __( 'Add new release', 'hkih-cpt-release' ),
            'new_item'              => __( 'New release', 'hkih-cpt-release' ),
            'edit_item'             => __( 'Edit', 'hkih-cpt-release' ),
            'update_item'           => __( 'Update', 'hkih-cpt-release' ),
            'view_item'             => __( 'View release', 'hkih-cpt-release' ),
            'search_items'          => __( 'Search releases', 'hkih-cpt-release' ),
            'not_found'             => __( 'Not found', 'hkih-cpt-release' ),
            'not_found_in_trash'    => __( 'No releases in trash.', 'hkih-cpt-release' ),
            'insert_into_item'      => __( 'Insert into release', 'hkih-cpt-release' ),
            'uploaded_to_this_item' => __( 'Uploaded to this release', 'hkih-cpt-release' ),
            'items_list'            => __( 'release', 'hkih-cpt-release' ),
            'items_list_navigation' => __( 'release', 'hkih-cpt-release' ),
            'filter_items_list'     => __( 'release', 'hkih-cpt-release' ),
        ];

        $labels = apply_filters( 'hkih_posttype_release_labels', $labels );

        $args = [
            'label'               => __( 'Releases', 'hkih-cpt-release' ),
            'description'         => __( 'Releases', 'hkih-cpt-release' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'editor', 'revisions' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-format-status',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'map_meta_cap'        => [ 'releases' ],
            'capability_type'     => 'release',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'graphql_single_name' => static::get_graphql_single_name(),
            'graphql_plural_name' => 'releases',
            'query_var'           => true,
            'taxonomies'          => [],
        ];

        $args = apply_filters( 'hkih_posttype_release_args', $args );

        register_post_type( static::$slug, $args );
    }

    /**
     * Set the allowed block types. By default the allowed_blocks array
     * is empty, which means that all block types are allowed. We simply
     * fill the array with the block types that the theme supports.
     *
     * @param bool|array $allowed_blocks An empty array.
     * @param \WP_Post   $post           The post resource data.
     *
     * @return array An array of allowed block types.
     */
    private function allowed_block_types( $allowed_blocks, $post ) : array {
        if ( static::get_post_type() !== \get_post_type( $post ) ) {
            return $allowed_blocks;
        }

        return [
            'core/block',
            'core/template',
            'core/heading',
            'core/paragraph',
            'core/list',
            'core/list-item',
            'core/buttons',
        ];
    }
}
