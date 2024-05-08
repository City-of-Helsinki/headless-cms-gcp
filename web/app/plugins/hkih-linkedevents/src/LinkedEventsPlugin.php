<?php
/**
 * This file initializes all plugin functionalities.
 */

namespace HKIH\LinkedEvents;

use Geniem\ACF\Exception;
use Geniem\ACF\Field\FlexibleContent;
use Geniem\Theme\Logger;
use HKIH\LinkedEvents\ACF\EventSearchCarouselLayout;
use HKIH\LinkedEvents\ACF\EventSearchLayout;
use HKIH\LinkedEvents\ACF\SelectedEventsCarouselLayout;
use HKIH\LinkedEvents\ACF\SelectedEventsLayout;
use HKIH\LinkedEvents\ACF\Settings;
use HKIH\LinkedEvents\API\ApiClient;

/**
 * Class LinkedEventsPlugin
 *
 * @package HKIH\LinkedEvents
 */
final class LinkedEventsPlugin {

    /**
     * Holds the singleton.
     *
     * @var LinkedEventsPlugin
     */
    protected static $instance;

    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';

    /**
     * Get the instance.
     *
     * @return LinkedEventsPlugin
     */
    public static function get_instance() : LinkedEventsPlugin {
        return self::$instance;
    }

    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin root uri without trailing slash.
     *
     * @var string
     */
    protected $plugin_uri = '';

    /**
     * String translations.
     *
     * @var array
     */
    protected $strings = [
        'show_all' => [
            'slug' => 'show_all',
            'text' => 'Show all',
        ],
    ];

    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version() : string {
        return $this->version;
    }

    /**
     * Get the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_path() : string {
        return $this->plugin_path;
    }

    /**
     * Get the plugin directory uri.
     *
     * @return string
     */
    public function get_plugin_uri() : string {
        return $this->plugin_uri;
    }

    /**
     * Initialize the plugin by creating the singleton.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    public static function init( $version, $plugin_path ) {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $version, $plugin_path );
            self::$instance->hooks();
        }
    }

    /**
     * Get the plugin instance.
     *
     * @return LinkedEventsPlugin
     */
    public static function plugin() : LinkedEventsPlugin {
        return self::$instance;
    }

    /**
     * Initialize the plugin functionalities.
     *
     * @param string $version     The current plugin version.
     * @param string $plugin_path The plugin path.
     */
    protected function __construct( $version, $plugin_path ) {
        $this->version     = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_uri  = plugin_dir_url( $plugin_path ) . basename( $this->plugin_path );
    }

