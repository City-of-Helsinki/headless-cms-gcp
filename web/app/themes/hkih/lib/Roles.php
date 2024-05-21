<?php
/**
 * WP-Geniem Roles functionality
 */

namespace Geniem\Theme;

use \Geniem\Theme\PostType;

/**
 * Class Roles
 *
 * @package Geniem\Theme
 */
class Roles implements Interfaces\Controller {

    /**
     * Settings Capabilities.
     * Default is to disallow all.
     *
     * @see \Geniem\Theme\PostType\Settings
     * @var array|bool[]
     */
    private array $caps_settings = [
        'edit_setting'              => true,
        'read_setting'              => true,
        'delete_setting'            => true,
        'edit_others_settings'      => true,
        'delete_settings'           => true,
        'publish_settings'          => true,
        'publish_setting'           => true,
        'read_private_settings'     => true,
        'delete_private_settings'   => true,
        'delete_published_settings' => true,
        'delete_others_settings'    => true,
        'edit_private_settings'     => true,
        'edit_published_settings'   => true,
        'edit_settings'             => true,
    ];

    /**
     * Core Posts Capabilities
     *
     * @var array|bool[]
     */
    private array $caps_posts = [
        'read_private_posts'     => true,
        'publish_posts'          => true,
        'edit_posts'             => true,
        'edit_others_posts'      => true,
        'edit_published_posts'   => true,
        'delete_posts'           => true,
        'delete_others_posts'    => true,
        'delete_private_posts'   => true,
        'delete_published_posts' => true,
    ];

    /**
     * Core Pages Capabilities
     *
     * @var array|bool[]
     */
    private array $caps_pages = [
        'read_private_pages'     => true,
        'publish_pages'          => true,
        'edit_pages'             => true,
        'edit_others_pages'      => true,
        'delete_pages'           => true,
        'delete_others_pages'    => true,
        'delete_private_pages'   => true,
        'delete_published_pages' => true,
        'edit_published_pages'   => true,
    ];

    /**
     * WordPress Admin Pages to remove from HKI Headless Admin users.
     *
     * @var array|string[]
     */
    private array $remove_pages_from_admin_roles = [
        'wp_stream_settings',
        'site-health.php',
        'plugins.php',
        'tools.php',
        'themes.php' => [
            'customize.php',
        ],
    ];

    /**
     * WordPress Admin Pages to remove from HKI Headless users NOT IN ADMIN GROUP.
     *
     * @var array|string[]
     */
    private array $remove_pages_from_non_admin_roles = [
        'post-new.php?post_type=' . PostType\Settings::SLUG,
        'edit.php?post_type=' . PostType\Settings::SLUG,
        'wp_stream_settings',
        'site-health.php',
        'plugins.php',
        'tools.php',
        'themes.php' => [
            'themes.php',
            'customize.php',
        ],
    ];

    /**
     * Hooks
     */
    public function hooks() : void {
        if ( class_exists( '\Geniem\Roles' ) ) {
            $this->add_site_admin_role();
            $this->add_site_editor_role();
            $this->add_site_contributor_role();
            $this->add_site_viewer_role();
            $this->modify_administrator_caps();
        }
    }

    /**
     * Add site admin role
     */
    public function add_site_admin_role() : void {
        $admin_rights = array_merge(
            // hkih-cpt-collection / collection-cpt
            self::cpt_cap_for_admin( 'collection' ),
            // hkih-cpt-contact / contact-cpt
            self::cpt_cap_for_admin( 'contact' ),
            // hkih-cpt-landing-page / landing-page-cpt
            self::cpt_cap_for_admin( 'landing_page' ),
            // hkih-cpt-release / release-cpt
            self::cpt_cap_for_admin( 'releases', 'releases' ),
            // hkih-cpt-translation / translation-cpt
            self::cpt_cap_for_admin( 'translation' ),
            $this->caps_posts,
            $this->caps_pages,
            $this->caps_settings,
            [
                'read'              => true,
                'upload_files'      => true,
                'manage_categories' => true,
                'moderate_comments' => true,
                'list_users'        => true,
                'edit_users'        => true,
                'manage_options'    => true,
            ],
        );

        $admin_rights = apply_filters( 'hkih_roles_admin_rights', $admin_rights );

        $headless_admin = \Geniem\Roles::create(
            'headless-cms-admin',
            __( 'Headless CMS Admin', 'hkih' ),
            $admin_rights
        );

        $headless_admin->remove_menu_pages(
            apply_filters(
                'hkih_roles_admin_menus',
                $this->remove_pages_from_admin_roles
            )
        );

        $headless_admin = apply_filters( 'hkih_roles_admin', $headless_admin );

        if ( is_wp_error( $headless_admin ) ) {
            ( new Logger() )->error( $headless_admin->get_error_messages() );
        }

        unset( $headless_admin );
    }

    /**
     * Add site editor role
     */
    public function add_site_editor_role() : void {
        $editor_rights = array_merge(
            [ 'edit_theme_options' => true ],
            self::cpt_cap_for_admin( 'collection' ),
            $this->caps_posts,
            $this->caps_pages,
            [
                'read'         => true,
                'upload_files' => true,
                'read_setting' => false,
            ],
        );

        $editor_rights = apply_filters( 'hkih_roles_editor_rights', $editor_rights );

        $headless_editor = \Geniem\Roles::create(
            'headless-cms-editor',
            __( 'Headless CMS Editor', 'hkih' ),
            $editor_rights
        );

        // edit.php includes all in the left side sidebar,
        // post-new.php are the ones in the top bar
        $headless_editor->remove_menu_pages(
            apply_filters(
                'hkih_roles_editor_menus',
                $this->remove_pages_from_non_admin_roles
            )
        );

        $headless_editor = apply_filters( 'hkih_roles_editor', $headless_editor );

        if ( is_wp_error( $headless_editor ) ) {
            ( new Logger() )->error( $headless_editor->get_error_messages() );
        }

        unset( $headless_editor );
    }

