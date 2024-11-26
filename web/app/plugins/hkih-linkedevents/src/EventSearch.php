<?php
/**
 * EventSearch
 */

namespace HKIH\LinkedEvents;

use Geniem\Theme\Settings;
use HKIH\CPT\Collection\PostTypes\Collection;
use WPGraphQL\Model\Post;
use Geniem\Theme\Localization;

/**
 * Class EventSearch
 *
 * @package HKIH\LinkedEvents
 */
class EventSearch {

    /**
     * Normalize search params
     *
     * @param array $ajax_params Ajax query params.
     *
     * @return array
     */
    public function normalize_search_params( array $ajax_params ) : array {
        $params = [
            'keyword'          => [],
            'keyword_AND'      => [],
            'location'         => [],
            'division'         => [],
            'sort'             => 'end_time',
            'super_event_type' => 'umbrella,none',
            'language'         => Localization::get_current_language(),
            'page_size'        => 50,
        ];

        foreach ( $ajax_params as $key => $value ) {
            $key    = $this->map_key( $key );
            $params = $this->map_checkbox_values( $key, $value, $params );
            $params = $this->append_course_keywords( $key, $value, $params );
            $params = $this->append_event_keywords( $key, $value, $params );
            $value  = $this->format_dates( $key, $value );

            if ( $this->should_remove_param( $key, $value ) ) {
                continue;
            }

            if ( $key === 'event_type' ) {
                if ( $value === 'event' ) {
                    continue;
                }
                $value = 'Course';
            }

            $params[ $key ] = $value;
        }

        $params['keyword_AND'] = implode( ',', $params['keyword_AND'] );
        $params['keyword']     = implode( ',', $params['keyword'] );
        $params['location']    = implode( ',', $params['location'] );
        $params['division']    = implode( ',', $params['division'] );

        return $this->remove_empty_params( $params );
    }

    /**
     * Map param key
     *
     * @param string $key Param key.
     *
     * @return string
     */
    private function map_key( string $key ) : string {
        $map = [
            'search'     => 'text',
            'start_date' => 'start',
            'end_date'   => 'end',
            'place'      => 'location',
            'age_min'    => 'audience_min_age_gt',
            'age_max'    => 'audience_max_age_lt',
        ];

        return $map[ $key ] ?? $key;
    }

    /**
     * Format date values
     *
     * @param string $key   Param key.
     * @param mixed  $value Param value.
     *
     * @return string
     */
    private function format_dates( string $key, $value = '' ) {
        if ( ! empty( $value ) && ! is_bool( $value ) && in_array( $key, [ 'start', 'end' ], true ) ) {
            $dt = \DateTime::createFromFormat( 'Ymd', $value );
            if ( ! ( $dt instanceof \DateTime ) ) {
                return '';
            }

            $value = $dt->format( 'Y-m-d' );
        }

        if ( empty( $value ) && $key === 'start' ) {
            $value = 'now';
        }

        return $value;
    }

    /**
     * Map checkbox values to params
     *
     * @param string $key    Param key.
     * @param mixed  $value  Param value.
     * @param array  $params Query params.
     *
     * @return mixed
     */
    private function map_checkbox_values( string $key, $value, array $params ) : array {
        if ( (int) $value !== 1 ) {
            return $params;
        }

        if ( $key === 'free' ) {
            $params['is_free'] = 'true';
        }

        if ( $key === 'evening' ) {
            $params['starts_after'] = '16';
        }

        if ( $key === 'children' ) {
            $params['keyword_AND'][] = 'yso:p4354';
        }

        return $params;
    }

    /**
     * Should param be removed
     *
     * @param string $key   Param key.
     * @param mixed  $value Param value.
     *
     * @return bool
     */
    private function should_remove_param( string $key, $value ) : bool {
        $remove = [
            'activity',
            'category',
            'children',
            'evening',
            'result_count',
            'result_link',
            'title',
            'free',
            'selected_events',
            'init_amount_of_events',
            'order_newest_first',
            'amount_of_cards',
            'events_nearby',
            'show_all_link',
            'amount_of_cards_per_row',
        ];

        $remove_if_empty = [
            'audience_min_age_gt',
            'audience_max_age_lt',
        ];

        $in_remove_list          = in_array( $key, $remove, true );
        $in_remove_if_empty_list = empty( $value ) && in_array( $key, $remove_if_empty, true );

        return $in_remove_list || $in_remove_if_empty_list;
    }

