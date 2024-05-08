<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace HKIH\CPT\Contact;

use Geniem\ACF\Exception;
use Geniem\ACF\Field\FlexibleContent;
use Geniem\Theme\Logger;
use HKIH\CPT\Contact\ACF\ContactLayout;
use HKIH\CPT\Contact\PostTypes;

/**
 * Class ContactPlugin
 *
 * @package HKIH\CPT\Contact
 */
final class ContactPlugin {

    /**
     * Holds the singleton.
     *
     * @var ContactPlugin
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
     * @return ContactPlugin
     */
    public static function get_instance() : ContactPlugin {
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
    public static function init( $version, $plugin_path ) {
        if ( empty( static::$instance ) ) {
            static::$instance = new self( $version, $plugin_path );
            static::$instance->hooks();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return ContactPlugin
     */
    public static function plugin() {
        return static::$instance;
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
     * Hooks
     */
    public function hooks() : void {
        add_filter(
            'hkih_acf_page_modules_layouts',
            [ $this, 'add_module_layouts' ]
        );

        add_filter(
            'hkih_acf_post_modules_layouts',
            [ $this, 'add_module_layouts' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_contacts',
            [ $this, 'contacts_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_contacts',
            [ $this, 'contacts_rest_callback' ]
        );

        /**
         * Register LayoutContact to these GraphQL Union types.
         */
        add_filter(
            'hkih_posttype_page_graphql_layouts',
            [ $this, 'contacts_graphql_layout' ]
        );
        add_filter(
            'hkih_posttype_post_graphql_layouts',
            [ $this, 'contacts_graphql_layout' ]
        );
    }

    /**
     * Init classes
     */
    protected function init_classes() : void {
        $this->classes['PostTypes/Contact'] = new PostTypes\Contact();
    }

    /**
     * Add the CPT to Polylang translation.
     *
     * @param array $post_types The post type array.
     *
     * @return array The modified post_types array.
     */
    protected function add_to_polylang( array $post_types ) : array {
        $post_types[ PostTypes\Contact::get_post_type() ] = PostTypes\Contact::get_post_type();

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
            $modules->add_layout( new ContactLayout( $modules->get_key() ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $modules;
    }

    /**
     * Contacts REST callback
     *
     * @param array $layout ACF layout data.
     *
     * @return array
     */
    public function contacts_rest_callback( array $layout ) : array {
        if ( empty( $layout['contacts'] ) || ! is_array( $layout['contacts'] ) ) {
            $layout['contacts'] = [];
        }

        return [
            'title'    => esc_html( $layout['title'] ),
            'contacts' => array_map(
                static fn( $contact ) => PostTypes\Contact::add_fields_to_contact( $contact ),
                $layout['contacts']
            ),
            'module'   => $layout['acf_fc_layout'],
        ];
    }

    /**
     * Add ContactLayout (as LayoutContact) to included UnionType possibilities.
     *
     * @param array $layouts GraphQL Layouts.
     *
     * @return array
     */
    public function contacts_graphql_layout( array $layouts = [] ) : array {
        $key = ContactLayout::GRAPHQL_LAYOUT_KEY;

        $layouts[ $key ] = $key;

        return $layouts;
    }
}
