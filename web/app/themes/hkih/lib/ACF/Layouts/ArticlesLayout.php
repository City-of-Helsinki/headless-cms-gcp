<?php
/**
 * Articles ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\Theme\PostType\Post;
use Geniem\Theme\Taxonomy\Category;
use Geniem\Theme\Taxonomy\PostTag;
use Geniem\ACF\Field;

/**
 * Class ArticlesLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class ArticlesLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_articles';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutArticles';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Articles', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'articles';

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
            'background_color' => [
                'label'        => __( 'Background Color', 'hkih-contact' ),
                'instructions' => '',
            ],
            'category'         => [
                'label'        => __( 'Category', 'hkih-contact' ),
                'instructions' => '',
            ],
            'tag'              => [
                'label'        => __( 'Tag', 'hkih-contact' ),
                'instructions' => '',
            ],
            'limit'            => [
                'label'        => __( 'Tag', 'hkih-contact' ),
                'instructions' => '',
            ],
            'show_all_link'    => [
                'label'        => 'Url of Show all -link',
                'instructions' => '',
            ],
            'articles'         => [
                'label'        => __( 'Articles', 'hkih-contact' ),
                'instructions' => 'Bypass article search',
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

            $category_field = ( new Field\Taxonomy( $this->strings['category']['label'] ) )
                ->set_key( "${key}_category" )
                ->set_name( 'category' )
                ->set_taxonomy( Category::SLUG )
                ->allow_null()
                ->set_return_format( 'id' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['category']['instructions'] );

            $tag_field = ( new Field\Taxonomy( $this->strings['tag']['label'] ) )
                ->set_key( "${key}_tag" )
                ->set_name( 'tag' )
                ->set_taxonomy( PostTag::SLUG )
                ->allow_null()
                ->set_return_format( 'id' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['tag']['instructions'] );

            $limit_field = ( new Field\Number( $this->strings['limit']['label'] ) )
                ->set_key( "${key}_limit" )
                ->set_name( 'limit' )
                ->set_default_value( 6 )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['limit']['instructions'] );

            $show_all_link_field = ( new Field\URL( $this->strings['show_all_link']['label'] ) )
                ->set_key( "${key}_show_all_link" )
                ->set_name( 'show_all_link' )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['show_all_link']['instructions'] );

            $articles_field = ( new Field\Relationship( $this->strings['articles']['label'] ) )
                ->set_key( "${key}_articles" )
                ->set_name( 'articles' )
                ->set_post_types( [ Post::SLUG ] )
                ->set_filters( [ 'search' ] )
                ->set_instructions( $this->strings['articles']['instructions'] );

            $this->add_fields( [
                $title_field,
                $anchor_field,
                $background_color_field,
                $category_field,
                $tag_field,
                $limit_field,
                $show_all_link_field,
                $articles_field,
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
            'backgroundColor' => [
                'type'        => 'String',
                'description' => $this->strings['background_color']['label'],
                'resolve'     => fn( $post ) => $post['background_color'] ?? '',
            ],
            'category'        => [
                'type'        => 'Integer',
                'description' => $this->strings['category']['label'],
            ],
            'tag'             => [
                'type'        => 'Integer',
                'description' => $this->strings['tag']['label'],
            ],
            'limit'           => [
                'type'        => 'Integer',
                'description' => $this->strings['limit']['label'],
            ],
            'showAllLink'     => [
                'type'        => 'String',
                'description' => __( 'Show all -link', 'hkih-linked-events' ),
                'resolve'     => fn( $post ) => $post['show_all_link'] ?? '',
            ],
            'articles'        => [
                'type'        => [ 'list_of' => Post::SLUG ],
                'description' => $this->strings['articles']['label'],
                'resolve'     => function ( $articles ) {
                    return array_map(
                        fn( $a ) => new \WPGraphQL\Model\Post( $a ),
                        $articles['articles'] ?? []
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
