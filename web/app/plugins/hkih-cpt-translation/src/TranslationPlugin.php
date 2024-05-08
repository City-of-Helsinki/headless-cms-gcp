<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace HKIH\CPT\Translation;

use HKIH\CPT\Translation\PostTypes;

/**
 * Class TranslationPlugin
 *
 * @package HKIH\CPT\Translation
 */
final class TranslationPlugin {

    /**
     * Holds the singleton.
     *
     * @var TranslationPlugin
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
     * @return TranslationPlugin
     */
    public static function get_instance() : TranslationPlugin {
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
    public static function init( $version = '', $plugin_path = '' ) : void {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $version, $plugin_path );
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return TranslationPlugin
     */
    public static function plugin() : self {
        return self::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( $version = '', $plugin_path = '' ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );

        \add_action(
            'init',
            \Closure::fromCallable( [ $this, 'init_classes' ] ),
            0
        );
    }

    /**
     * Init classes
     */
    protected function init_classes() : void {
        $this->classes['PostTypes/Translation'] = new PostTypes\Translation();
    }
}