    /**
     * Add plugin hooks and filters.
     */
    protected function hooks() : void {
        add_action(
            'init',
            \Closure::fromCallable( [ $this, 'register_pll_strings' ] )
        );

        add_action(
            'acf/include_fields',
            [ $this, 'require_rest_relationship_field' ]
        );

        add_action(
            'admin_enqueue_scripts',
            [ $this, 'enqueue_admin_scripts' ]
        );

        add_action(
            'wp_ajax_event_search',
            [ $this, 'event_search_callback' ]
        );

        add_action(
            'wp_ajax_event_selected',
            [ $this, 'event_selected_callback' ]
        );

        /**
         * Register Event Search REST API response.
         */
        add_filter(
            'hkih_rest_acf_collection_modules_layout_event_search',
            [ $this, 'event_search_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_event_search',
            [ $this, 'event_search_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_event_search',
            [ $this, 'event_search_rest_callback' ]
        );

        /**
         * Adds selected events REST API response.
         */
        add_filter(
            'hkih_rest_acf_collection_modules_layout_event_selected',
            [ $this, 'event_selected_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_event_selected',
            [ $this, 'event_selected_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_event_selected',
            [ $this, 'event_selected_rest_callback' ]
        );

        /**
         * Register Event Search Carousel REST API response.
         */
        add_filter(
            'hkih_rest_acf_collection_modules_layout_event_search_carousel',
            [ $this, 'event_search_carousel_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_event_search_carousel',
            [ $this, 'event_search_carousel_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_event_search_carousel',
            [ $this, 'event_search_carousel_rest_callback' ]
        );

        /**
         * Register Selected events carousel REST API response.
         */
        add_filter(
            'hkih_rest_acf_collection_modules_layout_event_selected_carousel',
            [ $this, 'event_selected_carousel_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_event_selected_carousel',
            [ $this, 'event_selected_carousel_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_event_selected_carousel',
            [ $this, 'event_selected_carousel_rest_callback' ]
        );

        /**
         * Add Collection Modules to these module layouts.
         */
        add_filter(
            'hkih_acf_collection_modules_layouts',
            [ $this, 'add_collection_layouts' ]
        );

        add_filter(
            'hkih_acf_post_modules_layouts',
            [ $this, 'add_collection_layouts' ]
        );

        add_filter(
            'hkih_acf_page_modules_layouts',
            [ $this, 'add_collection_layouts' ]
        );

        \wp_cache_add_global_groups(
            ApiClient::CACHE_GROUP
        );

        ( new Settings() )->hooks();
    }

    /**
     * Register pll strings.
     *
     * @return void
     */
    private function register_pll_strings() : void {
        if ( ! function_exists( 'pll_register_string' ) ) {
            return;
        }

        foreach ( $this->strings as $string ) {
            pll_register_string(
                $string['slug'],
                $string['text'],
            );
        }
    }

    /**
     * Registers rest_relationship ACF Field.
     */
    public function require_rest_relationship_field() : void {
        require_once __DIR__ . '/ACF/Fields/AcfFieldRestRelationship.php';
    }

    /**
     * Enqueue admin side scripts if they exist.
     */
    public function enqueue_admin_scripts() : void {
        $css_path = $this->plugin_path . '/assets/dist/admin.css';
        $js_path  = $this->plugin_path . '/assets/dist/admin.js';

        $css_mod_time = file_exists( $css_path ) ? filemtime( $css_path ) : $this->version;
        $js_mod_time  = file_exists( $js_path ) ? filemtime( $js_path ) : $this->version;

        if ( file_exists( $css_path ) ) {
            wp_enqueue_style(
                'hkih-linked-events-admin-css',
                $this->plugin_uri . '/assets/dist/admin.css',
                [],
                $css_mod_time,
                'all'
            );
        }

        if ( file_exists( $js_path ) ) {
            wp_enqueue_script(
                'hkih-linked-events-admin-js',
                $this->plugin_uri . '/assets/dist/admin.js',
                [ 'jquery', 'acf-input', 'underscore' ],
                $js_mod_time,
                true
            );
        }
    }

    /**
     * Add collection layouts
     *
     * @param FlexibleContent $modules Flexible content object.
     *
     * @return FlexibleContent
     */
    public function add_collection_layouts( FlexibleContent $modules ) : FlexibleContent {
        try {
            $modules->add_layout( new EventSearchLayout( $modules->get_key() ) );
            $modules->add_layout( new SelectedEventsLayout( $modules->get_key() ) );
            $modules->add_layout( new EventSearchCarouselLayout( $modules->get_key() ) );
            $modules->add_layout( new SelectedEventsCarouselLayout( $modules->get_key() ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $modules;
    }

    /**
     * Event AJAX search callback
     */
    public function event_search_callback() : void {
        $event_search = new \HKIH\LinkedEvents\EventSearch();
        $params       = $_GET['params']; // phpcs:ignore

        if ( empty( $params ) ) {
            wp_send_json_success();

            return;
        }

        $params    = $event_search->normalize_search_params( $params );
        $cache_key = md5( json_encode( $params ) );
        $response  = wp_cache_get( $cache_key );

        if ( ! $response ) {
            $response = $event_search->get_search_meta( $params );

            wp_cache_set( $cache_key, $response, '', MINUTE_IN_SECONDS * 2 );
        }

        wp_send_json_success( $response );
    }

    /**
     * Event search REST field layout callback
     *
     * @param array $layout ACF layout data.
     *
     * @return array
     */
    public function event_search_rest_callback( array $layout ) : array {
        $event_search = new \HKIH\LinkedEvents\EventSearch();
        $params       = $event_search->normalize_search_params( $layout );
        $layout_name  = $params['acf_fc_layout'];

        unset( $params['acf_fc_layout'] );

        return [
            'title'              => esc_html( $layout['title'] ),
            'url'                => trim( $event_search->get_search_result_url( $params ) ),
            'showAllLink'        => trim( esc_html( $layout['result_link'] ?? '' ) ),
            'showAllLinkCustom'  => trim( $layout['show_all_link'] ?? '' ),
            'initAmountOfEvents' => esc_html( $layout['init_amount_of_events'] ?: 4 ),
            'module'             => esc_html( $layout_name ),
        ];
    }

    /**
     * Event AJAX search callback
     */
    public function event_selected_callback() : void {
        $params = $_GET['params']; // phpcs:ignore

        if ( empty( $params ) ) {
            wp_send_json_success();

            return;
        }

        try {
            $response = self::event_selected_fetch( $params );

            wp_send_json_success( $response );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
            wp_send_json_error();
        }
    }

    /**
     * Event selected REST field layout callback
     *
     * @param array $layout ACF layout data.
     *
     * @return array
     */
    public function event_selected_rest_callback( array $layout ) : array {
        if ( empty( $layout['selected_events'] ) || ! is_array( $layout['selected_events'] ) ) {
            $layout['selected_events'] = [];
        }

        return [
            'title'              => esc_html( $layout['title'] ),
            'events'             => array_keys( $layout['selected_events'] ?? [] ),
            'initAmountOfEvents' => esc_html( $layout['init_amount_of_events'] ?: 4 ),
            'showAllLink'        => trim( $layout['show_all_link'] ) ?? '',
            'module'             => esc_html( $layout['acf_fc_layout'] ?? '' ),
        ];
    }

    /**
     * Event search carousel REST field layout callback
     *
     * @param array $layout ACF layout data.
     *
     * @return array
     */
    public function event_search_carousel_rest_callback( array $layout ) : array {
        $event_search = new \HKIH\LinkedEvents\EventSearch();
        $params       = $event_search->normalize_search_params( $layout );
        $layout_name  = $params['acf_fc_layout'];

        unset( $params['acf_fc_layout'] );

        $amount_of_cards = $layout['amount_of_cards'];
        $amount_of_cards = empty( $amount_of_cards ) ? 24 : $amount_of_cards;

        $show_all_link = trim( \esc_html( $layout['result_link'] ?? '' ) );

        return [
            'title'             => esc_html( $layout['title'] ),
            'url'               => self::get_event_search_url( $show_all_link ),
            'orderNewestFirst'  => esc_html( $layout['order_newest_first'] ?? false ),
            'amountOfCards'     => esc_html( $amount_of_cards ),
            'eventsNearby'      => esc_html( $layout['events_nearby'] ?? false ),
            'showAllLink'       => $show_all_link,
            'showAllLinkCustom' => trim( $layout['show_all_link'] ?? '' ),
            'module'            => esc_html( $layout_name ),
        ];
    }

    /**
     * Event selected carousel REST field layout callback
     *
     * @param array $layout ACF layout data.
     *
     * @return array
     */
    public function event_selected_carousel_rest_callback( array $layout ) : array {
        if ( empty( $layout['selected_events'] ) || ! is_array( $layout['selected_events'] ) ) {
            $layout['selected_events'] = [];
        }

        $amount_of_cards = $layout['amount_of_cards'];
        $amount_of_cards = empty( $amount_of_cards ) ? 24 : $amount_of_cards;

        return [
            'title'               => esc_html( $layout['title'] ),
            'events'              => array_keys( $layout['selected_events'] ?? [] ),
            'amountOfCards'       => esc_html( $amount_of_cards ),
            'eventsNearby'        => esc_html( $layout['events_nearby'] ?? false ),
            'showAllLink'         => trim( $layout['show_all_link'] ?? '' ),
            'amountOfCardsPerRow' => esc_html( $layout['amount_of_cards_per_row'] ?? 3 ),
            'module'              => esc_html( $layout['acf_fc_layout'] ?? '' ),
        ];
    }

    /**
     * event_selected API Query.
     *
     * @param array $params Fetching parameters.
     *
     * @return array|bool|mixed|string
     * @throws \JsonException Thrown if JSON from API has errors.
     */
    public static function event_selected_fetch( $params = [] ) {
        $event_search = new \HKIH\LinkedEvents\EventSearch();
        $params       = $event_search->normalize_search_params( $params );
        $cache_key    = md5( json_encode( $params, JSON_THROW_ON_ERROR ) ) . '_selected';
        $response     = wp_cache_get( $cache_key );

        if ( ! $response ) {
            $response = $event_search->get_selection_result( $params );

            wp_cache_set( $cache_key, $response, '', MINUTE_IN_SECONDS * 2 );
        }

        return $response;
    }

    /**
     * Convent Show all link to Event search api link.
     *
     * @param string $showAllLink Show all link url.
     *
     * @return string
     */
    public static function get_event_search_url( $show_all_link ) {
        if ( empty( $show_all_link ) ) {
            return '';
        }

        $parts = parse_url( html_entity_decode( $show_all_link ) );
        parse_str( $parts['query'], $query_params );

        return (new EventSearch())->get_search_result_url( $query_params );
    }
}
