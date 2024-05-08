<?php
/**
 * LinkedEvents Settings
 */

namespace HKIH\LinkedEvents\ACF;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\Theme\Logger;
use HKIH\LinkedEvents\API\ApiClient;

/**
 * Class Settings
 *
 * @package HKIH\LinkedEvents\ACF
 */
class Settings {

    /**
     * Hooks
     */
    public function hooks() {
        add_filter(
            'hkih_theme_settings',
            Closure::fromCallable( [ $this, 'get_fields' ] ),
            10,
            2
        );

        add_filter(
            'acf/load_field/name=event_keyword_group_keywords',
            Closure::fromCallable( [ $this, 'fill_keywords_as_choices' ] )
        );

        add_filter(
            'acf/load_field/name=course_keyword_group_keywords',
            Closure::fromCallable( [ $this, 'fill_keywords_as_choices' ] )
        );
    }

    /**
     * Get settings fields
     *
     * @param array  $fields Current fields.
     * @param string $key    Field group key.
     *
     * @return array
     */
    protected function get_fields( array $fields, string $key ) : array {
        $strings = [
            'tab'                           => 'Tapahtuma- ja kurssinosto',
            'event_keywords'                => [
                'label'        => 'Tapahtumahaun avainsanaryhmät',
                'instructions' => '',
            ],
            'event_keyword_group_text'      => [
                'label'        => 'Valinnan teksti',
                'instructions' => 'Esim. musiikki',
            ],
            'event_keyword_group_keywords'  => [
                'label'        => 'Valinnan avainsanat',
                'instructions' => '',
            ],
            'course_keywords'               => [
                'label'        => 'Kurssihaun harrastusmuoto',
                'instructions' => '',
            ],
            'course_keyword_group_text'     => [
                'label'        => 'Valinnan teksti',
                'instructions' => 'Esim. ruuanlaitto',
            ],
            'course_keyword_group_keywords' => [
                'label'        => 'Valinnan avainsanat',
                'instructions' => '',
            ],
            'event_search_carousel_search_url' => [
                'label'        => 'Tapahtuma- ja kurssikarusellin hakutulosten osoite',
                'instructions' => '',
            ],
        ];

        try {
            $tab = new Field\Tab( $strings['tab'] );
            $tab->set_placement( 'left' );

            $event_keywords = ( new Field\Repeater( $strings['event_keywords']['label'] ) )
                ->set_key( "${key}_event_keywords" )
                ->set_name( 'event_keywords' )
                ->set_instructions( $strings['event_keywords']['instructions'] );

            $event_keyword_group_text = ( new Field\Text( $strings['event_keyword_group_text']['label'] ) )
                ->set_key( "${key}_event_keyword_group_text" )
                ->set_name( 'event_keyword_group_text' )
                ->set_instructions( $strings['event_keyword_group_text']['instructions'] );

            $event_keyword_group_keywords = ( new Field\Select( $strings['event_keyword_group_keywords']['label'] ) )
                ->set_key( "${key}_event_keyword_group_keywords" )
                ->set_name( 'event_keyword_group_keywords' )
                ->use_ui()
                ->allow_multiple()
                ->use_ajax()
                ->set_instructions( $strings['event_keyword_group_keywords']['instructions'] );

            $event_keywords->add_fields( [
                $event_keyword_group_text,
                $event_keyword_group_keywords,
            ] );

            $tab->add_field( $event_keywords );

            $course_keywords = ( new Field\Repeater( $strings['course_keywords']['label'] ) )
                ->set_key( "${key}_course_keywords" )
                ->set_name( 'course_keywords' )
                ->set_instructions( $strings['course_keywords']['instructions'] );

            $course_keyword_group_text = ( new Field\Text( $strings['course_keyword_group_text']['label'] ) )
                ->set_key( "${key}_course_keyword_group_text" )
                ->set_name( 'course_keyword_group_text' )
                ->set_instructions( $strings['course_keyword_group_text']['instructions'] );

            $course_keyword_group_keywords = ( new Field\Select( $strings['course_keyword_group_keywords']['label'] ) )
                ->set_key( "${key}_course_keyword_group_keywords" )
                ->set_name( 'course_keyword_group_keywords' )
                ->use_ui()
                ->allow_multiple()
                ->use_ajax()
                ->set_instructions( $strings['course_keyword_group_keywords']['instructions'] );

            $course_keywords->add_fields( [
                $course_keyword_group_text,
                $course_keyword_group_keywords,
            ] );

            $tab->add_field( $course_keywords );

            $event_search_carousel_search_url = ( new Field\Text( $strings['event_search_carousel_search_url']['label'] ) )
                ->set_key( "${key}_event_search_carousel_search_url" )
                ->set_name( 'event_search_carousel_search_url' )
                ->set_instructions( $strings['event_search_carousel_search_url']['instructions'] );


            $tab->add_field( $event_search_carousel_search_url );

            $fields[] = $tab;
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }

        return $fields;
    }

    /**
     * Get LinkedEvents keywords as field choices
     *
     * @param array $field The field array containing all settings.
     *
     * @return array
     */
    protected function fill_keywords_as_choices( array $field ) : array {
        $api_client = new ApiClient();
        $keywords   = $api_client->get_all_keywords();

        foreach ( $keywords as $keyword ) {
            $field['choices'][ $keyword->get_id() ] = sprintf(
                '%s - %s',
                $keyword->get_name(),
                $keyword->get_id()
            );
        }

        return $field;
    }
}