    /**
     * Remove empty params.
     *
     * @param array $params Array of params.
     *
     * @return array
     */
    private function remove_empty_params( array $params ) : array {
        return empty( $params )
            ? $params
            : array_filter( $params, fn( $val ) => ! empty( $val ) );
    }

    /**
     * Append selected course keyword values to query params
     *
     * @param string $key    Param key.
     * @param mixed  $value  Selected keyword group texts.
     * @param array  $params Array of query params.
     *
     * @return array
     */
    private function append_course_keywords( string $key, $value, array $params ) : array {
        if ( $key === 'activity' && ! empty( $value ) ) {
            $keywords = Settings::get_setting( 'course_keywords' );

            return $this->append_keywords(
                $value,
                $params,
                $keywords,
                'course_keyword_group_text',
                'course_keyword_group_keywords'
            );
        }

        return $params;
    }

    /**
     * Append selected event keyword values to query params
     *
     * @param string $key    Param key.
     * @param mixed  $value  Selected keyword group texts.
     * @param array  $params Array of query params.
     *
     * @return array
     */
    private function append_event_keywords( string $key, $value, array $params ) : array {
        if ( $key === 'category' && ! empty( $value ) ) {
            $keywords = Settings::get_setting( 'event_keywords' );

            return $this->append_keywords(
                $value,
                $params,
                $keywords,
                'event_keyword_group_text',
                'event_keyword_group_keywords'
            );
        }

        return $params;
    }

    /**
     * Append keywords to params
     *
     * @param array      $value        Selected keyword group texts.
     * @param array      $params       Array of query params.
     * @param array|null $keywords     Array of keywords.
     * @param string     $text         Keywords text.
     * @param string     $keywords_key Keywords key.
     *
     * @return array
     */
    private function append_keywords(
        array $value,
        array $params,
        $keywords,
        $text,
        $keywords_key
    ) : array {
        if ( empty( $keywords ) ) {
            return $params;
        }

        foreach ( $keywords as $keyword ) {
            if ( in_array( $keyword[ $text ], $value, true ) ) {
                foreach ( $keyword[ $keywords_key ] as $keyword_id ) {
                    $params['keyword'][] = $keyword_id;
                }
            }
        }

        return $params;
    }

    /**
     * Get search meta info
     *
     * @param array $params Array of query params.
     *
     * @return array
     */
    public function get_search_meta( array $params ) : array {
        $api      = new API\ApiClient();
        $response = $api->get( 'event', $params );

        return [
            'count' => $response->meta->count,
            'url'   => $this->get_search_result_url( $params ),
        ];
    }

    /**
     * Get search result url
     *
     * @param array $params Array of query params.
     *
     * @return string
     */
    public function get_search_result_url( array $params ) : string {
        $api = new API\ApiClient();

        return $api->get_request_url( 'event', $params );
    }

    /**
     * Get search meta info and events as key-value pairs (id, title).
     *
     * @param array $params Array of query params.
     *
     * @return array
     */
    public function get_selection_result( array $params ) : array {
        $api      = new API\ApiClient();
        $response = $api->get( 'event', $params );

        $data = array_map(
            static fn( $item ) => new API\Entities\Event( $item ),
            $response->data ?? []
        );

        $results = [];
        foreach ( $data as $item ) {
            $id         = $item->get_id();
            $start_time = $this->format_date( $item->get_start_time() ) ?? '';
            $end_time   = $this->format_date( $item->get_end_time() ) ?? '';

            $event_time = trim(
                implode( ' - ', [ $start_time, $end_time ] ),
                "- \t\n\r\0\x0B" // defaults and "-"
            );

            if ( ! empty( $event_time ) ) {
                $event_time = '(' . $event_time . ')';
            }

            $results[ $id ] = [
                'key'   => $id,
                'value' => sprintf(
                    '%s %s (id: %s)',
                    $item->get_name(),
                    $event_time,
                    $id
                ),
            ];
        }

        return [
            'count'   => $response->meta->count,
            'url'     => $this->get_search_result_url( $params ),
            'results' => $results,
        ];
    }

    /**
     * Helper to format DateTime objects to given format.
     *
     * @param \DateTime|null $date   DateTime object, or bust.
     * @param string|null    $format Return format, if empty, use WP default.
     *
     * @return string
     */
    private function format_date( $date = null, $format = '' ) : string {
        if ( ! ( $date instanceof \DateTime ) ) {
            return '';
        }

        $format = empty( $format )
            ? get_option( 'date_format', 'j.n.Y' ) . ' ' . get_option( 'time_format', 'H:i' )
            : $format;

        return $date->format( $format );
    }
}
