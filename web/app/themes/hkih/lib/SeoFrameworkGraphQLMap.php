<?php
/**
 * The SEO Framework (autodescription) plugin GraphQL Mappings
 */

namespace Geniem\Theme;

/**
 * Class SeoFrameworkGraphQLMap
 *
 * @package Geniem\Theme
 */
class SeoFrameworkGraphQLMap implements Interfaces\Controller {
    /**
     * Dependencies check.
     *
     * @var bool
     */
    private bool $dependencies_missing = false;
    /**
     * The SEO Framework Loader.
     *
     * @var \The_SEO_Framework\Load $tsf
     */
    private $tsf;

    /**
     * SeoFrameworkGraphQLMap constructor.
     */
    public function __construct() {
        add_action( 'admin_init', function () {
            $core_dependencies = [
                'WPGraphQL plugin'  => class_exists( 'WPGraphQL' ),
                'The SEO Framework' => function_exists( 'the_seo_framework' ),
            ];

            $missing_dependencies = array_keys(
                array_diff( $core_dependencies, array_filter( $core_dependencies ) )
            );

            $display_admin_notice = static function () use ( $missing_dependencies ) {
                echo '<div class="notice notice-error"><p>';
                esc_html_e(
                    "The WPGraphQL SEO Framework plugin can't be loaded. These dependencies are missing:",
                    'wp-graphql-seo-framework'
                );
                echo '</p><ul>';
                foreach ( $missing_dependencies as $missing_dependency ) {
                    echo '<li>' . esc_html( $missing_dependency ) . '</li>';
                }
                echo '</ul></div>';
            };

            if ( ! empty( $missing_dependencies ) ) {
                $this->dependencies_missing = true;
                add_action( 'network_admin_notices', $display_admin_notice );
                add_action( 'admin_notices', $display_admin_notice );
            }
        } );
    }

