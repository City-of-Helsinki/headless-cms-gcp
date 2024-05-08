<?php
/**
 * A class for handling getting site settings made with ACF.
 */

namespace Geniem\Theme;

use \Geniem\Theme\PostType;

/**
 * Class Settings
 *
 * @package Geniem\Theme
 */
class Settings {

    /**
     * This determines the slug of the settings CPT.
     */
    const POST_TYPE = PostType\Settings::SLUG;

    /**
     * This is used to cache the language specific settings page IDs.
     *
     * @var array
     */
    protected static $cached_ids = [];

    /**
     * This returns all site settings by using get_fields on the current localised
     * version of the settings page.
     *
     * @param string $lang The language version of the settings. Optional.
     * @param bool   $raw  Use get_post_meta instead of ACF.
     *
     * @return array|boolean The fields as an array or false.
     */
    public static function get_settings( string $lang = '', bool $raw = false ) {

        $settings_page_id = static::get_localised_settings_page_id( $lang );

        $fields = $raw
            ? \get_post_meta( $settings_page_id )
            : \get_fields( $settings_page_id );

        return $fields;
    }

    /**
     * This fetches a single field from current localised settings page.
     *
     * @param string $field The field name that's being fetched.
     * @param string $lang  The language version of the settings. Optional.
     * @param bool   $raw   Use get_post_meta instead of ACF.
     *
     * @return mixed The field in a mixed format or false or an empty string. Depends on ACF's handling of the field.
     */
    public static function get_setting( string $field = '', string $lang = '', bool $raw = false ) {

        $settings_page_id = static::get_localised_settings_page_id( $lang );

        $field = $raw
            ? \get_post_meta( $settings_page_id, $field, true )
            : \get_field( $field, $settings_page_id );

        return $field;
    }

    /**
     * This gets the settings page ID for each language. If the ID has been fetched
     * already, it's returned from the class parameter '$cached_ids'.
     *
     * @param string $lang A language parameter. If empty, the language is fetched automatically.
     *
     * @return int|string The ID of the wanted settings page or an empty string.
     */
    protected static function get_localised_settings_page_id( string $lang = '' ) {

        $lang_slug = empty( $lang )
            ? Localization::get_current_language()
            : $lang;

        // If the settings page's ID is already fetched, just return it.
        if ( ! empty( static::$cached_ids[ $lang_slug ] ) ) {
            return static::$cached_ids[ $lang_slug ];
        }

        $args = [
            'post_type'              => static::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'update_post_term_cache' => false,
            'lang'                   => $lang_slug, // NOTE: this is for RediPress.
        ];

        if ( ! empty( $lang ) ) {
            $args['lang'] = $lang;
        }

        $settings_page_query = new \WP_Query( $args );
        $settings_page_id    = $settings_page_query->posts[0]->ID ?? '';

        // Add found ID to class property for faster performance.
        if ( ! empty( $settings_page_id ) ) {
            static::$cached_ids[ $lang_slug ] = $settings_page_id;
        }

        return $settings_page_id;
    }
}
