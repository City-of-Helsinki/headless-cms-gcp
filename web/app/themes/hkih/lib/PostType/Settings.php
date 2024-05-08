<?php
/**
 * The settings post type is used to create translatable site settings.
 *
 * There's only one post created in this post type and it can be removed only
 * by super admin. Others can modify, so the settings are updated.
 */

namespace Geniem\Theme\PostType;

use \Geniem\Theme\Interfaces\PostType;

/**
 * This class defines the post type.
 *
 * @package Geniem\Theme\PostType
 */
class Settings implements PostType {
    /**
     * This defines the slug of this post type.
     */
    public const SLUG = 'settings-cpt';

    /**
     * This defines what is shown in the url. This can
     * be different than the slug which is used to register the post type.
     *
     * @var string
     */
    private $url_slug = '';

    /**
     * Define the CPT description
     *
     * @var string
     */
    private $description = '';

    /**
     * This is used to position the post type menu in admin.
     *
     * @var int
     */
    private $menu_order = 50;

    /**
     * This defines the CPT icon.
     *
     * @var string
     */
    private $icon = 'dashicons-admin-home';

    /**
     * Constructor
     */
    public function __construct() {
        // Make url slug translatable
        $this->url_slug = _x( 'settings', 'theme CPT slugs', 'hkih' );

        // Make possible description text translatable.
        $this->description = _x( 'CPT Description', 'theme CPT', 'hkih' );
    }

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action( 'init', \Closure::fromCallable( [ $this, 'register' ] ), 15 );
    }

    /**
     * Get post type
     *
     * @return string
     */
    public static function get_post_type() : string {
        return static::SLUG;
    }

    /**
     * This registers the post type.
     *
     * @return void
     */
    private function register() {
        $labels = [
            'name'                  => 'Sivuston asetukset',
            'singular_name'         => 'Sivuston asetukset',
            'menu_name'             => 'Sivuston asetukset',
            'name_admin_bar'        => 'Sivuston asetukset',
            'archives'              => 'Arkistot',
            'attributes'            => 'Ominaisuudet',
            'parent_item_colon'     => 'Vanhempi:',
            'all_items'             => 'Kaikki',
            'add_new_item'          => 'Lisää uusi',
            'add_new'               => 'Lisää uusi',
            'new_item'              => 'Uusi',
            'edit_item'             => 'Muokkaa',
            'update_item'           => 'Päivitä',
            'view_item'             => 'Näytä',
            'view_items'            => 'Näytä kaikki',
            'search_items'          => 'Etsi',
            'not_found'             => 'Ei löytynyt',
            'not_found_in_trash'    => 'Ei löytynyt roskakorista',
            'featured_image'        => 'Kuva',
            'set_featured_image'    => 'Aseta kuva',
            'remove_featured_image' => 'Poista kuva',
            'use_featured_image'    => 'Käytä kuvana',
            'insert_into_item'      => 'Aseta julkaisuun',
            'uploaded_to_this_item' => 'Lisätty tähän julkaisuun',
            'items_list'            => 'Listaus',
            'items_list_navigation' => 'Listauksen navigaatio',
            'filter_items_list'     => 'Suodata listaa',
        ];

        $rewrite = [
            'slug'       => static::SLUG,
            'with_front' => false,
            'pages'      => false,
            'feeds'      => false,
        ];

        $args = [
            'label'               => $labels['name'],
            'description'         => '',
            'labels'              => $labels,
            'supports'            => [ 'title', 'custom-fields', 'revisions' ],
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => $this->menu_order,
            'menu_icon'           => $this->icon,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => false,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'rewrite'             => $rewrite,
            'capability_type'     => [ 'settings', 'settings' ],
            'map_meta_caps'       => true,
            'show_in_rest'        => true,
        ];

        register_post_type( static::SLUG, $args );
    }
}