    /**
     * Runs Hooks on activation.
     */
    public function hooks() : void {
        if ( $this->dependencies_missing ) {
            return;
        }

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'add_the_seo_framework_fields' ] )
        );

        // Fixes decoding when default separator is in use.
        \add_action(
            'the_seo_framework_title_separator', function( $separator ) {
                return html_entity_decode( $separator );
            }
        );
    }

    /**
     * Register SEO Framework GraphQL Fields
     */
    public function add_the_seo_framework_fields() : void {
        /**
         * The SEO Framework Loader.
         *
         * @var \The_SEO_Framework\Load $tsf
         */
        $this->tsf = the_seo_framework() ?? false;

        if ( $this->tsf === null || ! class_exists( 'WPGraphQL' ) ) {
            return;
        }

        $post_types     = \WPGraphQL::get_allowed_post_types();
        $meta_fields    = $this->create_meta_fields( $this->tsf );
        $setting_fields = $this->create_settings_fields( $this->tsf );

        register_graphql_object_type( 'SEO', [
            'fields' => $meta_fields,
        ] );

        register_graphql_object_type( 'SeoSettings', [
            'fields' => $setting_fields,
        ] );

        register_graphql_field( 'RootQuery', 'seoSettings', [
            'type'        => 'SeoSettings',
            'description' => __( 'The SEO Framework settings', 'wp-graphql' ),
            'resolve'     => function ( $root, $args, $context, $info ) use ( $setting_fields ) { // phpcs:ignore
                $seoSettings = [];

                foreach ( $setting_fields as $key => $setting_field ) {
                    $seoSettings[ $key ] = $setting_field['seo_cb'];
                }

                return ! empty( $seoSettings ) ? $seoSettings : null;
            },
        ] );

        if ( ! empty( $post_types ) ) {
            $this->register_graphql_fields_posttype( $post_types, $meta_fields );
        }
    }

    /**
     * Create Settings (like title separator for SEO Titles) array.
     *
     * @param \The_SEO_Framework\Load $tsf The SEO Framework instance.
     *
     * @return array[]
     */
    private function create_settings_fields( \The_SEO_Framework\Load $tsf ) : array {
        return [
            'separator' => [
                'type'        => 'String',
                'description' => 'Title separator setting for seo titles',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    return $tsf->get_separator();
                },
            ],
        ];
    }

    /**
     * Create Post Meta Fields array.
     *
     * @param \The_SEO_Framework\Load $tsf The SEO Framework instance.
     *
     * @return array
     */
    private function create_meta_fields( \The_SEO_Framework\Load $tsf ) : array {
        return [
            'title'                => [
                'meta_key'    => '_genesis_title',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $title = $this->get_overwrite_title( $post_id, '_genesis_title', $tsf );

                    return $title ?? $tsf->get_title( $post_id );;
                },
                'type'        => 'String',
                'description' => 'SEO Title',
            ],
            'description'          => [
                'meta_key'    => '_genesis_description',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $description = $this->get_overwrite_description( $post_id, '_genesis_description' );

                    return $description ?? $tsf->get_description( $post_id );
                },
                'type'        => 'String',
                'description' => 'SEO Description',
            ],
            'canonicalUrl'         => [
                'meta_key'    => '_genesis_canonical_uri',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    return $tsf->create_canonical_url( [
                        'id'               => $post_id,
                        'get_custom_field' => true,
                    ] );
                },
                'type'        => 'String',
                'description' => 'Canonical URL',
            ],
            'socialImage'          => [
                'type'   => 'MediaItem',
                'seo_cb' => function ( $post_id, $context ) use ( $tsf ) {
                    // get_image_details returns an array,
                    // but we only need the most recent selected image
                    $images = $tsf->get_image_details( $post_id, true );
                    if ( empty( $images ) ) {
                        return null;
                    }

                    $id = (int) $tsf->get_image_details( $post_id, true )[0]['id'];

                    return $context->get_loader( 'post' )->load_deferred( $id );
                },
            ],
            'openGraphTitle'       => [
                'type'        => 'String',
                'description' => 'Open Graph title',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $title = $this->get_overwrite_title( $post_id, '_open_graph_title', $tsf );
                    return $title ?? $tsf->get_open_graph_title( $post_id );
                },
            ],
            'openGraphDescription' => [
                'type'        => 'String',
                'description' => 'Open Graph description',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $description = $this->get_overwrite_description( $post_id, '_open_graph_description' );
                    return $description ?? $tsf->get_open_graph_description( $post_id );
                },
            ],
            'openGraphType'        => [
                'type'        => 'String',
                'description' => "Open Graph type ('website', 'article', ...)",
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    return $tsf->get_og_type();
                },
            ],
            'twitterTitle'         => [
                'type'        => 'String',
                'description' => 'Twitter title',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $title = $this->get_overwrite_title( $post_id, '_twitter_title', $tsf );
                    return $title ?? $tsf->get_twitter_title( $post_id );
                },
            ],
            'twitterDescription'   => [
                'type'        => 'String',
                'description' => 'Twitter description',
                'seo_cb'      => function ( $post_id, $context ) use ( $tsf ) { // phpcs:ignore
                    $description = $this->get_overwrite_description( $post_id, '_twitter_description' );
                    return $description ?? $tsf->get_twitter_description( $post_id );
                },
            ],
            'removeSiteTitle'      => [
                'meta_key'    => '_tsf_title_no_blogname',
                'type'        => 'Boolean',
                'description' => 'If true, site title is/should not be added to the end of the SEO title',
            ],
            'redirectUrl'          => [
                'meta_key'    => 'redirect',
                'type'        => 'String',
                'description' => '301 redirect URL to force visitors to another page',
            ],
            'noIndex'              => [
                'meta_key'    => '_genesis_noindex',
                'type'        => 'Boolean',
                'description' => 'Whether search engines should index this page',
            ],
            'noFollow'             => [
                'meta_key'    => '_genesis_nofollow',
                'type'        => 'Boolean',
                'description' => 'Whether search engines should follow the links of this page',
            ],
            'noArchive'            => [
                'meta_key'    => '_genesis_noarchive',
                'type'        => 'Boolean',
                'description' => 'Whether search engines should show cached links of this page',
            ],
            'excludeLocalSearch'   => [
                'meta_key'    => 'exclude_local_search',
                'type'        => 'Boolean',
                'description' => 'Whether this page should be excluded from all search queries',
            ],
            'excludeFromArchive'   => [
                'meta_key'    => 'exclude_from_archive',
                'type'        => 'Boolean',
                'description' => 'Whether this page should be excluded from all archive queries',
            ],
        ];
    }

    /**
     * Registers GraphQL Fields to All Allowed PostTypes.
     *
     * @param array $post_types  All allowed post types.
     * @param array $meta_fields Shared meta fields.
     */
    private function register_graphql_fields_posttype( array $post_types, array $meta_fields ) : void {
        foreach ( $post_types as $post_type ) {
            $single_name = \Geniem\Theme\Utils::get_post_type_graphql_single_name( $post_type );

            if ( empty( $single_name ) ) {
                continue;
            }

            register_graphql_field( $single_name, 'seo', [
                'type'        => 'SEO',
                'description' => __( 'The SEO Framework data of the ' . $single_name, 'wp-graphql' ), // phpcs:ignore
                'resolve'     => function ( $root, $args, $context, $info ) use ( $meta_fields ) { // phpcs:ignore
                    $post_id = $root->ID;
                    $seo     = [];

                    // If no callback has been defined, use get_field and definition array meta_key.
                    foreach ( $meta_fields as $key => $meta_field ) {
                        $seo[ $key ] = ! empty( $meta_field['seo_cb'] )
                            ? $meta_field['seo_cb']( $post_id, $context )
                            : get_field( $meta_field['meta_key'], $post_id );
                    }

                    return ! empty( $seo ) ? $seo : null;
                },
            ] );
        }
    }

    /**
     * Check overwrite title
     *
     * @param string $meta_field Field to check.
     *
     * @return string
     */
    private function get_overwrite_title( $post_id, $meta_field, $tsf ) : string {
        $title     = \get_post_meta( $post_id, $meta_field )[0] ?? '';
        $site_name = \get_bloginfo( 'name' );

        // Fallback.
        if ( empty( $title ) ) {
            $title = \get_post_meta( $post_id, '_genesis_title' )[0] ?? '';
        }

        return sprintf('%s %s %s', $title, $tsf->get_separator(), $site_name);
    }

    /**
     * Check overwrite description
     *
     * @param string $meta_field Field to check.
     *
     * @return string
     */
    private function get_overwrite_description( $post_id, $meta_field ) : string {
        $description = \get_post_meta( $post_id, $meta_field )[0] ?? '';

        // Fallback.
        if ( empty( $description ) ) {
            $description = \get_post_meta( $post_id, '_genesis_description' )[0] ?? '';
        }

        return $description;
    }
}
