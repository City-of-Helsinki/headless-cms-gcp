<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace HKIH\CPT\Collection;

use Geniem\ACF\Exception;
use Geniem\ACF\Field\FlexibleContent;
use Geniem\Theme\Logger;
use HKIH\CPT\Collection\ACF\CollectionLayout;
use HKIH\CPT\Collection\PostTypes;
use HKIH\CPT\Collection\PostTypes\Collection;

/**
 * Class CollectionPlugin
 *
 * @package HKIH\CPT\Collection
 */
final class CollectionPlugin {

    /**
     * Holds the singleton.
     *
     * @var CollectionPlugin
     */
    protected static $instance;

    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';

    /**
     * Get the instance.
     *
     * @return CollectionPlugin
     */
    public static function get_instance() : CollectionPlugin {
        return self::$instance;
    }

    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin root uri without trailing slash.
     *
     * @var string
     */
    protected $plugin_uri = '';

    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version() : string {
        return $this->version;
    }

    /**
     * Get the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_path() : string {
        return $this->plugin_path;
    }

    /**
     * Get the plugin directory uri.
     *
     * @return string
     */
    public function get_plugin_uri() : string {
        return $this->plugin_uri;
    }

    /**
     * Storage array for plugin class references.
     *
     * @var array
     */
    protected $classes = [];

    /**
     * Initialize the plugin by creating the singleton.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    public static function init( $version, $plugin_path ) : void {
        if ( empty( static::$instance ) ) {
            static::$instance = new self( $version, $plugin_path );
            static::$instance->hooks();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return CollectionPlugin
     */
    public static function plugin() {
        return self::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( $version, $plugin_path ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );

        $this->init_classes();

        add_filter(
            'pll_get_post_types',
            \Closure::fromCallable( [ $this, 'add_to_polylang' ] )
        );
    }

    /**
     * Hooks.
     */
    protected function hooks() : void {
        /**
         * Add ACF/CollectionLayout to these post types
         */
        add_filter(
            'hkih_acf_page_modules_layouts',
            [ $this, 'add_module_layouts' ]
        );

        add_filter(
            'hkih_acf_post_modules_layouts',
            [ $this, 'add_module_layouts' ]
        );

        /**
         * Register REST Response callback to these ACF Modules
         */
        add_filter(
            'hkih_rest_acf_page_modules_layout_collection',
            [ $this, 'collection_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_collection',
            [ $this, 'collection_rest_callback' ]
        );

        /**
         * Register Collections relationship to these ACF Groups.
         */
        add_filter(
            'hkih_posttype_landing_page_fields_content',
            [ $this, 'add_collection_relationship' ]
        );

        /**
         * Register LayoutCollection to these GraphQL Union types.
         */
        add_filter(
            'hkih_posttype_post_graphql_layouts',
            [ $this, 'collection_graphql_layout' ]
        );

        add_filter(
            'hkih_posttype_page_graphql_layouts',
            [ $this, 'collection_graphql_layout' ]
        );
    }

    /**
     * Adds Collection Relationship to PostType field group.
     *
     * @param \Geniem\ACF\Group $field_group Field Group.
     *
     * @return \Geniem\ACF\Group
     */
    public function add_collection_relationship( \Geniem\ACF\Group $field_group ) : \Geniem\ACF\Group {
        try {
            $collections = ( new \Geniem\ACF\Field\Relationship( __( 'Collections', 'hkih-cpt-collection' ) ) )
                ->set_key( sprintf( '%s_collections', $field_group->get_key() ) )
                ->set_name( 'collections' )
                ->set_post_types( [ PostTypes\Collection::get_post_type() ] )
                ->set_filters( [ 'search' ] );
            $field_group->add_field( $collections );
        }
        catch ( \Geniem\ACF\Exception $e ) {
            ( new \Geniem\Theme\Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }

        return $field_group;
    }

    /**
     * Init classes
     */
    protected function init_classes() : void {
        $this->classes['PostTypes/Collection'] = new PostTypes\Collection();
    }

    /**
     * Add the CPT to Polylang translation.
     *
     * @param array $post_types The post type array.
     *
     * @return array The modified post_types array.
     */
    protected function add_to_polylang( array $post_types ) : array {
        $post_types[ PostTypes\Collection::get_post_type() ] = PostTypes\Collection::get_post_type();

        return $post_types;
    }

    /**
     * Add modules layouts
     *
     * @param FlexibleContent $modules Flexible content object.
     *
     * @return FlexibleContent
     */
    public function add_module_layouts( FlexibleContent $modules ) : FlexibleContent {
        try {
            $modules->add_layout( new CollectionLayout( $modules->get_key() ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $modules;
    }

    /**
     * Collection REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function collection_rest_callback( array $layout ) : array {
        if ( empty( $layout['collection'] ) ) {
            return [
                'collection' => [],
                'module'     => $layout['acf_fc_layout'],
            ];
        }

        $collection                     = $layout['collection'];
        $collection->description        = Collection::get_description( $collection->ID );
        $collection->show_on_front_page = Collection::get_show_on_front_page( $collection->ID );
        $collection->background_color   = Collection::get_background_color( $collection->ID );
        $collection->image              = Collection::get_image( $collection->ID );
        $collection->modules            = Collection::get_modules( $collection->ID );

        return [
            'collection' => $collection,
            'module'     => $layout['acf_fc_layout'],
        ];
    }

    /**
     * Add CollectionLayout (as LayoutCollection) to included UnionType possibilities.
     *
     * @param array $layouts GraphQL Layouts.
     *
     * @return array
     */
    public function collection_graphql_layout( array $layouts = [] ) : array {
        $key = CollectionLayout::GRAPHQL_LAYOUT_KEY;

        $layouts[ $key ] = $key;

        return $layouts;
    }
}