    /**
     * Add site contributor role
     */
    public function add_site_contributor_role() : void {
        $page_caps = [
            'publish_page'      => true,
            'edit_page'         => true,
            'edit_pages'        => true,
            'edit_others_pages' => true,
        ];

        $collection_caps = [
            'publish_collection'      => true,
            'edit_collection'         => true,
            'edit_collections'        => true,
            'edit_others_collections' => true,
        ];

        $contributor_rights = array_merge(
            $page_caps,
            $collection_caps,
            $this->caps_posts,
            [
                'read'                 => true,
                'upload_files'         => true,
                'publish_pages'        => false,
                'edit_pages'           => true,
                'read_setting'         => false,
                'list_published_pages' => true,
            ],
        );

        $contributor_rights = apply_filters( 'hkih_roles_contributor_rights', $contributor_rights );

        $headless_contributor = \Geniem\Roles::create(
            'headless-cms-contributor',
            __( 'Headless CMS Contributor', 'hkih' ),
            $contributor_rights
        );

        // edit.php includes all in the left side sidebar,
        // post-new.php are the ones in the top bar
        $headless_contributor->remove_menu_pages(
            apply_filters(
                'hkih_roles_contributor_menus',
                $this->remove_pages_from_non_admin_roles
            )
        );

        $headless_contributor = apply_filters( 'hkih_roles_contributor', $headless_contributor );

        if ( is_wp_error( $headless_contributor ) ) {
            ( new Logger() )->error( $headless_contributor->get_error_messages() );
        }

        unset( $headless_contributor );
    }

    /**
     * Add site viewer role
     */
    public function add_site_viewer_role() : void {
        $viewer_rights = [
            'read'                => true,
            'read_private_pages'  => true,
            'read_private_posts'  => true,
            'read_setting'        => false,
            'read_collections'    => true,
            'publish_page'        => true,
            'publish_collection'  => true,
            'publish_pages'       => true,
            'publish_collections' => true,
        ];

        $viewer_rights = apply_filters( 'hkih_roles_viewer_rights', $viewer_rights );

        $headless_viewer = \Geniem\Roles::create(
            'headless-cms-viewer',
            __( 'Headless CMS Viewer', 'hkih' ),
            $viewer_rights
        );

        // edit.php includes all in the left side sidebar,
        // post-new.php are the ones in the top bar
        $headless_viewer->remove_menu_pages(
            apply_filters(
                'hkih_roles_viewer_menus',
                $this->remove_pages_from_non_admin_roles
            )
        );

        $headless_viewer = apply_filters( 'hkih_roles_viewer', $headless_viewer );

        if ( is_wp_error( $headless_viewer ) ) {
            ( new Logger() )->error( $headless_viewer->get_error_messages() );
        }

        unset( $headless_viewer );
    }

    /**
     * Modify 'administrator' capabilities
     */
    public function modify_administrator_caps() : void {
        $admin_rights = array_merge(
            // hkih-cpt-collection / collection-cpt
            self::cpt_cap_for_admin( 'collection' ),
            // hkih-cpt-contact / contact-cpt
            self::cpt_cap_for_admin( 'contact' ),
            // hkih-cpt-landing-page / landing-page-cpt
            self::cpt_cap_for_admin( 'landing_page' ),
            // hkih-cpt-release / release-cpt
            self::cpt_cap_for_admin( 'releases', 'releases' ),
            // hkih-cpt-translation / translation-cpt
            self::cpt_cap_for_admin( 'translation' ),
            $this->caps_settings,
        );

        $admin_rights = array_map( function( $item, $key ) {
            return $key;
        }, $admin_rights, array_keys( $admin_rights ) );

        $admin_rights = apply_filters( 'hkih_roles_administrator_rights', $admin_rights );

        $admin = \Geniem\Roles::get( 'administrator' );

        $admin->add_caps( $admin_rights );

        if ( is_wp_error( $admin ) ) {
            ( new Logger() )->error( $admin->get_error_messages() );
        }

        unset( $admin );
    }

    /**
     * Generate CPT Rules array with few defaults.
     *
     * @param string $cap_singular Capability type in singular.
     * @param string $cap_plural   Capability type in plural, if empty adds 's' to singular.
     *
     * @return bool[]
     */
    private static function cpt_cap(
        string $cap_singular = '',
        string $cap_plural = ''
    ) : array {
        if ( empty( $cap_plural ) ) {
            $cap_plural = $cap_singular . 's';
        }

        $s1 = $cap_singular;
        $s2 = $cap_plural;

        return [
            "delete_others_{$s2}"    => false,
            "delete_private_{$s2}"   => false,
            "delete_published_{$s2}" => false,
            "delete_{$s1}"           => false,
            "delete_{$s2}"           => false,
            "edit_others_{$s2}"      => false,
            "edit_private_{$s2}"     => false,
            "edit_published_{$s2}"   => false,
            "edit_{$s1}"             => false,
            "edit_{$s2}"             => false,
            "publish_{$s1}"          => false,
            "publish_{$s2}"          => false,
            "read_private_{$s2}"     => true,
            "read_{$s1}"             => true,
        ];
    }

    /**
     * Return CPT Capability with all permissions given.
     *
     * @param string $singular CPT Capability singular name.
     * @param string $plural   CPT Capability plural name.
     *
     * @return array
     */
    private static function cpt_cap_for_admin( string $singular = '', string $plural = '' ) : array {
        $rights = self::cpt_cap( $singular, $plural );

        return array_fill_keys( array_keys( $rights ), true );
    }
}
