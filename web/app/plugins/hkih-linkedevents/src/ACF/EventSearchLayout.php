<?php
/**
 * Event search ACF flexible content layout
 */

namespace HKIH\LinkedEvents\ACF;

use Geniem\ACF\ConditionalLogicGroup;
use Geniem\ACF\Field;
use Geniem\Theme\Logger;
use HKIH\LinkedEvents\API\ApiClient;

/**
 * Class EventSearch
 *
 * @package HKIH\LinkedEvents\ACF
 */
class EventSearchLayout extends Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_event_search';
    /**
     * Translations.
     *
     * @var array[]
     */
    private array $strings;
    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'EventSearch';

    /**
     * Create the layout
     *
     * @param string $key   Key from the flexible content.
     * @param string $label Label for layout.
     * @param string $name  Name for the layout.
     */
    public function __construct( $key = '', $label = 'Event search', $name = 'event_search' ) {
        $label = __( $label, 'hkih-linked-events' );
        $key   = $key . self::KEY;

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'        => [
                'label'        => __( 'Title', 'hkih-linked-events' ),
            ],
            'event_type'   => [
                'label'        => __( 'Event type', 'hkih-linked-events' ),
                'choices'      => [
                    'event'  => __( 'Event', 'hkih-linked-events' ),
                    'course' => __( 'Course', 'hkih-linked-events' ),
                ],
            ],
            'search'       => [
                'label'        => __( 'What are you looking for', 'hkih-linked-events' ),
            ],
            'start_date'   => [
                'label'        => __( 'Start date', 'hkih-linked-events' ),
            ],
            'end_date'     => [
                'label'        => __( 'End date', 'hkih-linked-events' ),
            ],
            'place'        => [
                'label'        => __( 'Location / Place', 'hkih-linked-events' ),
            ],
            'division'        => [
                'label'        => __( 'Division', 'hkih-linked-events' ),
            ],
            'category'     => [
                'label'        => __( 'Category', 'hkih-linked-events' ),
            ],
            'activity'     => [
                'label'        => __( 'Activity', 'hkih-linked-events' ),
            ],
            'age_min'      => [
                'label'        => __( 'Min age', 'hkih-linked-events' ),
            ],
            'age_max'      => [
                'label'        => __( 'Max age', 'hkih-linked-events' ),
            ],
            'children'     => [
                'label'        => __( 'Show only for children', 'hkih-linked-events' ),
            ],
            'init_amount_of_events' => [
                'label'        => __( 'Amount of events to show', 'hkih-linked-events' ),
                'instructions' => 'Amount of events to show before Show more -button. All events will be displayed straight away if left empty.',
            ],
            'free'         => [
                'label'        => __( 'Show only events that are free', 'hkih-linked-events' ),
            ],
            'evening'      => [
                'label'        => __( 'Show only evening events', 'hkih-linked-events' ),
            ],
            'show_all_link'              => [
                'label'        => 'Url of Show all -link',
            ],
            'result_count' => [
                'label'        => __( 'Result count', 'hkih-linked-events' ),
            ],
            'result_link'  => [
                'label'        => __( 'Result link', 'hkih-linked-events' ),
            ],
        ];

        $this->add_layout_fields();

        add_filter(
            'acf/load_field/name=category',
            \Closure::fromCallable( [ $this, 'fill_category_choices' ] )
        );

        add_filter(
            'acf/load_field/name=activity',
            \Closure::fromCallable( [ $this, 'fill_activity_choices' ] )
        );

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] ),
            9
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
                ->add_wrapper_class( 'no-search' )
                ->set_default_value( '' );

            $event_type_field = ( new Field\Radio( $this->strings['event_type']['label'] ) )
                ->set_key( "{$key}_event_type" )
                ->set_name( 'event_type' )
                ->set_choices( $this->strings['event_type']['choices'] )
                ->set_wrapper_width( 50 );

            $course_condition = ( new ConditionalLogicGroup() )
                ->add_rule( $event_type_field, '==', 'course' );

            $search_field = ( new Field\Text( $this->strings['search']['label'] ) )
                ->set_key( "{$key}_search" )
                ->set_name( 'search' );

            $start_date_field = ( new Field\DatePicker( $this->strings['start_date']['label'] ) )
                ->set_key( "{$key}_start_date" )
                ->set_name( 'start_date' )
                ->set_wrapper_width( 25 )
                ->set_display_format( 'd.m.Y' )
                ->set_return_format( 'Ymd' );

            $end_date_field = ( new Field\DatePicker( $this->strings['end_date']['label'] ) )
                ->set_key( "{$key}_end_date" )
                ->set_name( 'end_date' )
                ->set_wrapper_width( 25 )
                ->set_display_format( 'd.m.Y' )
                ->set_return_format( 'Ymd' );

            $place_field = ( new Field\Select( $this->strings['place']['label'] ) )
                ->set_key( "{$key}_place" )
                ->set_name( 'place' )
                ->use_ui()
                ->allow_null()
                ->allow_multiple()
                ->set_wrapper_width( 25 )
                ->set_choices( $this->get_place_choices() );

            $division_field = ( new Field\Select( $this->strings['division']['label'] ) )
                ->set_key( "{$key}_division" )
                ->set_name( 'division' )
                ->use_ui()
                ->allow_null()
                ->allow_multiple()
                ->set_wrapper_width( 25 )
                ->set_choices( [
                    'kunta:helsinki'    => 'Helsinki',
                    'kunta:espoo'       => 'Espoo',
                    'kunta:vantaa'      => 'Vantaa',
                    'kunta:kauniainen'  => 'Kauniainen',
                    'kunta:kirkkonummi' => 'Kirkkonummi',
                ] )
                ->set_default_value( 'kunta:helsinki' );

            $category_field = ( new Field\Select( $this->strings['category']['label'] ) )
                ->set_key( "{$key}_category" )
                ->set_name( 'category' )
                ->use_ui()
                ->use_ajax()
                ->allow_multiple()
                ->set_wrapper_width( 50 );

            $activity_field = ( new Field\Select( $this->strings['activity']['label'] ) )
                ->set_key( "{$key}_activity" )
                ->set_name( 'activity' )
                ->use_ui()
                ->use_ajax()
                ->allow_multiple()
                ->add_conditional_logic( $course_condition )
                ->set_wrapper_width( 50 );

            $age_min_field = ( new Field\Number( $this->strings['age_min']['label'] ) )
                ->set_key( "{$key}_age_min" )
                ->set_name( 'age_min' )
                ->set_wrapper_width( 50 )
                ->add_conditional_logic( $course_condition );

            $age_max_field = ( new Field\Number( $this->strings['age_max']['label'] ) )
                ->set_key( "{$key}_age_max" )
                ->set_name( 'age_max' )
                ->set_wrapper_width( 50 )
                ->add_conditional_logic( $course_condition );

            $children_field = ( new Field\TrueFalse( $this->strings['children']['label'] ) )
                ->set_key( "{$key}_children" )
                ->set_name( 'children' )
                ->set_default_value( false )
                ->use_ui()
                ->set_wrapper_width( 25 );

            $init_amount_of_events_field = ( new Field\Number( $this->strings['init_amount_of_events']['label'] ) )
                ->set_key( "{$key}_init_amount_of_events" )
                ->set_name( 'init_amount_of_events' )
                ->set_wrapper_width( 25 )
                ->add_wrapper_class( 'no-search' )
                ->set_instructions( $this->strings['init_amount_of_events']['instructions'] );

            $free_field = ( new Field\TrueFalse( $this->strings['free']['label'] ) )
                ->set_key( "{$key}_free" )
                ->set_name( 'free' )
                ->set_default_value( false )
                ->use_ui()
                ->set_wrapper_width( 50 );

            $evening_events_field = ( new Field\TrueFalse( $this->strings['evening']['label'] ) )
                ->set_key( "{$key}_evening" )
                ->set_name( 'evening' )
                ->set_default_value( false )
                ->use_ui()
                ->set_wrapper_width( 50 );

            $result_count_field = ( new Field\Text( $this->strings['result_count']['label'] ) )
                ->set_key( "{$key}_result_count" )
                ->set_name( 'result_count' )
                ->set_readonly()
                ->set_wrapper_width( 20 );

            $result_link_field = ( new Field\Text( $this->strings['result_link']['label'] ) )
                ->set_key( "{$key}_result_link" )
                ->set_name( 'result_link' )
                ->set_readonly()
                ->set_wrapper_width( 80 );

            $show_all_link_field = ( new Field\URL( $this->strings['show_all_link']['label'] ) )
                ->set_key( "{$key}_show_all_link" )
                ->set_name( 'show_all_link' )
                ->add_wrapper_class( 'no-search' )
                ->set_wrapper_width( 100 );

            $this->add_fields( [
                $title_field,
                $event_type_field,
                $search_field,
                $start_date_field,
                $end_date_field,
                $place_field,
                $division_field,
                $category_field,
                $activity_field,
                $age_min_field,
                $age_max_field,
                $children_field,
                $init_amount_of_events_field,
                $free_field,
                $evening_events_field,
                $result_link_field,
                $result_count_field,
                $show_all_link_field,
            ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
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
            'url'    => [
                'type'        => 'String',
                'description' => __( 'Search query', 'hkih-linked-events' ),
            ],
            'initAmountOfEvents' => [
                'type'        => 'Integer',
                'description' => __( 'Amount of events listed before "show more -button"', 'hkih-linked-events' ),
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
     * Get place choices
     *
     * @return array
     */
    private function get_place_choices() : array {
        $api_client = new ApiClient();
        $places     = $api_client->get_all_places();
        $choices    = [];

        if ( empty( $places ) ) {
            return $choices;
        }

        foreach ( $places as $place ) {
            if ( ! empty( $place->get_name() ) ) {
                $choices[ $place->get_id() ] = $place->get_name();
            }
        }

        return $choices;
    }

    /**
     * Fill category choices
     *
     * @param array $field ACF field.
     *
     * @return array
     */
    private function fill_category_choices( array $field ) : array {
        $keywords = \Geniem\Theme\Settings::get_setting( 'event_keywords' );
        $key      = 'event_keyword_group_text';

        if ( empty( $keywords ) ) {
            return $field;
        }

        foreach ( $keywords as $keyword ) {
            $field['choices'][ $keyword[ $key ] ] = $keyword[ $key ];
        }

        return $field;
    }

    /**
     * Get activity choices
     *
     * @param array $field ACF field.
     *
     * @return array
     */
    private function fill_activity_choices( array $field ) : array {
        $keywords = \Geniem\Theme\Settings::get_setting( 'course_keywords' );
        $key      = 'course_keyword_group_text';

        if ( empty( $keywords ) ) {
            return $field;
        }

        foreach ( $keywords as $keyword ) {
            $field['choices'][ $keyword[ $key ] ] = $keyword[ $key ];
        }

        return $field;
    }
}
