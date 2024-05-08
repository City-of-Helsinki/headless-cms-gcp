<?php
/**
 * SelectedEvents ACF Layout
 */

namespace HKIH\LinkedEvents\ACF;

use \Geniem\Theme\Logger;
use \HKIH\LinkedEvents\ACF\Fields;
use \Geniem\ACF\Field;

/**
 * Class SelectedEventsLayout
 *
 * @package HKIH\LinkedEvents\ACF
 */
class SelectedEventsLayout extends EventSearchLayout {

    /**
     * Layout key
     */
    const KEY = '_event_selected';
    /**
     * Translations.
     *
     * @var array[]
     */
    private array $strings;
    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'EventSelected';

    /**
     * SelectedEventsLayout constructor.
     *
     * @param $key
     */
    public function __construct( $key ) {
        $key = $key . self::KEY;

        parent::__construct( $key, 'Event selection', 'event_selected' );

        $this->strings = [
            'event_selector' => [
                'label'        => __( 'Select Events', 'hkih-linked-events' ),
                'instructions' => '',
            ],
            'show_all_link'  => [
                'label'        => 'Url of Show all -link',
                'instructions' => '',
            ],
        ];

        $this->add_selection_fields();

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] ),
            9
        );
    }

    private function add_selection_fields() : void {
        $key = $this->get_key();

        try {
            $event_selector = ( new Fields\AcfCodifierRestRelationship( $this->strings['event_selector']['label'] ) )
                ->set_key( "${key}_selected_events" )
                ->set_name( 'selected_events' )
                ->update_value( fn( $values, $post_id, $field, $raw ) => $raw )
                ->set_instructions( $this->strings['event_selector']['instructions'] );

            $this->add_field( $event_selector );


            $show_all_link_field = ( new Field\URL( $this->strings['show_all_link']['label'] ) )
                ->set_key( "${key}_show_all_link" )
                ->set_name( 'show_all_link' )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['show_all_link']['instructions'] );

            $this->add_field( $show_all_link_field );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }
    }

    /**
     * Register Layout fields to GraphQL.
     */
    public function register_graphql_fields() : void {
        $key = self::GRAPHQL_LAYOUT_KEY;

        // If the layout is already known/initialized, no need to register it again.
        if ( array_key_exists( $key, \apply_filters( 'hkih_graphql_layouts', [] ) ) ) {
            return;
        }

        $fields = [
            'title'              => [
                'type'        => 'String',
                'description' => __( 'Module title', 'hkih-linked-events' ),
            ],
            'module'             => [
                'type'        => 'String',
                'description' => __( 'Module type', 'hkih-linked-events' ),
            ],
            'events'             => [
                'type'        => [ 'list_of' => 'String' ],
                'description' => __( 'List of event IDs', 'hkih-linked-events' ),
            ],
            'initAmountOfEvents' => [
                'type'        => 'Integer',
                'description' => __( 'Amount of events listed before "show more -button"', 'hkih-linked-events' ),
            ],
            'showAllLink'        => [
                'type'        => 'String',
                'description' => __( 'Show all -link', 'hkih-linked-events' ),
            ],
        ];

        \add_filter( 'hkih_graphql_layouts', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_posttype_collection_modules', \Geniem\Theme\Utils::add_to_layouts( $key, $key ) );
        \add_filter( 'hkih_posttype_page_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_posttype_post_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
    }
}
