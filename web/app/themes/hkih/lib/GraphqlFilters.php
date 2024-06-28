<?php
/**
 * GraphqlFilters
 */

namespace Geniem\Theme;

/**
 * Class GraphqlFilters
 *
 * @package Geniem\Theme
 */
class GraphqlFilters implements Interfaces\Controller {

    /**
     * Hooks
     */
    public function hooks() : void {
        \add_filter( 'graphql_html_entity_decoding_enabled', '__return_true' );
    }
}
