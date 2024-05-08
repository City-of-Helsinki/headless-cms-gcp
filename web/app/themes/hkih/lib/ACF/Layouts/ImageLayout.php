<?php
/**
 * Image ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;

/**
 * Class ImageLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class ImageLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_image';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutImage';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Image', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'image';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'image' => [
                'label' => __( 'Image', 'hkih' ),
            ],
            'border' => [
                'label' => __( 'Border', 'hkih' ),
            ],
            'lightbox' => [
                'label' => __( 'Lightbox', 'hkih' ),
            ],
            'photographer_name' => [
                'label' => __( 'Photographer name (overwrite)', 'hkih' ),
            ],
            'lightbox' => [
                'label' => __( 'Show on lightbox', 'hkih' ),
            ],
        ];

        $this->add_layout_fields();

        \add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] )
        );

        // Photographer name must be set.
        \add_filter(
            'acf/validate_value/key=' . $this->get_key() . '_photographer_name',
            \Closure::fromCallable( [ $this, 'validate_photographer_name' ] ),
            10, 4
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

            $image_field = ( new Field\Image( $this->strings['image']['label'] ) )
                ->set_key( "{$key}_image" )
                ->set_name( 'image' )
                ->set_required()
                ->set_wrapper_width( 33 );

            $border_field = ( new Field\TrueFalse( $this->strings['border']['label'] ) )
                ->set_key( "{$key}_border" )
                ->set_name( 'border' )
                ->set_default_value( false )
                ->use_ui()
                ->set_wrapper_width( 33 );

            $photographer_name_field = ( new Field\Text( $this->strings['photographer_name']['label'] ) )
                ->set_key( "{$key}_photographer_name" )
                ->set_name( 'photographer_name' )
                ->set_wrapper_width( 33 );

            $show_on_lightbox_field = ( new Field\TrueFalse( $this->strings['lightbox']['label'] ) )
                ->set_key( "{$key}_show_on_lightbox" )
                ->set_name( 'show_on_lightbox' )
                ->use_ui()
                ->set_wrapper_width( 50 );

            $this->add_fields( [
                $image_field,
                $border_field,
                $photographer_name_field,
                $show_on_lightbox_field,
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
            'image'            => [
                'type'          => 'Image',
                'description'   => $this->strings['image']['label'],
            ],
            'border'            => [
                'type'          => 'Boolean',
                'description'   => $this->strings['border']['label'],
            ],
            'show_on_lightbox'  => [
                'type'          => 'Boolean',
                'description'   => $this->strings['lightbox']['label'],
            ],
            'photographer_name' => [
                'type'          => 'String',
                'description'   => $this->strings['photographer_name']['label'],
            ],
        ];

        register_graphql_object_type( 'Image', [
            'description' => __( 'Image', 'hkih' ),
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

    /**
     *
     * Validate photographer name.
     *
     * @param mixed  $valid Whether or not the value is valid (boolean) or a custom error message (string).
     * @param mixed  $value The field value.
     * @param array  $field The field array containing all settings.
     * @param string $input_name The field DOM element name attribute.
     * @return mixed
     */
    private function validate_photographer_name( $valid, $value, $field, $input_name ) {

        // Bail early if value is already invalid.
        if ( ! $valid ) {
            return $valid;
        }

        $photographer_name = '';

        // Convert input name as array.
        $array = preg_split( '/\[|\]\[/', $input_name );

        // Get image ID from POST array.
        $image_id = $_POST[ $array[0] ][ $array[1] ][ $array[2] ][ $this->get_key() . '_image' ] ?? null;

        if ( ! empty( $image_id ) ) {
            $photographer_name = \get_field( 'photographer_name', $image_id );
        }

        if ( empty( $photographer_name ) && empty( $value ) ) {
            $valid = __( 'Set photographers name to image or overwrite it by filling this field.', 'hkih' );
        }

        return $valid;
    }
}
