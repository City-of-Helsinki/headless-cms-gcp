<?php

namespace Geniem\Theme\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use Geniem\Theme\PostType\Settings;
use \Geniem\Theme\Logger;

/**
 * Class DefaultImagesSettingsTab
 */
class DefaultImagesSettingsTab extends Tab {

    /**
     * Where should the tab switcher be located
     *
     * @var string
     */
    protected $placement = 'left';

    /**
     * Tab strings.
     *
     * @var array
     */
    protected $strings = [
        'tab'    => 'Oletuskuvat',
        'images' => [
            'page'    => [
                'label'        => 'Sisältösivun oletuskuva',
                'instructions' => '',
            ],
            'article' => [
                'label'        => 'Artikkelin oletuskuva',
                'instructions' => '',
            ],
            'hero'    => [
                'label'        => 'Herokomponentin oletuskuva',
                'instructions' => '',
            ],
            'event'   => [
                'label'        => 'Tapahtuman oletuskuva',
                'instructions' => '',
            ],
        ],
    ];

    /**
     * The constructor for tab.
     *
     * @param string $label Label.
     * @param null   $key   Key.
     * @param null   $name  Name.
     */
    public function __construct( $label = '', $key = null, $name = null ) { // phpcs:ignore
        $label = $this->strings['tab'];

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
        $strings = $this->strings;

        try {

            $images = [];
            foreach ( $strings['images'] as $image_key => $image ) {
                $new_image = ( new Field\Image( $image['label'] ) )
                    ->set_key( "{$key}_images_${image_key}" )
                    ->set_name( "{$image_key}_image" )
                    ->set_return_format( 'id' );
                $images[]  = $new_image;
            }

            $this->add_fields( $images );
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Registers GraphQL Fields from our Settings.
     */
    public function register_graphql_fields() : void {
        if ( ! class_exists( 'WPGraphQL' ) ) {
            return;
        }

        $strings = $this->strings;
        $fields  = [];

        foreach ( $strings['images'] as $image_key => $image ) {
            $fields[ $image_key ] = [
                'type'        => 'String',
                'description' => "Attachment URL for ${image_key} image",
                'callback'    => function ( $lang = '' ) use ( $image_key ) {
                    $logo_id = \Geniem\Theme\Settings::get_setting( "{$image_key}_image", $lang ) ?? false;
                    $logo    = wp_get_attachment_image_url( $logo_id, 'full' );

                    return $logo ?? '';
                },
            ];
        }

        $fields = apply_filters(
            'hkih_theme_settings_default_images_graphql_fields',
            $fields
        );

        register_graphql_object_type( 'DefaultImages', [
            'fields' => $fields,
            'description' => __( 'Default images of different post types. Returns url of image of queried post type. Values come from Sivuston Asetukset -> Oletuskuvat.', 'hkih' ),
        ] );

        register_graphql_field( 'RootQuery', 'defaultImages', [
            'type'        => 'DefaultImages',
            'description' => __( 'Default Images', 'hkih' ),
            'args'        => [
                'language' => [
                    'type' => [
                        'non_null' => 'String',
                    ],
                ],
            ],
            'resolve'     => function ( $source, array $args ) use ( $fields ) { // phpcs:ignore
                $lang          = $args['language'] ?? pll_current_language();
                $site_settings = [];

                foreach ( $fields as $key => $setting_field ) {
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
