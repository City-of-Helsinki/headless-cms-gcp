<?php
/**
 * This file controls Post type functionality.
 */

namespace Geniem\Theme;

/**
 * Define the controller class.
 */
class PostTypeController implements Interfaces\Controller {

    /**
     * The post type class instances
     *
     * @var \Geniem\Theme\Interfaces\PostType[]
     */
    private $classes = [];

    /**
     * Get a single class instance from Theme Controller
     *
     * @param string|null $class Class name to get.
     * @return \Geniem\Theme\Interfaces\PostType|null
     */
    public function get_class( ?string $class ) : ?Interfaces\PostType {
        return $this->classes[ $class ] ?? null;
    }

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action( 'init', \Closure::fromCallable( [ $this, 'register_cpts' ] ), 0 );
    }

    /**
     * This registers all custom post types.
     *
     * @return void
     */
    private function register_cpts() {

        $instances = array_map(
            function( $field_class ) {

                $field_class = basename( $field_class, '.' . pathinfo( $field_class )['extension'] );
                $class_name  = __NAMESPACE__ . '\PostType\\' . $field_class;

                // Bail early if the class does not exist for some reason
                if ( ! \class_exists( $class_name ) ) {
                    return null;
                }

                return new $class_name();
            },
            array_diff( scandir( __DIR__ . '/PostType' ), [ '.', '..' ] )
        );

        foreach ( $instances as $instance ) {
            if ( $instance instanceof Interfaces\PostType ) {
                $instance->hooks();

                $this->classes[ $instance::SLUG ] = $instance;
            }
        }
    }
}
