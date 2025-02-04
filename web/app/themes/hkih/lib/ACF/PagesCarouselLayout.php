<?php
/**
 * Pages carousel ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use Geniem\Theme\PostType\Page;

/**
 * Class PagesCarouselLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class PagesCarouselLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_pages_carousel';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutPagesCarousel';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Pages carousel', 'hkih-contact' );
        $key   = $key . self::KEY;
        $name  = 'pages_carousel';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'            => [
                'label'        => __( 'Title', 'hkih-contact' ),
                'instructions' => '',
            ],
            'anchor'           => [
                'label'        => __( 'Anchor', 'hkih-contact' ),
                'instructions' => '',
            ],
            'description'      => [
                'label'        => __( 'Description', 'hkih-contact' ),
                'instructions' => '',
            ],
            'background_color' => [
                'label'        => __( 'Background Color', 'hkih-contact' ),
                'instructions' => '',
            ],
            'pages'            => [
                'label'        => __( 'Pages', 'hkih-contact' ),
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
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['title']['instructions'] );

            $anchor_field = ( new Field\Text( $this->strings['anchor']['label'] ) )
                ->set_key( "{$key}_anchor" )
                ->set_name( 'anchor' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['anchor']['instructions'] );

            $description_field = ( new Field\Textarea( $this->strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->set_wrapper_width( 50 )
                ->set_rows( 2 )
                ->set_instructions( $this->strings['description']['instructions'] );

            $background_color_field = ( new Field\Select( $this->strings['background_color']['label'] ) )
                ->set_key( "{$key}_background_color" )
                ->set_name( 'background_color' )
                ->set_choices( [
                    'white' => __( 'White', 'hkih' ),
                    'light' => __( 'Light', 'hkih' ),
                    'dark'  => __( 'Dark', 'hkih' ),
                ] )
                ->use_ui()
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['background_color']['instructions'] );

            $pages_field = ( new Field\Relationship( $this->strings['pages']['label'] ) )
                ->set_key( "{$key}_pages" )
                ->set_name( 'pages' )
                ->set_post_types( [ Page::SLUG ] )
                ->set_filters( [ 'search' ] )
                ->set_instructions( $this->strings['pages']['instructions'] );

            $this->add_fields( [
                $title_field,
                $anchor_field,
                $description_field,
                $background_color_field,
                $pages_field,
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
            'anchor'          => [
                'type'        => 'String',
                'description' => $this->strings['anchor']['label'],
            ],
            'description'     => [
                'type'        => 'String',
                'description' => $this->strings['description']['label'],
            ],
            'backgroundColor' => [
                'type'        => 'String',
                'description' => $this->strings['background_color']['label'],
                'resolve'     => fn( $post ) => $post['background_color'] ?? '',
            ],
            'pages'           => [
                'type'        => [ 'list_of' => Page::SLUG ],
                'description' => $this->strings['pages']['label'],
                'resolve'     => function ( $posts ) {
                    return array_map(
                        fn( $p ) => new \WPGraphQL\Model\Post( $p ),
                        $posts['pages'] ?? []
                    );
                },
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
