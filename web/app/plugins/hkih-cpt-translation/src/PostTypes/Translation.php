<?php
/**
 * Post type definition for Translation
 */

namespace HKIH\CPT\Translation\PostTypes;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\Field;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Post;
use function __;
use function _x;
use function register_post_type;

/**
 * Class Translation
 *
 * @package HKIH\CPT\Translation\PostTypes
 */
class Translation {

    /**
     * Post type slug
     *
     * @var string
     */
    protected static $slug = 'translation-cpt';

    /**
     * Graphql single name
     *
     * @var string
     */
    protected static $graphql_single_name = 'translation';

    /**
     * Get the post type slug.
     *
     * @return string
     */
    public static function get_post_type() : string {
        return static::$slug;
    }

    /**
     * Get the post type graphql slug.
     *
     * @return string
     */
    public static function get_graphql_single_name() : string {
        return static::$graphql_single_name;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action(
            'init',
            Closure::fromCallable( [ $this, 'register' ] ),
            100,
            0
        );

        add_action(
            'acf/init',
            Closure::fromCallable( [ $this, 'fields' ] ),
            50,
            0
        );

        add_filter(
            'use_block_editor_for_post_type',
            Closure::fromCallable( [ $this, 'disable_gutenberg' ] ),
            10,
            2
        );

        add_action(
            'graphql_register_types',
            Closure::fromCallable( [ $this, 'register_graphql_types' ] )
        );

        add_action(
            'rest_api_init',
            Closure::fromCallable( [ $this, 'register_rest_fields' ] )
        );

        static::$slug = apply_filters( 'hkih_posttype_translation_slug', static::$slug );
    }

