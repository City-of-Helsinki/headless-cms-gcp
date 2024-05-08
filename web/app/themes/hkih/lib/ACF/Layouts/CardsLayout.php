<?php
/**
 * Cards ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;

/**
 * Class CardsLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class CardsLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_cards';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutCards';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Cards', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'cards';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'cards'            => [
                'label'        => __( 'Cards', 'hkih' ),
                'instructions' => '',
            ],
            'icon'             => [
                'label'        => __( 'Icon', 'hkih' ),
                'instructions' => '',
            ],
            'title'            => [
                'label'        => __( 'Title', 'hkih' ),
                'instructions' => '',
            ],
            'description'      => [
                'label'        => __( 'Description', 'hkih' ),
                'instructions' => '',
            ],
            'link'             => [
                'label'        => __( 'Link', 'hkih' ),
                'instructions' => '',
            ],
            'background_color' => [
                'label'        => __( 'Background Color', 'hkih' ),
                'instructions' => '',
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

            $cards_field = ( new Field\Repeater( $this->strings['cards']['label'] ) )
                ->set_key( "{$key}_cards" )
                ->set_name( 'cards' )
                ->set_layout( 'block' )
                ->hide_label()
                ->set_instructions( $this->strings['cards']['instructions'] )
                ->set_min( 1 )
                ->set_max( 3 );

            $icon_field = ( new Field\Select( $this->strings['icon']['label'] ) )
                ->set_key( "{$key}_icon" )
                ->set_name( 'icon' )
                ->set_choices( \apply_filters( 'hkih_hds_icons', [] ) )
                ->use_ui()
                ->allow_null()
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['icon']['instructions'] );

            $cards_field->add_field( $icon_field );

            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['title']['instructions'] );

            $cards_field->add_field( $title_field );

            $description_field = ( new Field\Textarea( $this->strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->set_wrapper_width( 100 )
                ->set_rows( 4 )
                ->set_instructions( $this->strings['description']['instructions'] );

            $cards_field->add_field( $description_field );

            $link_field = ( new Field\Link( $this->strings['link']['label'] ) )
                ->set_key( "{$key}_link" )
                ->set_name( 'link' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['link']['instructions'] );

            $cards_field->add_field( $link_field );

            $background_color_field = ( new Field\Select( $this->strings['background_color']['label'] ) )
                ->set_key( "{$key}_background_color" )
                ->set_name( 'background_color' )
                ->set_choices( \apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->use_ui()
                ->allow_null()
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['background_color']['instructions'] );

            $cards_field->add_field( $background_color_field );

            $this->add_fields( [
                $cards_field,
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
            'cards' => [
                'type'        => [ 'list_of' => 'Card' ],
                'description' => $this->strings['cards']['label'],
            ],
        ];

        register_graphql_object_type( 'Card', [
            'description' => __( 'Card field', 'hkih' ),
            'fields'      => [
                'icon' => [
                    'type'        => 'String',
                    'description' => $this->strings['icon']['label'],
                ],
                'title'           => [
                    'type'        => 'String',
                    'description' => $this->strings['title']['label'],
                ],
                'description'     => [
                    'type'        => 'String',
                    'description' => $this->strings['description']['label'],
                ],
                'link' => [
                    'type'        => 'Link',
                    'description' => $this->strings['link']['label'],
                ],
                'backgroundColor' => [
                    'type'        => 'String',
                    'description' => $this->strings['background_color']['label'],
                    'resolve'     => fn( $post ) => $post['background_color'] ?? '',
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
