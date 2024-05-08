<?php
/**
 * Post type definition for LandingPage
 */

namespace HKIH\CPT\LandingPage\PostTypes;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\Field;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;
use Geniem\Theme\Utils;
use HKIH\CPT\Collection\PostTypes\Collection;
use WPGraphQL\Model\Post;
use function __;
use function _x;
use function register_post_type;

/**
 * Class LandingPage
 *
 * @package HKIH\CPT\LandingPage\PostTypes
 */
class LandingPage {

    /**
     * Post type slug
     *
     * @var string
     */
    protected static $slug = 'landing-page-cpt';

    /**
     * Graphql single name
     *
     * @var string
     */
    protected static $graphql_single_name = 'landingPage';

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

        add_action(
            'acf/init',
            Closure::fromCallable( [ $this, 'fields' ] ),
            50,
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

        add_action(
            'rest_api_init',
            Closure::fromCallable( [ $this, 'register_rest_fields' ] )
        );

        /**
         * Adds modules to GraphQL response.
         *
         * @see \HKIH\CPT\Collection\PostTypes\Collection::register_collection_modules_graphql()
         */
        add_filter(
            'hkih_posttype_collection_modules',
            [ $this, 'add_to_collection_modules' ]
        );

        static::$slug = apply_filters( 'hkih_posttype_landing_page_slug', static::$slug );
    }

    /**
     * Add To GraphQL Modules.
     *
     * @param array $graphql_names GraphQL Single Names.
     *
     * @return array
     */
    public function add_to_collection_modules( array $graphql_names ) : array {
        $graphql_names[] = static::$graphql_single_name;

        return $graphql_names;
    }

