<?php
/**
 * Steps ACF Flexible Content Layout
 */

namespace Geniem\Theme\ACF\Layouts;

use Geniem\Theme\Logger;
use Geniem\ACF\Field;
use WPGraphQL\Model\Post;

/**
 * Class StepsLayout
 *
 * @package Geniem\Theme\ACF\Layouts
 */
class StepsLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_steps';

    /**
     * Translation strings.
     *
     * @var array
     */
    private array $strings;

    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutSteps';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Steps', 'hkih' );
        $key   = $key . self::KEY;
        $name  = 'steps';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title' => [
                'label'        => __( 'Title', 'hkih' ),
                'instructions' => '',
            ],
            'description' => [
                'label'        => __( 'Description', 'hkih' ),
                'instructions' => '',
            ],
            'type' => [
                'label'        => __( 'Type', 'hkih' ),
                'instructions' => '',
                'choices'      => [
                    'bullets' => __( 'Bullets', 'hkih' ),
                    'numbers' => __( 'Numbers', 'hkih' ),
                ],
            ],
            'color' => [
                'label'        => __( 'Color', 'hkih' ),
                'instructions' => '',
            ],
            'steps'             => [
                'label'        => __( 'Steps', 'hkih' ),
                'instructions' => '',
            ],
            'step_title'             => [
                'label'        => __( 'Step title', 'hkih' ),
                'instructions' => '',
            ],
            'step_content' => [
                'label'        => __( 'Step content', 'hkih' ),
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
            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_wrapper_width( 100 )
                ->set_instructions( $this->strings['title']['instructions'] );

            $description_field = ( new Field\Wysiwyg( $this->strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->disable_media_upload()
                ->set_toolbar( 'basic' )
                ->set_wrapper_width( 100 )
                ->set_instructions( $this->strings['description']['instructions'] );

            $type_field = ( new Field\Radio( $this->strings['type']['label'] ) )
                ->set_key( "{$key}_type" )
                ->set_name( 'type' )
                ->set_choices( $this->strings['type']['choices'] )
                ->set_wrapper_width( 50 );

            $color_field = ( new Field\Select( $this->strings['color']['label'] ) )
                ->set_key( "{$key}_color" )
                ->set_name( 'color' )
                ->set_choices( \apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->use_ui()
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['color']['instructions'] );

            $steps_field = ( new Field\Repeater( $this->strings['steps']['label'] ) )
                ->set_key( "{$key}_steps" )
                ->set_name( 'steps' )
                ->set_layout( 'block' )
                ->set_instructions( $this->strings['steps']['instructions'] );

            $step_title_field = ( new Field\Wysiwyg( $this->strings['step_title']['label'] ) )
                ->set_key( "{$key}_step_title" )
                ->set_name( 'step_title' )
                ->disable_media_upload()
                ->set_toolbar( 'light' )
                ->set_required()
                ->set_wrapper_width( 100 )
                ->set_instructions( $this->strings['step_title']['instructions'] );

            $step_content_field = ( new Field\Wysiwyg( $this->strings['step_content']['label'] ) )
                ->set_key( "{$key}_step_content" )
                ->set_name( 'step_content' )
                ->disable_media_upload()
                ->set_toolbar( 'basic' )
                ->set_required()
                ->set_wrapper_width( 100 )
                ->set_instructions( $this->strings['step_content']['instructions'] );

            $steps_field->add_field( $step_title_field );
            $steps_field->add_field( $step_content_field );

            $this->add_fields( [
                $title_field,
                $description_field,
                $type_field,
                $color_field,
                $steps_field,
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
            'title' => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
            ],
            'description' => [
                'type'        => 'String',
                'description' => $this->strings['description']['label'],
            ],
            'type' => [
                'type'        => 'String',
                'description' => $this->strings['type']['label'],
            ],
            'color' => [
                'type'        => 'String',
                'description' => $this->strings['color']['label'],
            ],
            'steps' => [
                'type'        => [ 'list_of' => 'Step' ],
                'description' => $this->strings['steps']['label'],
            ],
        ];

        register_graphql_object_type( 'Step', [
            'description' => __( 'Step field', 'hkih' ),
            'fields'      => [
                'title' => [
                    'type'        => 'String',
                    'description' => __( 'The title of the step', 'hkih' ),
                ],
                'content' => [
                    'type'        => 'String',
                    'description' => __( 'The content of the step', 'hkih' ),
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