    /**
     * Register the post type
     *
     * @return void
     */
    protected function register() : void {
        $labels = [
            'name'                  => _x( 'Translations', 'Post Type General Name', 'hkih-cpt-translations' ),
            'singular_name'         => _x( 'Translation', 'Post Type Singular Name', 'hkih-cpt-translations' ),
            'menu_name'             => __( 'Translations', 'hkih-cpt-translations' ),
            'name_admin_bar'        => __( 'Translation', 'hkih-cpt-translations' ),
            'archives'              => __( 'Translations', 'hkih-cpt-translations' ),
            'parent_item_colon'     => __( 'Translations', 'hkih-cpt-translations' ),
            'all_items'             => __( 'All Translations', 'hkih-cpt-translations' ),
            'add_new_item'          => __( 'Add new Translation', 'hkih-cpt-translations' ),
            'add_new'               => __( 'Add new Translation', 'hkih-cpt-translations' ),
            'new_item'              => __( 'New Translation', 'hkih-cpt-translations' ),
            'edit_item'             => __( 'Edit', 'hkih-cpt-translations' ),
            'update_item'           => __( 'Update', 'hkih-cpt-translations' ),
            'view_item'             => __( 'View Translation', 'hkih-cpt-translations' ),
            'search_items'          => __( 'Search Translations', 'hkih-cpt-translations' ),
            'not_found'             => __( 'Not found', 'hkih-cpt-translations' ),
            'not_found_in_trash'    => __( 'No Translations in trash.', 'hkih-cpt-translations' ),
            'insert_into_item'      => __( 'Insert into Translation', 'hkih-cpt-translations' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Translation', 'hkih-cpt-translations' ),
            'items_list'            => __( 'Translation', 'hkih-cpt-translations' ),
            'items_list_navigation' => __( 'Translation', 'hkih-cpt-translations' ),
            'filter_items_list'     => __( 'Translation', 'hkih-cpt-translations' ),
        ];

        $labels = apply_filters( 'hkih_posttype_translation_labels', $labels );

        $args = [
            'label'               => __( 'Translations', 'hkih-cpt-translations' ),
            'description'         => __( 'Translations', 'hkih-cpt-translations' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'revisions' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-translation',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'map_meta_cap'        => true,
            'capability_type'     => 'translation',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'graphql_single_name' => static::get_graphql_single_name(),
            'graphql_plural_name' => 'translations',
            'query_var'           => true,
            'taxonomies'          => [],
        ];

        $args = apply_filters( 'hkih_posttype_translation_args', $args );

        register_post_type( static::$slug, $args );
    }

    /**
     * Register the fields for the post type
     *
     * @return void
     */
    protected function fields() : void {
        try {
            $this->register_translation_fields();
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Register translation fields
     *
     * @throws Exception ACF-Codifier exception.
     */
    protected function register_translation_fields() : void {
        $key = 'hkih-cpt-translations-fields';

        $s = [
            'group'       => [
                'title' => __( 'Translations', 'hkih-cpt-translations' ),
            ],
            'repeater'    => [
                'title' => __( 'Translation group', 'hkih-cpt-translations' ),
            ],
            'group_title' => [
                'title' => __( 'Group title', 'hkih-cpt-translations' ),
                'help'  => __( 'Group title is for documentation purposes', 'hkih-cpt-translations' ),
            ],
            't_key'       => [
                'title' => __( 'Translation key', 'hkih-cpt-translations' ),
                'help'  => __( 'Key for the translations. Please, use only lowercase and no spaces',
                    'hkih-cpt-translations' ),
            ],
            't_val'       => [
                'title' => __( '%s', 'hkih-cpt-translations' ),
                'help'  => __( 'Translation for %s', 'hkih-cpt-translations' ),
            ],
        ];

        $translations_group = new Group( $s['group']['title'], $key );
        $translations_group->add_rule_group( $this->get_rule_group() );

        $repeater = ( new Field\Repeater( $s['repeater']['title'] ) )
            ->set_key( "{$key}_repeater" )
            ->set_layout( 'block' )
            ->set_name( 'translations' )
            ->hide_label();

        $group_title = ( new Field\Text( $s['group_title']['title'] ) )
            ->set_key( "{$key}_group_title" )
            ->set_name( 'group_title' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $s['group_title']['help'] );

        $repeater->add_field( $group_title );

        $translation_key = ( new Field\Text( $s['t_key']['title'] ) )
            ->set_key( "{$key}_translation_key" )
            ->set_name( 'translation_key' )
            ->set_instructions( $s['t_key']['help'] )
            ->set_placeholder( 'translation.key.for.ui' )
            ->set_required()
            ->set_wrapper_width( 50 )
            ->load_value( fn( $value ) => self::fix_translation_key( $value ) )
            ->format_value( fn( $value ) => self::fix_translation_key( $value ) )
            ->update_value( fn( $value ) => self::fix_translation_key( $value ) );

        $repeater->add_field( $translation_key );

        // If we have 2-3 languages, translations can be side by side.
        $languages          = function_exists( 'pll_languages_list' ) ? pll_languages_list() : [];
        $lang_wrapper_width = count( $languages ) >= 2 && count( $languages ) < 4
            ? absint( 100 / count( $languages ) )
            : 100;

        if ( ! empty( $languages ) ) {
            foreach ( $languages as $lang ) {
                $translation = ( new Field\Textarea( sprintf( $s['t_val']['title'], $lang ) ) )
                    ->set_key( "{$key}_translation_{$lang}" )
                    ->set_name( "translation_{$lang}" )
                    ->set_wrapper_width( $lang_wrapper_width )
                    ->set_new_lines( 'wpautop' )
                    ->set_rows( 2 )
                    ->set_instructions( sprintf( $s['t_val']['help'], $lang ) );

                $repeater->add_field( $translation );
            }
        }

        $translations_group->add_field( $repeater );

        $translations_group = apply_filters(
            'hkih_posttype_translation_fields',
            $translations_group
        );

        $translations_group->register();
    }

    /**
     * Fix Translation key to lowercase without spaces.
     *
     * @param string $value Translation key.
     *
     * @return string
     */
    private static function fix_translation_key( $value = '' ) : string {
        $value = str_replace( ' ', '.', $value );

        return strtolower( trim( $value ) );
    }

    /**
     * Get rule group for post type
     *
     * @return RuleGroup
     * @throws Exception ACF-Codifier exception.
     */
    protected function get_rule_group() : RuleGroup {
        return ( new RuleGroup() )
            ->add_rule( 'post_type', '==', static::$slug );
    }

    /**
     * Disable Gutenberg for this post type
     *
     * @param boolean $current_status The current Gutenberg status.
     * @param string  $post_type      The post type.
     *
     * @return boolean
     */
    protected function disable_gutenberg( bool $current_status, string $post_type ) : bool {
        return $post_type === static::$slug ? false : $current_status;
    }

    /**
     * Register fields for graphql
     */
    protected function register_graphql_types() : void {
        $languages                = pll_languages_list();
        $translation_items_fields = [];

        if ( ! empty( $languages ) ) {
            foreach ( $languages as $lang ) {
                $translation_items_fields[ $lang ] = [
                    'type'        => 'String',
                    'description' => __( 'Translation string', 'hkih-cpt-translations' ),
                ];
            }
        }

        register_graphql_object_type( 'TranslationItems', [
            'description' => __( 'Translation with language/value pairs', 'hkih-cpt-translations' ),
            'fields'      => $translation_items_fields,
        ] );

        register_graphql_object_type( 'TranslationResponse', [
            'description' => __( 'Translation response contains translation key and translations',
                'hkih-cpt-translations' ),
            'fields'      => [
                'key'          => [
                    'type'        => 'String',
                    'description' => __( 'Translation key for frontend', 'hkih-cpt-translations' ),
                ],
                'translations' => [
                    'type'        => 'TranslationItems',
                    'description' => __( 'Translations for frontend', 'hkih-cpt-translations' ),
                ],
            ],
        ] );

        register_graphql_field( static::get_graphql_single_name(), 'translations', [
            'type'        => [ 'list_of' => 'TranslationResponse' ],
            'description' => __( 'Translations', 'hkih-cpt-translations' ),
            'resolve'     => fn( Post $post ) => static::get_translations( $post->ID ),
        ] );
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() : void {
        register_rest_field(
            [ static::get_post_type() ],
            'translations',
            [
                'get_callback' => fn( $object ) => static::get_translations( $object['id'] ),
            ]
        );
    }

    /**
     * Translations REST callback
     *
     * @param int $post_id WP_Post ID.
     *
     * @return array
     */
    public function get_translations( int $post_id ) : array {
        $translations = get_field( 'translations', $post_id ) ?? [];

        if ( empty( $translations ) || ! is_array( $translations ) ) {
            return $translations;
        }

        $languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : [];
        $response  = [];

        foreach ( $translations as $row ) {
            $translation = new \stdClass();

            $translation->key          = wp_filter_kses( $row['translation_key'] );
            $translation->translations = [];

            if ( ! empty( $languages ) ) {
                foreach ( $languages as $lang ) {
                    $translation->translations[ $lang ] = wp_filter_kses(
                        trim( $row[ 'translation_' . $lang ] )
                    );
                }
            }

            $response[ $translation->key ] = $translation;
        }

        return $response;
    }
}
