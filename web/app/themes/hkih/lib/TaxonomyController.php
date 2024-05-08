<?php
/**
 * This file controls taxonomy functionality.
 */

namespace Geniem\Theme;

/**
 * Define the controller class.
 */
class TaxonomyController implements Interfaces\Controller {

    /**
     * The taxonomy class instances
     *
     * @var \Geniem\Theme\Interfaces\Taxonomy[]
     */
    private $classes = [];

    /**
     * Get a single class instance from Theme Controller
     *
     * @param string|null $class Class name to get.
     * @return \Geniem\Theme\Interfaces\Taxonomy|null
     */
    public function get_class( ?string $class ) : ?Interfaces\Taxonomy {
        return $this->classes[ $class ] ?? null;
    }

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action( 'init', \Closure::fromCallable( [ $this, 'register_taxonomies' ] ) );
    }

    /**
     * This registers all custom taxonomies.
     *
     * @return void
     */
    private function register_taxonomies() {

        $instances = array_map(
            function( $field_class ) {

                $field_class = basename( $field_class, '.' . pathinfo( $field_class )['extension'] );
                $class_name  = __NAMESPACE__ . '\Taxonomy\\' . $field_class;

                // Bail early if the class does not exist for some reason
                if ( ! \class_exists( $class_name ) ) {
                    return null;
                }

                return new $class_name();
            },
            array_diff( scandir( __DIR__ . '/Taxonomy' ), [ '.', '..' ] )
        );

        foreach ( $instances as $instance ) {
            if ( $instance instanceof Interfaces\Taxonomy ) {
                $instance->hooks();

                $this->classes[ $instance::SLUG ] = $instance;
            }
        }
    }
}
