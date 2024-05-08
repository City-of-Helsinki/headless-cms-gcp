<?php
/**
 * Image Gallery ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use WPGraphQL\Model\Post;

/**
 * Class ImageGalleryLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class ImageGalleryLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_gallery';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutImageGallery';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Image gallery', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'gallery';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'gallery' => [
                'label' => __( 'Gallery', 'hkih' ),
            ],
            'per_row' => [
                'label' => __( 'Per row', 'hkih' ),
            ],
        ];

        $this->add_layout_fields();

        \add_action(
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
            $gallery_field = ( new Field\Gallery( $this->strings['gallery']['label'] ) )
                ->set_key( "{$key}_gallery" )
                ->set_name( 'gallery' )
                ->set_required()
                ->set_wrapper_width( 100 );

            $this->add_fields( [
                $gallery_field,
            ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Register Layout output structure for GraphQL Union type.
     *
     * Array key is the name of the type, all included fields under it
     * should be the same as the REST output fields.
     *
     * The layout name should be the module name, but in CamelCase,
     * so event_search becomes EventSearch.
     */
    private function register_graphql_fields() : void {
        $key = self::GRAPHQL_LAYOUT_KEY;

        // If the layout is already known/initialized, no need to register it again.
        if ( array_key_exists( $key, \apply_filters( 'hkih_graphql_layouts', [] ) ) ) {
            return;
        }

        $fields = [
            'gallery' => [
                'type'        => [ 'list_of' => 'GalleryImage' ],
                'description' => $this->strings['gallery']['label'],
            ],
        ];

        register_graphql_object_type( 'GalleryImage', [
            'description' => __( 'Gallery Image', 'hkih' ),
            'fields'      => [
                'title' => [
                    'type'        => 'String',
                    'description' => __( 'Title of the image', 'hkih' ),
                ],
                'caption' => [
                    'type'        => 'String',
                    'description' => __( 'Caption of the image', 'hkih' ),
                ],
                'description' => [
                    'type'        => 'String',
                    'description' => __( 'Description of the image', 'hkih' ),
                ],
                'thumbnail' => [
                    'type'        => 'String',
                    'description' => __( 'The url of the thumbnail image', 'hkih' ),
                ],
                'medium' => [
                    'type'        => 'String',
                    'description' => __( 'The url of the medium image', 'hkih' ),
                ],
                'medium_large' => [
                    'type'        => 'String',
                    'description' => __( 'The url of the medium large image', 'hkih' ),
                ],
                'large' => [
                    'type'        => 'String',
                    'description' => __( 'The url of the large image', 'hkih' ),
                ],
            ],
        ] );

        register_graphql_object_type( $key, [
            'description' => sprintf(
                /* translators: %s is layout name */
                __( 'Layout: %s', 'hkih' ),
                $key
            ),
            'fields'      => $fields,
        ] );

        \add_filter( 'hkih_graphql_layouts', function ( array $layouts = [] ) use ( $fields, $key ) {
            $layouts[ $key ] = $fields;

            return $layouts;
        } );
    }
}
