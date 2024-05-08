<?php
/**
 * EventSearchCarouselLayout ACF Layout
 */

namespace HKIH\LinkedEvents\ACF;

use \Geniem\Theme\Logger;
use \HKIH\LinkedEvents\ACF\Fields;
use \Geniem\ACF\Field;

/**
 * Class EventSearchCarouselLayout
 *
 * @package HKIH\LinkedEvents\ACF
 */
class EventSearchCarouselLayout extends EventSearchLayout {

    /**
     * Layout key
     */
    const KEY = '_event_search_carousel';
    /**
     * Translations.
     *
     * @var array[]
     */
    private array $strings;
    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'EventSearchCarousel';

    /**
     * EventSearchCarouselLayout constructor.
     *
     * @param $key
     */
    public function __construct( $key ) {
        $key = $key . self::KEY;

        parent::__construct( $key, 'Event search carousel', 'event_search_carousel' );

        $this->strings = [
            'order_newest_first' => [
                'label'        => 'Show newest first',
                'instructions' => '',
            ],
            'amount_of_cards'    => [
                'label'        => 'Amount of cards in carousel',
                'instructions' => '',
            ],
            'events_nearby'    => [
                'label'        => 'Events nearby',
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
            $order_newest_first_field = ( new Field\TrueFalse( $this->strings['order_newest_first']['label'] ) )
                ->set_key( "${key}_order_newest_first" )
                ->set_name( 'order_newest_first' )
                ->set_default_value( false )
                ->use_ui()
                ->add_wrapper_class( 'no-search' )
                ->set_wrapper_width( 33 )
                ->set_instructions( $this->strings['order_newest_first']['instructions'] );

            $amount_of_cards_field = ( new Field\Number( $this->strings['amount_of_cards']['label'] ) )
                ->set_key( "${key}_amount_of_cards" )
                ->set_name( 'amount_of_cards' )
                ->set_wrapper_width( 33 )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['amount_of_cards']['instructions'] );

            $events_nearby_field = ( new Field\TrueFalse( $this->strings['events_nearby']['label'] ) )
                ->set_key( "${key}_events_nearby" )
                ->set_name( 'events_nearby' )
                ->set_default_value( false )
                ->use_ui()
                ->add_wrapper_class( 'no-search' )
                ->set_wrapper_width( 34 )
                ->set_instructions( $this->strings['events_nearby']['instructions'] );

            $this->add_fields(
                [
                    $order_newest_first_field,
                    $amount_of_cards_field,
                    $events_nearby_field,
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
            'title'  => [
                'type'        => 'String',
                'description' => __( 'Module title', 'hkih-linked-events' ),
            ],
            'module' => [
                'type'        => 'String',
                'description' => __( 'Module type', 'hkih-linked-events' ),
            ],
            'orderNewestFirst' => [
                'type'        => 'Boolean',
                'description' => __( 'Events order', 'hkih-linked-events' ),
            ],
            'url'              => [
                'type'        => 'String',
                'description' => __( 'Search query', 'hkih-linked-events' ),
            ],
            'amountOfCards'    => [
                'type'        => 'Integer',
                'description' => __( 'Amount of cards in carousel', 'hkih-linked-events' ),
            ],
            'eventsNearby'    => [
                'type'        => 'Boolean',
                'description' => __( 'Events nearby', 'hkih-linked-events' ),
            ],
            'showAllLink'    => [
                'type'        => 'String',
                'description' => __( 'Show all -link, final link is combination of Tapahtuma- ja kurssikarusellin
                                    hakutulosten osoite -link and search params of the module, for example:
                                    https://client-url.com/search/?sort=end_time&amp;super_event_type=umbrella,none&amp;language=fi&amp;start=2022-10-29
                                    ', 'hkih-linked-events' ),
            ],
            'showAllLinkCustom'    => [
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