    /**
     * Register the post type
     *
     * @return void
     */
    protected function register() : void {
        $labels = [
            'name'                  => _x( 'Landing Pages', 'Post Type General Name', 'hkih-cpt-landing-page' ),
            'singular_name'         => _x( 'Landing Page', 'Post Type Singular Name', 'hkih-cpt-landing-page' ),
            'menu_name'             => __( 'Landing Pages', 'hkih-cpt-landing-page' ),
            'name_admin_bar'        => __( 'Landing Page', 'hkih-cpt-landing-page' ),
            'archives'              => __( 'Landing Pages', 'hkih-cpt-landing-page' ),
            'parent_item_colon'     => __( 'Landing Pages', 'hkih-cpt-landing-page' ),
            'all_items'             => __( 'All landing pages', 'hkih-cpt-landing-page' ),
            'add_new_item'          => __( 'Add new landing page', 'hkih-cpt-landing-page' ),
            'add_new'               => __( 'Add new landing page', 'hkih-cpt-landing-page' ),
            'new_item'              => __( 'New landing page', 'hkih-cpt-landing-page' ),
            'edit_item'             => __( 'Edit', 'hkih-cpt-landing-page' ),
            'update_item'           => __( 'Update', 'hkih-cpt-landing-page' ),
            'view_item'             => __( 'View landing page', 'hkih-cpt-landing-page' ),
            'search_items'          => __( 'Search landing pages', 'hkih-cpt-landing-page' ),
            'not_found'             => __( 'Not found', 'hkih-cpt-landing-page' ),
            'not_found_in_trash'    => __( 'No landing pages in trash.', 'hkih-cpt-landing-page' ),
            'insert_into_item'      => __( 'Insert into landing page', 'hkih-cpt-landing-page' ),
            'uploaded_to_this_item' => __( 'Uploaded to this landing page', 'hkih-cpt-landing-page' ),
            'items_list'            => __( 'landing page', 'hkih-cpt-landing-page' ),
            'items_list_navigation' => __( 'landing page', 'hkih-cpt-landing-page' ),
            'filter_items_list'     => __( 'landing page', 'hkih-cpt-landing-page' ),
        ];

        $labels = apply_filters( 'hkih_posttype_landing_page_labels', $labels );

        $args = [
            'label'               => __( 'Landing Pages', 'hkih-cpt-landing-page' ),
            'description'         => __( 'Landing Pages', 'hkih-cpt-landing-page' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'revisions' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-admin-site',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'map_meta_cap'        => true,
            'capability_type'     => 'landing_page',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'graphql_single_name' => static::get_graphql_single_name(),
            'graphql_plural_name' => 'landingPages',
            'query_var'           => true,
            'taxonomies'          => [],
        ];

        $args = apply_filters( 'hkih_posttype_landing_page_args', $args );

        register_post_type( static::$slug, $args );
    }

    /**
     * Register the fields for the post type
     *
     * @return void
     */
    protected function fields() : void {
        try {
            $this->register_hero_fields();
            $this->register_content_fields();
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Register hero fields
     *
     * @throws Exception ACF-Codifier exception.
     */
    protected function register_hero_fields() : void {
        $hero_key = 'hkih-cpt-landing-page-hero';

        $hero_group = new Group(
            __( 'Hero', 'hkih-cpt-landing-page' ),
            $hero_key
        );

        $hero_group->add_rule_group( $this->get_rule_group() );

        $desktop_image = ( new Field\Image( __( 'Desktop Image', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_desktop_image" )
            ->set_name( 'desktop_image' )
            ->set_wrapper_width( 33 );

        $hero_group->add_field( $desktop_image );

        $mobile_image = ( new Field\Image( __( 'Mobile Image', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_mobile_image" )
            ->set_name( 'mobile_image' )
            ->set_wrapper_width( 33 );

        $hero_group->add_field( $mobile_image );

        $float_image = ( new Field\Image( __( 'Float Image', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_float_image" )
            ->set_name( 'float_image' )
            ->set_wrapper_width( 33 );

        $hero_group->add_field( $float_image );

        $background_color = ( new Field\Select( __( 'Background Color', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_background_color" )
            ->set_name( 'background_color' )
            ->set_choices( apply_filters( 'hkih_hds_brand_colors', [] ) )
            ->set_wrapper_width( 50 );

        $hero_group->add_field( $background_color );

        $box_color = ( new Field\Select( __( 'Box Color', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_box_color" )
            ->set_name( 'box_color' )
            ->set_choices( apply_filters( 'hkih_hds_brand_colors', [] ) )
            ->set_wrapper_width( 50 );

        $hero_group->add_field( $box_color );

        $description = ( new Field\Textarea( __( 'Description', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_description" )
            ->set_name( 'description' )
            ->set_new_lines( 'wpautop' )
            ->set_rows( 4 )
            ->set_wrapper_width( 50 );

        $hero_group->add_field( $description );

        $link = ( new Field\Link( __( 'Link', 'hkih-cpt-landing-page' ) ) )
            ->set_key( "${hero_key}_link" )
            ->set_name( 'link' )
            ->set_wrapper_width( 50 );

        $hero_group->add_field( $link );

        // Filter for these fields
        $hero_group = apply_filters(
            'hkih_posttype_landing_page_fields_hero',
            $hero_group
        );

        // Filter for all fields
        $hero_group = apply_filters(
            'hkih_posttype_landing_page_fields',
            $hero_group
        );

        $hero_group->register();
    }

    /**
     * Register content fields
     *
     * @throws Exception ACF-Codifier exception.
     */
    protected function register_content_fields() : void {
        $key = 'hkih-cpt-landing-page';

        $field_group = new Group(
            __( 'Landing Page', 'hkih-cpt-landing-page' ),
            $key
        );

        $field_group->add_rule_group( $this->get_rule_group() );

        // Filter for these fields
        $field_group = apply_filters(
            'hkih_posttype_landing_page_fields_content',
            $field_group
        );

        // Filter for all fields
        $field_group = apply_filters(
            'hkih_posttype_landing_page_fields',
            $field_group
        );

        $field_group->register();
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
    protected function register_graphql_types() : void {
        register_graphql_connection( [
            'description'    => __( 'Desktop Image', 'hkih-cpt-collection' ),
            'fromType'       => static::get_graphql_single_name(),
            'toType'         => 'MediaItem',
            'fromFieldName'  => 'desktopImage',
            'connectionArgs' => \WPGraphQL\Connection\PostObjects::get_connection_args(),
            'resolve'        => fn( Post $p, $a, $c, $i ) => Utils::resolve_image( $p, $a, $c, $i, 'desktop_image' ),
        ] );

        register_graphql_connection( [
            'description'    => __( 'Mobile Image', 'hkih-cpt-collection' ),
            'fromType'       => static::get_graphql_single_name(),
            'toType'         => 'MediaItem',
            'fromFieldName'  => 'mobileImage',
            'connectionArgs' => \WPGraphQL\Connection\PostObjects::get_connection_args(),
            'resolve'        => fn( Post $p, $a, $c, $i ) => Utils::resolve_image( $p, $a, $c, $i, 'mobile_image' ),
        ] );

        register_graphql_connection( [
            'description'    => __( 'Float Image', 'hkih-cpt-collection' ),
            'fromType'       => static::get_graphql_single_name(),
            'toType'         => 'MediaItem',
            'fromFieldName'  => 'floatImage',
            'connectionArgs' => \WPGraphQL\Connection\PostObjects::get_connection_args(),
            'resolve'        => fn( Post $p, $a, $c, $i ) => Utils::resolve_image( $p, $a, $c, $i, 'float_image' ),
        ] );

        register_graphql_field( static::get_graphql_single_name(), 'backgroundColor', [
            'type'        => 'String',
            'description' => __( 'Background Color', 'hkih-cpt-collection' ),
            'resolve'     => fn( Post $post ) => static::get_background_color( $post->ID ),
        ] );

        register_graphql_field( static::get_graphql_single_name(), 'description', [
            'type'        => 'String',
            'description' => __( 'Description', 'hkih-cpt-collection' ),
            'resolve'     => fn( Post $post ) => static::get_description( $post->ID ),
        ] );

        register_graphql_field( static::get_graphql_single_name(), 'boxColor', [
            'type'        => 'String',
            'description' => __( 'Box Color', 'hkih-cpt-collection' ),
            'resolve'     => fn( Post $post ) => static::get_box_color( $post->ID ),
        ] );

        register_graphql_field( static::get_graphql_single_name(), 'heroLink', [
            'type'        => [ 'list_of' => 'String' ],
            'description' => __( 'Link', 'hkih-cpt-collection' ),
            'resolve'     => fn( Post $post ) => static::get_link( $post->ID ) ?: [],
        ] );
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() {
        register_rest_field(
            [ static::get_post_type() ],
            'desktop_image',
            [
                'get_callback' => fn( $object ) => static::get_desktop_image( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'mobile_image',
            [
                'get_callback' => fn( $object ) => static::get_mobile_image( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'float_image',
            [
                'get_callback' => fn( $object ) => static::get_float_image( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'background_color',
            [
                'get_callback' => fn( $object ) => static::get_background_color( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'box_color',
            [
                'get_callback' => fn( $object ) => static::get_box_color( $object['id'] ),
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
            'link',
            [
                'get_callback' => fn( $object ) => static::get_link( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'collections',
            [
                'get_callback' => [ $this, 'get_rest_collections' ],
            ]
        );
    }

    /**
     * Collections REST callback
     *
     * @param array $object WP_Post array.
     *
     * @return array
     */
    public function get_rest_collections( $object ) {
        $collections = get_field( 'collections', $object['id'] );

        if ( empty( $collections ) ) {
            return $collections;
        }

        return array_map( function ( $collection ) {
            $collection->description        = Collection::get_description( $collection->ID );
            $collection->show_on_front_page = Collection::get_show_on_front_page( $collection->ID );
            $collection->background_color   = Collection::get_background_color( $collection->ID );
            $collection->image              = Collection::get_image( $collection->ID );
            $collection->modules            = Collection::get_modules( $collection->ID );

            return $collection;
        }, $collections );
    }

    /**
     * Get desktop image
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_desktop_image( $post_id ) {
        return get_field( 'desktop_image', $post_id );
    }

    /**
     * Get mobile image
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_mobile_image( $post_id ) {
        return get_field( 'mobile_image', $post_id );
    }

    /**
     * Get float image
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_float_image( $post_id ) {
        return get_field( 'float_image', $post_id );
    }

    /**
     * Get background color
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_background_color( $post_id ) {
        return get_field( 'background_color', $post_id );
    }

    /**
     * Get box color
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_box_color( $post_id ) {
        return get_field( 'box_color', $post_id );
    }

    /**
     * Get link
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_description( $post_id ) {
        return wp_kses_post( get_field( 'description', $post_id ) );
    }

    /**
     * Get link
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_link( $post_id ) {
        return get_field( 'link', $post_id );
    }
}
