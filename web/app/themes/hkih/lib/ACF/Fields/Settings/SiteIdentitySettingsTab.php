<?php

namespace Geniem\Theme\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use \Geniem\Theme\Logger;

/**
 * Class SiteIdentitySettingsTab
 */
class SiteIdentitySettingsTab extends Tab {

    /**
     * Where should the tab switcher be located
     *
     * @var string
     */
    protected $placement = 'left';

    /**
     * The constructor for tab.
     *
     * @param string $label Label.
     * @param null   $key   Key.
     * @param null   $name  Name.
     */
    public function __construct( $label = '', $key = null, $name = null ) { // phpcs:ignore
        $label = __( 'Site Identity', 'hkih' );

        parent::__construct( $label );

        $this->sub_fields( $key );

        \add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] ),
        );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        try {
            $current_blog_id = \get_current_blog_id();

            $site_name_title        = __( 'Site Title' );
            $site_name_instructions = sprintf(
            /* translators: %s is a link to General Settings page with text 'General Settings' */
                __( 'This can be controlled from %s', 'hkih' ),
                sprintf(
                    '<a href="%s">%s</a>',
                    get_admin_url( $current_blog_id, 'options-general.php' ),
                    __( 'General Settings', 'default' )
                )
            );

            $site_name = ( new Field\Text( $site_name_title ) )
                ->set_instructions( $site_name_instructions )
                ->set_key( $key . '_site_name' )
                ->set_name( 'site_name' )
                ->set_default_value( \get_blog_option( $current_blog_id, 'blogname' ) )
                ->load_value( function () use ( $current_blog_id ) {
                    return \get_blog_option( $current_blog_id, 'blogname' );
                } )
                ->update_value( function () use ( $current_blog_id ) {
                    return \get_blog_option( $current_blog_id, 'blogname' );
                } )
                ->set_readonly();

            $logo = ( new Field\Image( __( 'Logo', 'hkih' ) ) )
                ->set_key( $key . '_logo' )
                ->set_name( 'logo' )
                ->set_return_format( 'id' )
                ->set_wrapper_width( 50 );

            $wave_motif = ( new Field\Select( __( 'Default wave motif', 'hkih' ) ) )
                ->set_key( $key . '_wave_motif' )
                ->set_name( 'wave_motif' )
                ->allow_null()
                ->set_choices( \apply_filters( 'hkih_wave_motifs', [] ) )
                ->set_wrapper_width( 50 );

            $client_url = ( new Field\URL( __( 'Client Url', 'hkih' ) ) )
                ->set_key( $key . '_client_url' )
                ->set_name( 'client_url' );

            $revalidate_url_production = ( new Field\URL( __( 'Revalidate Url (production)', 'hkih' ) ) )
                ->set_key( $key . '_revalidate_url_production' )
                ->set_name( 'revalidate_url_production' );

            $revalidate_url_staging = ( new Field\URL( __( 'Revalidate Url (staging)', 'hkih' ) ) )
                ->set_key( $key . '_revalidate_url_staging' )
                ->set_name( 'revalidate_url_staging' );

            $revalidate_url_testing = ( new Field\URL( __( 'Revalidate Url (testing)', 'hkih' ) ) )
                ->set_key( $key . '_revalidate_url_testing' )
                ->set_name( 'revalidate_url_testing' );

            $linked_events_base_url = ( new Field\URL( __( 'LinkedEvents base url overwrite', 'hkih' ) ) )
                ->set_key( $key . '_linked_events_base_url' )
                ->set_name( 'linked_events_base_url' )
                ->set_instructions( 'Default is: https://api.hel.fi/linkedevents/v1' );

            $preview_url = ( new Field\URL( __( 'Preview URL', 'hkih' ) ) )
                ->set_key( $key . '_preview_url' )
                ->set_name( 'preview_url' )
                ->set_instructions( 'Params will be appended to url ?&uri={$uri}&token={$token}' );

            $this->add_fields( apply_filters(
                'hkih_theme_settings_identity', [
                    $site_name,
                    $logo,
                    $wave_motif,
                    $client_url,
                    $revalidate_url_production,
                    $revalidate_url_staging,
                    $revalidate_url_testing,
                    $linked_events_base_url,
                    $preview_url,
                ],
                $key
            ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Registers GraphQL Fields from our Settings.
     */
    public function register_graphql_fields() : void {
        if ( ! class_exists( 'WPGraphQL' ) ) {
            return;
        }

        /**
         * Fields we are registering into our SiteSettings GraphQL object.
         * You can use 'field_name' or 'callback' for resolver.
         *
         * Callback should be an anonymous function that returns the value.
         * The callback should expect one argument, language slug.
         * 'field_name' is the ACF Settings field name you called your setting.
         *
         * Simple as that.
         * Oh and the array key is used as it's GraphQL name. Please use camelCase.
         *
         * See the two ways in practice below.
         */
        $setting_fields = [
            'siteName' => [
                'type'        => 'String',
                'description' => 'Identifying name',
                'field_name'  => 'site_name',
            ],
            'logo'     => [
                'type'        => 'String',
                'description' => 'Attachment ID for logo',
                'callback'    => function ( $lang = '' ) {
                    $logo_id = \Geniem\Theme\Settings::get_setting( 'logo', $lang ) ?? false;
                    $logo    = wp_get_attachment_image_url( $logo_id, 'full' );

                    return $logo ?? '';
                },
            ],
            'redirects'     => [
                'type'        => 'String',
                'description' => 'Redirects',
                'callback'    => function ( $lang = '' ) {
                    $redirects = \Geniem\Theme\Settings::get_setting( 'redirects', $lang ) ?? [];

                    if ( empty( $redirects ) ) {
                        return [];
                    }

                    $redirects = array_map( function ( $redirect ) {
                        return [
                            $redirect['redirect_from_uri'] => $redirect['redirect_to_uri']
                        ];
                    }, $redirects );

                    return json_encode( $redirects, JSON_UNESCAPED_SLASHES );
                },
            ],
        ];

        $setting_fields = apply_filters(
            'hkih_theme_settings_graphql_fields',
            $setting_fields
        );

        register_graphql_object_type( 'SiteSettings', [
            'fields' => $setting_fields,
        ] );

        register_graphql_field( 'RootQuery', 'siteSettings', [
            'type'        => 'SiteSettings',
            'description' => __( 'Site Settings', 'hkih' ),
            'args'        => [
                'language' => [
                    'type' => [
                        'non_null' => 'String',
                    ],
                ],
            ],
            'resolve'     => function ( $source, array $args ) use ( $setting_fields ) { // phpcs:ignore
                $lang          = $args['language'] ?? pll_current_language();
                $site_settings = [];

                foreach ( $setting_fields as $key => $setting_field ) {
                    $has_callback = array_key_exists( 'callback', $setting_field ) &&
                                    is_callable( $setting_field['callback'] );

                    if ( $has_callback ) {
                        $site_settings[ $key ] = $setting_field['callback']( $lang );
                    }

                    if ( ! $has_callback && ! empty( $setting_field['field_name'] ?? '' ) ) {
                        $site_settings[ $key ] = \Geniem\Theme\Settings::get_setting(
                            $setting_field['field_name'],
                            $lang
                        );
                    }
                }

                return ! empty( $site_settings ) ? $site_settings : null;
            },
        ] );
    }
}
