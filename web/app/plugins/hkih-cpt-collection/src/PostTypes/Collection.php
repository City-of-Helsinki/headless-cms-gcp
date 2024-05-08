<?php
/**
 * Post type definition for Collection
 */

namespace HKIH\CPT\Collection\PostTypes;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;
use Geniem\Theme\Utils;
use function __;
use function _x;
use function register_graphql_field;
use function register_post_type;

/**
 * Class Collection
 *
 * @package HKIH\CPT\Collection\PostTypes
 */
class Collection {

    /**
     * Post type slug
     *
     * @var string
     */
    protected static $slug = 'collection-cpt';

    /**
     * Graphql single name
     *
     * @var string
     */
    protected static $graphql_single_name = 'collection';

    /**
     * Graphql plural name
     *
     * @var string
     */
    protected static $graphql_plural_name = 'collections';

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
            0,
            0
        );

        add_action(
            'acf/init',
            Closure::fromCallable( [ $this, 'fields' ] ),
            10,
            0
        );

        add_filter(
            'use_block_editor_for_post_type',
            Closure::fromCallable( [ $this, 'disable_gutenberg' ] ),
            10,
            2
        );

        add_action(
            'graphql_register_types',
            Closure::fromCallable( [ $this, 'register_graphql_types' ] )
        );

        add_filter( 'hkih_expirator_post_types', function ( $types ) {
            $types[ self::$slug ] = self::$slug;

            return $types;
        }, 100, 1 );

        add_action(
            'rest_api_init',
            Closure::fromCallable( [ $this, 'register_rest_fields' ] )
        );

        static::$slug = apply_filters( 'hkih_posttype_collection_slug', static::$slug );
    }

    /**
     * Register the post type
     *
     * @return void
     */
    protected function register() : void {
        $labels = [
            'name'                  => _x( 'Collections', 'Post Type General Name', 'hkih-cpt-collection' ),
            'singular_name'         => _x( 'Collection', 'Post Type Singular Name', 'hkih-cpt-collection' ),
            'menu_name'             => __( 'Collections', 'hkih-cpt-collection' ),
            'name_admin_bar'        => __( 'Collection', 'hkih-cpt-collection' ),
            'archives'              => __( 'Collections', 'hkih-cpt-collection' ),
            'parent_item_colon'     => __( 'Collections', 'hkih-cpt-collection' ),
            'all_items'             => __( 'All collections', 'hkih-cpt-collection' ),
            'add_new_item'          => __( 'Add new collection', 'hkih-cpt-collection' ),
            'add_new'               => __( 'Add new collection', 'hkih-cpt-collection' ),
            'new_item'              => __( 'New collection', 'hkih-cpt-collection' ),
            'edit_item'             => __( 'Edit', 'hkih-cpt-collection' ),
            'update_item'           => __( 'Update', 'hkih-cpt-collection' ),
            'view_item'             => __( 'View collection', 'hkih-cpt-collection' ),
            'search_items'          => __( 'Search collections', 'hkih-cpt-collection' ),
            'not_found'             => __( 'Not found', 'hkih-cpt-collection' ),
            'not_found_in_trash'    => __( 'No collections in trash.', 'hkih-cpt-collection' ),
            'insert_into_item'      => __( 'Insert into collection', 'hkih-cpt-collection' ),
            'uploaded_to_this_item' => __( 'Uploaded to this collection', 'hkih-cpt-collection' ),
            'items_list'            => __( 'collection', 'hkih-cpt-collection' ),
            'items_list_navigation' => __( 'collection', 'hkih-cpt-collection' ),
            'filter_items_list'     => __( 'collection', 'hkih-cpt-collection' ),
        ];

        $labels = apply_filters( 'hkih_posttype_collection_labels', $labels );

        $args = [
            'label'               => __( 'Collections', 'hkih-cpt-collection' ),
            'description'         => __( 'Collections', 'hkih-cpt-collection' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'revisions' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-forms',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'map_meta_cap'        => true,
            'capability_type'     => 'collection',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'graphql_single_name' => static::$graphql_single_name,
            'graphql_plural_name' => static::$graphql_plural_name,
            'query_var'           => true,
            'taxonomies'          => [],
        ];

        $args = apply_filters( 'hkih_posttype_collection_args', $args );

        register_post_type( static::$slug, $args );
    }

    /**
     * Register the fields for the post type
     *
     * @return void
     */
    protected function fields() : void {
        try {
            $key = 'hkih-cpt-collection';

            $field_group = new Group(
                __( 'Collection', 'hkih-cpt-collection' ),
                $key
            );

            $field_group->add_rule_group( $this->get_rule_group() );

            $show_on_front_page = ( new Field\TrueFalse( __( 'Show on front page', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_show_on_front_page" )
                ->set_name( 'show_on_front_page' )
                ->use_ui()
                ->set_wrapper_width( 50 );

            $image = ( new Field\Image( __( 'Image', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_image" )
                ->set_name( 'image' )
                ->set_wrapper_width( 50 );

            $background_color = ( new Field\Select( __( 'Background Color', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_background_color" )
                ->set_name( 'background_color' )
                ->set_choices( apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->set_wrapper_width( 50 );

            $description = ( new Field\Textarea( __( 'Description', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_description" )
                ->set_name( 'description' )
                ->set_new_lines( 'wpautop' )
                ->set_rows( 4 );

            $url_slug = ( new Field\Text( __( 'URL slug', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_url_slug" )
                ->set_name( 'url_slug' )
                ->set_wrapper_width( 50 );

            $modules = ( new Field\FlexibleContent( __( 'Modules', 'hkih-cpt-collection' ) ) )
                ->set_key( "${key}_modules" )
                ->set_name( 'modules' );

            $modules = apply_filters(
                'hkih_acf_collection_modules_layouts',
                $modules
            );

            $field_group->add_fields( [
                $image,
                $background_color,
                $description,
                $show_on_front_page,
                $url_slug,
                $modules,
            ] );

            $field_group = apply_filters(
                'hkih_posttype_collection_fields',
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Get rule group for post type
     *
     * @return RuleGroup
     * @throws Exception ACF-Codifier exception.
     */
    protected function get_rule_group() : RuleGroup {
        return ( new RuleGroup() )
            ->add_rule( 'post_type', '==', static::$slug );
    }

    /**
     * Disable Gutenberg for this post type
     *
     * @param boolean $current_status The current Gutenberg status.
     * @param string  $post_type      The post type.
     *
     * @return boolean
     */
    protected function disable_gutenberg( bool $current_status, string $post_type ) : bool {
        return $post_type === static::$slug ? false : $current_status;
    }

    /**
     * Register fields for graphql
     */
    protected function register_graphql_types( $type_registry ) : void {
        register_graphql_field( static::$graphql_single_name, 'backgroundColor', [
            'type'        => 'String',
            'description' => __( 'Background Color', 'hkih-cpt-collection' ),
            'resolve'     => fn( $post ) => static::get_background_color( $post->ID ),
        ] );

        register_graphql_field( static::$graphql_single_name, 'image', [
            'type'        => 'String',
            'description' => __( 'Image', 'hkih-cpt-collection' ),
            'resolve'     => fn( $post ) => static::get_image( $post->ID ),
        ] );

        register_graphql_field( static::$graphql_single_name, 'description', [
            'type'        => 'String',
            'description' => __( 'Description', 'hkih-cpt-collection' ),
            'resolve'     => fn( $post ) => static::get_description( $post->ID ),
        ] );

        register_graphql_field( static::$graphql_single_name, 'showOnFrontPage', [
            'type'        => 'Boolean',
            'description' => __( 'Show on front page', 'hkih-cpt-collection' ),
            'resolve'     => fn( $post ) => static::get_show_on_front_page( $post->ID ),
        ] );

        $this->register_collection_modules_graphql( $type_registry );
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() : void {
        register_rest_field(
            [ static::get_post_type() ],
            'background_color',
            [
                'get_callback' => fn( $object ) => static::get_background_color( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'image',
            [
                'get_callback' => fn( $object ) => static::get_image( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'show_on_front_page',
            [
                'get_callback' => fn( $object ) => static::get_show_on_front_page( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'description',
            [
                'get_callback' => fn( $object ) => static::get_description( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'modules',
            [
                'get_callback' => fn( $object ) => static::get_modules( $object['id'] ),
            ]
        );
    }

    /**
     * Flexible Content modules REST callback
     *
     * @param int $post_id WP_Post ID.
     *
     * @return array
     */
    public static function get_modules( int $post_id ) : array {
        return Utils::get_modules( $post_id, 'hkih_rest_acf_collection_modules_layout' );
    }

    /**
     * Get description
     *
     * @param int $post_id WP_Post ID.
     *
     * @return string
     */
    public static function get_description( $post_id ) {
        return wp_kses_post( get_field( 'description', $post_id ) );
    }

    /**
     * Get show on front page
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_show_on_front_page( $post_id ) {
        return get_field( 'show_on_front_page', $post_id );
    }

    /**
     * Get background color
     *
     * @param int $post_id WP_Post ID.
     *
     * @return string
     */
    public static function get_background_color( $post_id ) {
        return esc_html( get_field( 'background_color', $post_id ) );
    }

    /**
     * Get image
     *
     * @param int $post_id WP_Post ID.
     *
     * @return false|string
     */
    public static function get_image( $post_id ) {
        $image = get_field( 'image', $post_id ) ?? '';

        if ( empty( $image ) ) {
            return '';
        }

        return wp_get_attachment_image_url( $image['id'], 'large' );
    }

    /**
     * Register GraphQL modules from layouts.
     *
     * @param \WPGraphQL\Registry\TypeRegistry $type_registry GraphQL Type Registry.
     */
    private function register_collection_modules_graphql( $type_registry ) : void {
        $hkih_graphql_modules = \apply_filters( 'hkih_graphql_modules', [] );

        try {
            foreach ( $hkih_graphql_modules as $type => $fields ) {
                if ( $type_registry->get_type( $type ) === null ) {
                    register_graphql_object_type( $type, [
                        'description' => sprintf(
                        /* translators: %s is module name */
                            __( 'Collection Module: %s', 'hkih-cpt-collection' ),
                            $type
                        ),
                        'fields'      => $fields,
                    ] );
                }
            }

            register_graphql_union_type( 'CollectionModulesUnionType', [
                'typeNames'   => array_keys( $hkih_graphql_modules ),
                'resolveType' => function ( $layout ) use ( $type_registry ) {
                    $type     = Utils::resolve_layout_type( $layout );
                    $resolved = $type_registry->get_type( $type );

                    if ( empty( $resolved ) ) {
                        return $type;
                    }

                    return $resolved;
                },
            ] );

            $hkih_collection_modules = \apply_filters(
                'hkih_posttype_collection_modules',
                [ static::$graphql_single_name ]
            );

            if ( ! empty( $hkih_collection_modules ) ) {
                foreach ( $hkih_collection_modules as $graphql_single_name ) {
                    register_graphql_field( $graphql_single_name, 'modules', [
                        'type'        => [ 'list_of' => 'CollectionModulesUnionType' ],
                        'description' => __( 'List of modules', 'hkih-cpt-collection' ),
                        'resolve'     => fn( \WPGraphQL\Model\Post $post ) => $post->ID !== null ? self::get_modules( $post->ID ) : [],
                    ] );
                }
            }
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
