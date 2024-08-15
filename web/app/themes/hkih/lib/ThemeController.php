<?php
/**
 * Theme controller. This class initializes other classes
 * related to theme functionality.
 */

namespace Geniem\Theme;

/**
 * Class ThemeController
 *
 * This class sets up the theme functionalities.
 *
 * @package Geniem\Theme
 */
class ThemeController {
    /**
     * The controller instance
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * The class instances
     *
     * @var array
     */
    private $classes = [];

    /**
     * Get the ThemeController
     *
     * @return \Geniem\Theme\ThemeController
     */
    public static function instance() : ThemeController {
        if ( ! static::$instance ) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_classes();
    }

    /**
     * Get a single class instance from Theme Controller
     *
     * @param string|null $class Class name to retrieve. See init_classes().
     *
     * @return Interfaces\Controller|null
     */
    public function get_class( ?string $class ) : ?Interfaces\Controller {
        return $this->classes[ $class ] ?? null;
    }

    /**
     * Run setup for theme functionality.
     *
     * @return void
     */
    private function init_classes() : void {
        $this->classes = [
            'PostTypeController'     => new PostTypeController(),
            'TaxonomyController'     => new TaxonomyController(),
            'ACFController'          => new ACFController(),
            'BlocksController'       => new BlocksController(),
            'ThemeSupports'          => new ThemeSupports(),
            'Assets'                 => new Assets(),
            'Expirator'              => new Expirator(),
            'Admin'                  => new Admin(),
            'HDS'                    => new HDS(),
            'Roles'                  => new Roles(),
            'Localization'           => new Localization(),
            'SeoFrameworkGraphQLMap' => new SeoFrameworkGraphQLMap(),
            'CustomGraphqlSchemas'   => new CustomGraphqlSchemas(),
            'DynamicUpdate'          => new DynamicUpdate(),
            'WaveMotifs'             => new WaveMotifs(),
            'PreviewController'      => new PreviewController(),
        ];

        array_walk( $this->classes, function ( $instance ) {
            if ( $instance instanceof Interfaces\Controller ) {
                $instance->hooks();
            }
        } );
    }
}
