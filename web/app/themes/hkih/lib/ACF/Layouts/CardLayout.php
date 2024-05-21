<?php
/**
 * Cards ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;

/**
 * Class CardLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class CardLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_card';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutCard';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Card', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'card';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'image'            => [
                'label'        => __( 'Image', 'hkih' ),
            ],
            'alignment'        => [
                'label'        => __( 'Alignment', 'hkih' ),
            ],
            'title'            => [
                'label'        => __( 'Title', 'hkih' ),
            ],
            'description'      => [
                'label'        => __( 'Description', 'hkih' ),
            ],
            'link'             => [
                'label'        => __( 'Link', 'hkih' ),
            ],
            'background_color' => [
                'label'        => __( 'Background Color', 'hkih' ),
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

            $image_field = ( new Field\Image( __( 'Image', 'hkih-cpt-collection' ) ) )
                ->set_key( "{$key}_image" )
                ->set_name( 'image' )
                ->set_wrapper_width( 50 );

            $alignment_field = ( new Field\Select( $this->strings['alignment']['label'] ) )
                ->set_key( "{$key}_alignment" )
                ->set_name( 'alignment' )
                ->set_choices( [
                    'left'            => __( 'Left', 'hkih' ),
                    'right'           => __( 'Right', 'hkih' ),
                    'center-left'     => __( 'Center left', 'hkih' ),
                    'center-right'    => __( 'Center right', 'hkih' ),
                    'delimited-left'  => __( 'Delimited Left', 'hkih' ),
                    'delimited-right' => __( 'Delimited Right', 'hkih' ),
                ] )
                ->use_ui()
                ->set_wrapper_width( 50 );

            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 );

            $description_field = ( new Field\Textarea( $this->strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->set_wrapper_width( 100 )
                ->set_rows( 4 )
                ->set_new_lines();

            $link_field = ( new Field\Link( $this->strings['link']['label'] ) )
                ->set_key( "{$key}_link" )
                ->set_name( 'link' )
                ->set_wrapper_width( 50 );

            $background_color_field = ( new Field\Select( $this->strings['background_color']['label'] ) )
                ->set_key( "{$key}_background_color" )
                ->set_name( 'background_color' )
                ->set_choices( \apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->allow_null()
                ->use_ui()
                ->set_wrapper_width( 50 );

            $this->add_fields( [
                $image_field,
                $alignment_field,
                $title_field,
                $description_field,
                $link_field,
                $background_color_field,
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
            'image'           => [
                'type'        => 'Image',
                'description' => $this->strings['image']['label'],
            ],
            'alignment'       => [
                'type'        => 'String',
                'description' => $this->strings['alignment']['label'],
            ],
            'title'           => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
            ],
            'description'     => [
                'type'        => 'String',
                'description' => $this->strings['description']['label'],
            ],
            'link'            => [
                'type'        => 'Link',
                'description' => $this->strings['link']['label'],
            ],
            'backgroundColor' => [
                'type'        => 'String',
                'description' => $this->strings['background_color']['label'],
                'resolve'     => fn( $post ) => $post['background_color'] ?? '',
            ],
        ];

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
