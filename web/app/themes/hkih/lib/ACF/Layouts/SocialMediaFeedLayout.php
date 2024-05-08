<?php
/**
 * SocialMediaFeedLayout ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;

/**
 * Class SocialMediaFeedLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class SocialMediaFeedLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_social_media_feed';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutSocialMediaFeed';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Social Media Feed', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'social_media_feed';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'            => [
                'label'        => __( 'Title', 'hkih-contact' ),
            ],
            'anchor'           => [
                'label'        => __( 'Anchor', 'hkih-contact' ),
            ],
            'script' => [
                'label'        => __( 'Script', 'hkih-contact' ),
                'instructions' => 'Set Flockler embed code here.',
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
            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 50 );

            $anchor_field = ( new Field\Text( $this->strings['anchor']['label'] ) )
                ->set_key( "{$key}_anchor" )
                ->set_name( 'anchor' )
                ->set_wrapper_width( 50 );

            $script_field = ( new Field\Textarea( $this->strings['script']['label'] ) )
                ->set_key( "{$key}_script" )
                ->set_name( 'script' )
                ->set_wrapper_width( 100 )
                ->set_instructions( $this->strings['script']['instructions'] );

            $this->add_fields( [
                $title_field,
                $anchor_field,
                $script_field,
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
            'script'          => [
                'type'        => 'String',
                'description' => $this->strings['script']['label'],
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
