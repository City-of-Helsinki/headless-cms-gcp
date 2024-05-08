<?php
/**
 * LinkedEvents APIClient
 */

namespace HKIH\LinkedEvents\API;

use HKIH\LinkedEvents\API\Entities\Event;
use HKIH\LinkedEvents\API\Entities\Keyword;
use HKIH\LinkedEvents\API\Entities\Place;
use Geniem\Theme\Settings;

/**
 * Class ApiClient
 *
 * @package HKIH\LinkedEvents\API
 */
class ApiClient {

    /**
     * Cache group key.
     */
    const CACHE_GROUP = 'hkih-linked-events';

    /**
     * Create request url.
     *
     * @param string       $base_url Request base url.
     * @param string|array $path     Request path.
     * @param array        $params   Request parameters.
     *
     * @return string Request url
     */
    protected function create_request_url( string $base_url, $path, array $params ) : string {
        if ( is_array( $path ) ) {
            $path = trailingslashit( implode( '/', $path ) );
        }

        $path = trailingslashit( $path );

        if ( empty( $params ) ) {
            $path = trailingslashit( $path );
        }

        return add_query_arg(
            $params,
            sprintf(
                '%s/%s?',
                $base_url,
                $path
            )
        );
    }

    /**
     * Do an API request
     *
     * @param string|array $path   Request path.
     * @param array        $params Request parameters.
     *
     * @return bool|mixed
     */
    public function get( $path, array $params = [] ) {
        $request_url = $this->get_request_url( $path, $params );

        if ( empty( $request_url ) ) {
            return false;
        }

        $response = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return $response;
    }

    /**
     * Get request url
     *
     * @param string|array $path   Request path.
     * @param array        $params Request parameters.
     *
     * @return false|string
     */
    public function get_request_url( string $path, array $params = [] ) {
        $base_url = $this->get_base_url();

        if ( empty( $base_url ) ) {
            return false;
        }

        return $this->create_request_url( $base_url, $path, $params );
    }

    /**
     * Get API base url
     *
     * @return string
     */
    public function get_base_url() : string {
        return Settings::get_setting( 'linked_events_base_url' ) ?: 'https://api.hel.fi/linkedevents/v1';
    }

    /**
     * Do request to 'next' url returned by the API.
     *
     * @param string $request_url Request url.
     *
     * @return false|mixed
     */
    protected function next( string $request_url ) {
        $response = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return false;
    }

    /**
     * Get events
     *
     * @param array $params Event search params.
     *
     * @return Event[]|bool
     */
    public function get_events( array $params = [] ) {
        $cache_key = sprintf(
            'events-%s',
            md5( wp_json_encode( $params ) )
        );

        $response = wp_cache_get( $cache_key, static::CACHE_GROUP );

        if ( $response ) {
            return $response;
        }

        $response = $this->get(
            'event',
            $params
        );

        if ( $response && ! empty( $response->data ) ) {
            $events = array_map( fn( $event ) => new Event( $event ), $response->data );

            wp_cache_set( $cache_key, $events, static::CACHE_GROUP, MINUTE_IN_SECONDS * 5 );
        }

        return $events ?? false;
    }

    /**
     * Get all keywords
     *
     * @return Keyword[]|false
     */
    public function get_all_keywords() {
        $cache_key = 'all-keywords';
        $keywords  = wp_cache_get( $cache_key, static::CACHE_GROUP );

        if ( $keywords ) {
            return $keywords;
        }

        $keywords = $this->do_get_all_keywords();

        if ( $keywords ) {
            \wp_cache_set( $cache_key, $keywords, static::CACHE_GROUP, DAY_IN_SECONDS );
        }

        return $keywords;
    }

    /**
     * Get all keywords
     *
     * @param string $next_url Next url.
     * @param array  $keywords Array of keywords.
     *
     * @return Keyword[]|false
     */
    protected function do_get_all_keywords( $next_url = '', $keywords = [] ) {
        if ( empty( $next_url ) ) {
            $response = $this->get( 'keyword' );
        }
        else {
            $response = $this->next( $next_url );
        }

        if ( $response && ! empty( $response->data ) ) {
            foreach ( $response->data as $data ) {
                $keywords[] = new Keyword( $data );
            }

            if ( ! empty( $response->meta->next ) ) {
                $keywords = $this->do_get_all_keywords(
                    $response->meta->next,
                    $keywords
                );
            }
        }

        return $keywords;
    }

    /**
     * Get all places
     *
     * @return Place[]|false
     */
    public function get_all_places() {
        $cache_key = 'all-places';
        $keywords  = wp_cache_get( $cache_key, static::CACHE_GROUP );

        if ( $keywords ) {
            return $keywords;
        }

        $keywords = $this->do_get_all_places();

        if ( $keywords ) {
            \wp_cache_set( $cache_key, $keywords, static::CACHE_GROUP, DAY_IN_SECONDS );
        }

        return $keywords;
    }

    /**
     * Get all places
     *
     * @param string $next_url Next url.
     * @param array  $places   Array of places.
     *
     * @return Keyword[]|false
     */
    protected function do_get_all_places( $next_url = '', $places = [] ) {
        if ( empty( $next_url ) ) {
            $response = $this->get( 'place' );
        }
        else {
            $response = $this->next( $next_url );
        }

        if ( $response && ! empty( $response->data ) ) {
            foreach ( $response->data as $data ) {
                $places[] = new Keyword( $data );
            }

            if ( ! empty( $response->meta->next ) ) {
                $places = $this->do_get_all_places(
                    $response->meta->next,
                    $places
                );
            }
        }

        return $places;
    }
}
