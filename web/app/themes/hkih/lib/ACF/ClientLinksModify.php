<?php
/**
 * ACF Helper functions.
 */

namespace Geniem\Theme\ACF;

/**
 * Class ClientLinksModify.
 */
class ClientLinksModify {

    /**
     * Home url string.
     *
     * @var string
     */
    public string $home_url;

    /**
     * Client url string.
     *
     * @var string
     */
    public string $client_url;

    /**
     * Construct the class.
     */
    public function __construct() {

        $this->home_url = trailingslashit( \get_home_url() );

        $client_url = \Geniem\Theme\Settings::get_setting( 'client_url' ) ?? false;

        $this->client_url = empty( $client_url ) ? $client_url : trailingslashit( $client_url );

        \add_filter(
            'acf/format_value/type=link',
            \Closure::fromCallable( [ $this, 'maybe_modify_links_by_client_url' ] ),
            10,
            1
        );
    }

    /**
     * Maybe modify links by client url.
     *
     * @param array $value ACF link field value.
     * @return array ACF link field array.
     */
    private function maybe_modify_links_by_client_url( $value ) {

        // Safely get url.
        $url = $value['url'] ?? '';

        // Bail early if url is empty or url is not internal or client_url is empty
        if ( empty( $url ) || strpos( $url, $this->home_url ) === false || empty( $this->client_url ) ) {
            return $value;
        }

        $value['url'] = str_replace( $this->home_url, $this->client_url, $url );

        return $value;
    }
}

new ClientLinksModify();
