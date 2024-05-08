<?php
/**
 * Collection ACF Layout
 */

namespace HKIH\CPT\Collection\ACF;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use HKIH\CPT\Collection\PostTypes\Collection;

/**
 * Class CollectionLayout
 *
 * @package HKIH\CPT\Collection\ACF
 */
class CollectionLayout extends Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_collection';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutCollection';

    /**
     * Create the layout
     *
     * @param string $key Layout Key Prefix.
     */
    public function __construct( string $key ) {
        parent::__construct(
            __( 'Collection', 'hkih-cpt-collection' ),
            $key . static::KEY,
            'collection'
        );

        $this->strings = [
            'collection' => [
                'label'        => __( 'Collection', 'hkih-linked-events' ),
                'instructions' => '',
            ],
        ];

        $this->add_layout_fields();

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] )
        );
    }

    /**
     * Add layout fields
     *
     * @return void
     */
    private function add_layout_fields() : void {
        $key = $this->get_key();

        try {
            $collection_field = ( new Field\PostObject( $this->strings['collection']['label'] ) )
                ->set_key( "${key}_collection" )
                ->set_name( 'collection' )
                ->set_post_types( [ Collection::get_post_type() ] )
                ->set_instructions( $this->strings['collection']['instructions'] );

            $this->add_fields( [
                $collection_field,
            ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Register Layout fields to GraphQL.
     *
     * @param \WPGraphQL\Registry\TypeRegistry $type_registry GraphQL Type Registry.
     */
    private function register_graphql_fields( $type_registry ) : void {
        $key = self::GRAPHQL_LAYOUT_KEY;

        // If the layout is already known/initialized, no need to register it again.
        if ( array_key_exists( $key, \apply_filters( 'hkih_graphql_layouts', [] ) ) ) {
            return;
        }

        $fields = [
            'collection' => [
                'type'        => Collection::get_graphql_single_name(),
                'description' => $this->strings['collection']['label'],
                'resolve'     => fn( $layout ) => new \WPGraphQL\Model\Post( $layout['collection'] ?? [] ),
            ],
        ];

        if ( $type_registry->get_type( $key ) === null ) {
            register_graphql_object_type( $key, [
                'description' => sprintf(
                /* translators: %s is layout name */
                    __( 'Layout: %s', 'hkih' ),
                    $key
                ),
                'fields'      => $fields,
            ] );
        }

        \add_filter( 'hkih_graphql_layouts', function ( array $layouts = [] ) use ( $fields, $key ) {
            $layouts[ $key ] = $fields;

            return $layouts;
        } );
    }
}
