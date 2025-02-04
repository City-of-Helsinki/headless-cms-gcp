<?php
/**
 * SelectedEventsCarousel ACF Layout
 */

namespace HKIH\LinkedEvents\ACF;

use \Geniem\Theme\Logger;
use \Geniem\ACF\Field;

/**
 * Class SelectedEventsCarouselLayout
 *
 * @package HKIH\LinkedEvents\ACF
 */
class SelectedEventsCarouselLayout extends EventSearchLayout {

    /**
     * Layout key
     */
    const KEY = '_event_selected_carousel';
    /**
     * Translations.
     *
     * @var array[]
     */
    private array $strings;
    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'EventSelectedCarousel';

    /**
     * SelectedEventsCarouselLayout constructor.
     *
     * @param $key
     */
    public function __construct( $key ) {
        $key = $key . self::KEY;

        parent::__construct( $key, 'Event selection carousel', 'event_selected_carousel' );

        $this->strings = [
            'event_selector'             => [
                'label'        => __( 'Select Events', 'hkih-linked-events' ),
                'instructions' => '',
            ],
            'amount_of_cards'            => [
                'label'        => 'Amount of cards in carousel',
                'instructions' => '',
            ],
            'events_nearby'              => [
                'label'        => 'Events nearby',
                'instructions' => '',
            ],
            'show_all_link'              => [
                'label'        => 'Url of Show all -link',
                'instructions' => '',
            ],
            'amount_of_cards_per_row'    => [
                'label'        => 'Amount of cards per row',
                'instructions' => '',
            ],
        ];

        $this->modify_fields();

        $this->add_event_carousel_fields();

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] ),
            9
        );
    }

    /**
     * Add fields
     *
     * @return void
     */
    private function add_event_carousel_fields() : void {
        $key = $this->get_key();

        try {
            $event_selector = ( new Fields\AcfCodifierRestRelationship( $this->strings['event_selector']['label'] ) )
                ->set_key( "{$key}_selected_events" )
                ->set_name( 'selected_events' )
                ->update_value( fn( $values, $post_id, $field, $raw ) => $raw )
                ->set_instructions( $this->strings['event_selector']['instructions'] );

            $amount_of_cards_field = ( new Field\Number( $this->strings['amount_of_cards']['label'] ) )
                ->set_key( "{$key}_amount_of_cards" )
                ->set_name( 'amount_of_cards' )
                ->set_wrapper_width( 50 )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['amount_of_cards']['instructions'] );

            $events_nearby_field = ( new Field\TrueFalse( $this->strings['events_nearby']['label'] ) )
                ->set_key( "{$key}_events_nearby" )
                ->set_name( 'events_nearby' )
                ->set_default_value( false )
                ->use_ui()
                ->add_wrapper_class( 'no-search' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['events_nearby']['instructions'] );

            $amount_of_cards_per_row_field = ( new Field\Radio( $this->strings['amount_of_cards_per_row']['label'] ) )
                ->set_key( "{$key}_amount_of_cards_per_row" )
                ->set_name( 'amount_of_cards_per_row' )
                ->set_wrapper_width( 50 )
                ->set_choices( [
                    3 => '3 cards',
                    4 => '4 cards',
                ] )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['amount_of_cards_per_row']['instructions'] );

            $show_all_link_field = ( new Field\URL( $this->strings['show_all_link']['label'] ) )
                ->set_key( "{$key}_show_all_link" )
                ->set_name( 'show_all_link' )
                ->add_wrapper_class( 'no-search' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $this->strings['show_all_link']['instructions'] );

            $this->add_fields(
                [
                    $event_selector,
                    $amount_of_cards_field,
                    $events_nearby_field,
                    $amount_of_cards_per_row_field,
                    $show_all_link_field,
                ]
            );
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
            'title'                  => [
                'type'        => 'String',
                'description' => __( 'Module title', 'hkih-linked-events' ),
            ],
            'module'                 => [
                'type'        => 'String',
                'description' => __( 'Module type', 'hkih-linked-events' ),
            ],
            'events'             => [
                'type'        => [ 'list_of' => 'String' ],
                'description' => __( 'List of event IDs', 'hkih-linked-events' ),
            ],
            'amountOfCards'          => [
                'type'        => 'Integer',
                'description' => __( 'Amount of cards in carousel', 'hkih-linked-events' ),
            ],
            'eventsNearby'           => [
                'type'        => 'Boolean',
                'description' => __( 'Events nearby', 'hkih-linked-events' ),
            ],
            'showAllLink'            => [
                'type'        => 'String',
                'description' => __( 'Show all -link', 'hkih-linked-events' ),
            ],
            'amountOfCardsPerRow'    => [
                'type'        => 'Integer',
                'description' => __( 'Amount of cards per row', 'hkih-linked-events' ),
            ],
        ];

        \add_filter( 'hkih_graphql_layouts', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_posttype_collection_modules', \Geniem\Theme\Utils::add_to_layouts( $key, $key ) );
        \add_filter( 'hkih_posttype_page_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
        \add_filter( 'hkih_posttype_post_graphql_modules', \Geniem\Theme\Utils::add_to_layouts( $fields, $key ) );
    }

    /**
     * Modify fields.
     *
     * @return void
     */
    private function modify_fields() : void {

        if ( ! empty( $this->get_field( 'init_amount_of_events' ) ) ) {
            $this->remove_field( 'init_amount_of_events' );
        }

        $event_type_field = $this->get_field( 'event_type' ) ?? null;
        if ( ! empty( $event_type_field ) ) {
            $event_type_field->set_instructions( 'Use only courses in hobbies, in events only events' );
        }
    }
}
