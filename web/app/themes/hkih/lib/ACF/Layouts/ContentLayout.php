<?php
/**
 * Articles ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use WPGraphQL\Model\Post;

/**
 * Class ContentLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class ContentLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_content';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutContent';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Content', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'content';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'            => [
                'label'        => __( 'Title', 'hkih' ),
            ],
            'content'      => [
                'label'        => __( 'Content', 'hkih' ),
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
            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 );

            $background_color_field = ( new Field\Select( $this->strings['background_color']['label'] ) )
                ->set_key( "{$key}_background_color" )
                ->set_name( 'background_color' )
                ->allow_null()
                ->set_choices( \apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->set_wrapper_width( 50 );

            $content_field = ( new Field\Wysiwyg( $this->strings['content']['label'] ) )
                ->set_key( "{$key}_content" )
                ->set_name( 'content' )
                ->disable_media_upload()
                ->set_toolbar( 'basic' )
                ->set_wrapper_width( 100 );

            $this->add_fields( [
                $title_field,
                $background_color_field,
                $content_field,
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
            'backgroundColor' => [
                'type'        => 'String',
                'description' => $this->strings['background_color']['label'],
                'resolve'     => fn( $post ) => $post['background_color'] ?? '',
            ],
            'content'         => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
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
