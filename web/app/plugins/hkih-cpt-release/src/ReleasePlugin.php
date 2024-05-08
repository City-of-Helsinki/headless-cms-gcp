<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace HKIH\CPT\Release;

use HKIH\CPT\Release\PostTypes;

/**
 * Class ReleasePlugin
 *
 * @package HKIH\CPT\Release
 */
final class ReleasePlugin {

    /**
     * Holds the singleton.
     *
     * @var ReleasePlugin
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
     * @return ReleasePlugin
     */
    public static function get_instance() : ReleasePlugin {
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
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return ReleasePlugin
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

        \add_action(
            'init',
            \Closure::fromCallable( [ $this, 'init_classes' ] ),
            0
        );

        add_filter(
            'pll_get_post_types',
            \Closure::fromCallable( [ $this, 'add_to_polylang' ] )
        );
    }

    /**
     * Init classes
     */
    protected function init_classes() {
        $this->classes['PostTypes/Release'] = new PostTypes\Release();
    }

    /**
     * Add the CPT to Polylang translation.
     *
     * @param array $post_types The post type array.
     *
     * @return array The modified post_types array.
     */
    private function add_to_polylang( array $post_types ) {
        $post_types[ PostTypes\Release::get_post_type() ] = PostTypes\Release::get_post_type();

        return $post_types;
    }
}
