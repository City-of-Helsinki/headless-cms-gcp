<?php
/**
 * Articles ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use WPGraphQL\Model\Post;

/**
 * Class LinkListLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class LinkListLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_link_list';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutLinkList';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Link List', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'link_list';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'            => [
                'label'        => __( 'Title', 'hkih' ),
                'instructions' => '',
            ],
            'description'      => [
                'label'        => __( 'Description', 'hkih' ),
                'instructions' => '',
            ],
            'anchor'           => [
                'label'        => __( 'Anchor', 'hkih' ),
                'instructions' => '',
            ],
            'background_color' => [
                'label'        => __( 'Background Color', 'hkih' ),
                'instructions' => '',
            ],
            'links'            => [
                'label'        => __( 'Links', 'hkih' ),
                'instructions' => '',
            ],
            'link'             => [
                'label'        => __( 'Link', 'hkih' ),
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
            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "${key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['title']['instructions'] );

            $description_field = ( new Field\Textarea( $this->strings['description']['label'] ) )
                ->set_key( "${key}_description" )
                ->set_name( 'description' )
                ->set_wrapper_width( 50 )
                ->set_rows( 4 )
                ->set_new_lines()
                ->set_instructions( $this->strings['description']['instructions'] );

            $anchor_field = ( new Field\Text( $this->strings['anchor']['label'] ) )
                ->set_key( "${key}_anchor" )
                ->set_name( 'anchor' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['anchor']['instructions'] );

            $background_color_field = ( new Field\Select( $this->strings['background_color']['label'] ) )
                ->set_key( "${key}_background_color" )
                ->set_name( 'background_color' )
                ->set_choices( [
                    'white' => __( 'White', 'hkih' ),
                    'light' => __( 'Light', 'hkih' ),
                    'dark'  => __( 'Dark', 'hkih' ),
                ] )
                ->use_ui()
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['background_color']['instructions'] );

            $links_field = ( new Field\Repeater( $this->strings['links']['label'] ) )
                ->set_key( "${key}_links" )
                ->set_name( 'links' )
                ->set_instructions( $this->strings['links']['instructions'] );

            $link_field = ( new Field\Link( $this->strings['link']['label'] ) )
                ->set_key( "${key}_link" )
                ->set_name( 'link' )
                ->set_instructions( $this->strings['link']['instructions'] );

            $links_field->add_field( $link_field );

            $this->add_fields( [
                $title_field,
                $description_field,
                $anchor_field,
                $background_color_field,
                $links_field,
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
            'title'           => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
            ],
            'description'     => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
            ],
            'anchor'          => [
                'type'        => 'String',
                'description' => $this->strings['anchor']['label'],
            ],
            'backgroundColor' => [
                'type'        => 'String',
                'description' => $this->strings['background_color']['label'],
                'resolve'     => fn( $post ) => $post['background_color'] ?? '',
            ],
            'links'           => [
                'type'        => [ 'list_of' => 'Link' ],
                'description' => $this->strings['links']['label'],
                'resolve'     => function ( $data ) {
                    return array_map( fn( $i ) => $i['link'], $data['links'] ?? [] );
                },
            ],
        ];

        register_graphql_object_type( 'Link', [
            'description' => __( 'Link field', 'hkih' ),
            'fields'      => [
                'url'    => [
                    'type'        => 'String',
                    'description' => __( 'The url of the link', 'hkih' ),
                ],
                'title'  => [
                    'type'        => 'String',
                    'description' => __( 'The title of the link', 'hkih' ),
                ],
                'target' => [
                    'type'        => 'String',
                    'description' => __( 'The target of the link', 'hkih' ),
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
