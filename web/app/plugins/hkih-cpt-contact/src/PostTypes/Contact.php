<?php
/**
 * Post type definition for Contact
 */

namespace HKIH\CPT\Contact\PostTypes;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\Field;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;
use function __;
use function _x;
use function register_graphql_field;
use function register_post_type;

/**
 * Class Contact
 *
 * @package HKIH\CPT\Contact\PostTypes
 */
class Contact {

    /**
     * Post type slug
     *
     * @var string
     */
    protected static $slug = 'contact-cpt';

    /**
     * Graphql single name
     *
     * @var string
     */
    protected static $graphql_single_name = 'contact';

    /**
     * Graphql plural name
     *
     * @var string
     */
    protected static $graphql_plural_name = 'contacts';

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
            0,
            0
        );

        add_action(
            'acf/init',
            Closure::fromCallable( [ $this, 'fields' ] ),
            10,
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

        static::$slug = apply_filters( 'hkih_posttype_contact_slug', static::$slug );
    }

    /**
     * Register the post type
     *
     * @return void
     */
    protected function register() : void {
        $labels = [
            'name'                  => _x( 'Contacts', 'Post Type General Name', 'hkih-cpt-contact' ),
            'singular_name'         => _x( 'Contact', 'Post Type Singular Name', 'hkih-cpt-contact' ),
            'menu_name'             => __( 'Contacts', 'hkih-cpt-contact' ),
            'name_admin_bar'        => __( 'Contact', 'hkih-cpt-contact' ),
            'archives'              => __( 'Contacts', 'hkih-cpt-contact' ),
            'parent_item_colon'     => __( 'Contacts', 'hkih-cpt-contact' ),
            'all_items'             => __( 'All contacts', 'hkih-cpt-contact' ),
            'add_new_item'          => __( 'Add new contact', 'hkih-cpt-contact' ),
            'add_new'               => __( 'Add new contact', 'hkih-cpt-contact' ),
            'new_item'              => __( 'New contact', 'hkih-cpt-contact' ),
            'edit_item'             => __( 'Edit', 'hkih-cpt-contact' ),
            'update_item'           => __( 'Update', 'hkih-cpt-contact' ),
            'view_item'             => __( 'View contact', 'hkih-cpt-contact' ),
            'search_items'          => __( 'Search contacts', 'hkih-cpt-contact' ),
            'not_found'             => __( 'Not found', 'hkih-cpt-contact' ),
            'not_found_in_trash'    => __( 'No contacts in trash.', 'hkih-cpt-contact' ),
            'insert_into_item'      => __( 'Insert into contact', 'hkih-cpt-contact' ),
            'uploaded_to_this_item' => __( 'Uploaded to this contact', 'hkih-cpt-contact' ),
            'items_list'            => __( 'contact', 'hkih-cpt-contact' ),
            'items_list_navigation' => __( 'contact', 'hkih-cpt-contact' ),
            'filter_items_list'     => __( 'contact', 'hkih-cpt-contact' ),
        ];

        $labels = apply_filters( 'hkih_posttype_contact_labels', $labels );

        $args = [
            'label'               => __( 'Contacts', 'hkih-cpt-contact' ),
            'description'         => __( 'Contacts', 'hkih-cpt-contact' ),
            'labels'              => $labels,
            'supports'            => [ 'title', 'thumbnail', 'revisions' ],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-groups',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'map_meta_cap'        => true,
            'capability_type'     => 'contact',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'graphql_single_name' => static::$graphql_single_name,
            'graphql_plural_name' => static::$graphql_plural_name,
            'query_var'           => true,
            'taxonomies'          => [],
        ];

        $args = apply_filters( 'hkih_posttype_contact_args', $args );

        register_post_type( static::get_post_type(), $args );
    }

    /**
     * Register the fields for the post type
     *
     * @return void
     */
    protected function fields() : void {
        try {
            $key = 'hkih-cpt-contact';

            $field_group = new Group(
                __( 'Contact', 'hkih-cpt-contact' ),
                $key
            );

            $field_group->add_rule_group( $this->get_rule_group() );

            $first_name = ( new Field\Text( __( 'First name', 'hkih-cpt-contact' ) ) )
                ->set_key( "${key}_first_name" )
                ->set_name( 'first_name' )
                ->set_wrapper_width( 50 );

            $last_name = ( new Field\Text( __( 'Last name', 'hkih-cpt-contact' ) ) )
                ->set_key( "${key}_last_name" )
                ->set_name( 'last_name' )
                ->set_wrapper_width( 50 );

            $title = ( new Field\Text( __( 'Job title', 'hkih-cpt-contact' ) ) )
                ->set_key( "${key}_job_title" )
                ->set_name( 'job_title' );

            $description = ( new Field\Textarea( __( 'Description', 'hkih-cpt-contact' ) ) )
                ->set_key( "${key}_description" )
                ->set_name( 'description' )
                ->set_new_lines( 'wpautop' )
                ->set_rows( 4 );

            $field_group->add_fields( [
                $first_name,
                $last_name,
                $title,
                $description,
            ] );

            $field_group = apply_filters(
                'hkih_posttype_contact_fields',
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Get rule group for post type
     *
     * @return RuleGroup
     * @throws Exception ACF-Codifier exception.
     */
    protected function get_rule_group() : RuleGroup {
        return ( new RuleGroup() )
            ->add_rule( 'post_type', '==', static::get_post_type() );
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
        return $post_type === static::get_post_type() ? false : $current_status;
    }

    /**
     * Register fields for graphql
     */
    protected function register_graphql_types() : void {
        register_graphql_field(
            static::get_graphql_single_name(),
            'firstName',
            [
                'type'        => 'String',
                'description' => __( 'First name', 'hkih-cpt-contact' ),
                'resolve'     => fn( $post ) => static::get_first_name( $post->ID ),
            ]
        );

        register_graphql_field(
            static::get_graphql_single_name(),
            'lastName',
            [
                'type'        => 'String',
                'description' => __( 'Last name', 'hkih-cpt-contact' ),
                'resolve'     => fn( $post ) => static::get_last_name( $post->ID ),
            ]
        );

        register_graphql_field(
            static::get_graphql_single_name(),
            'jobTitle',
            [
                'type'        => 'String',
                'description' => __( 'Job Title', 'hkih-cpt-contact' ),
                'resolve'     => fn( $post ) => static::get_job_title( $post->ID ),
            ]
        );

        register_graphql_field(
            static::get_graphql_single_name(),
            'description',
            [
                'type'        => 'String',
                'description' => __( 'Description', 'hkih-cpt-contact' ),
                'resolve'     => fn( $post ) => static::get_description( $post->ID ),
            ]
        );
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() {
        register_rest_field(
            [ static::get_post_type() ],
            'first_name',
            [
                'get_callback' => fn( $object ) => static::get_first_name( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'last_name',
            [
                'get_callback' => fn( $object ) => static::get_last_name( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'job_title',
            [
                'get_callback' => fn( $object ) => static::get_job_title( $object['id'] ),
            ]
        );

        register_rest_field(
            [ static::get_post_type() ],
            'description',
            [
                'get_callback' => fn( $object ) => static::get_description( $object['id'] ),
            ]
        );
    }

    /**
     * Get description
     *
     * @param int $post_id WP_Post ID.
     *
     * @return string
     */
    public static function get_description( $post_id ) {
        return wp_kses_post( get_field( 'description', $post_id ) );
    }

    /**
     * Get first name
     *
     * @param int $post_id WP_Post ID.
     *
     * @return mixed
     */
    public static function get_first_name( $post_id ) {
        return get_field( 'first_name', $post_id );
    }

    /**
     * Get last name
     *
     * @param int $post_id WP_Post ID.
     *
     * @return string
     */
    public static function get_last_name( $post_id ) {
        return esc_html( get_field( 'last_name', $post_id ) );
    }

    /**
     * Get job title
     *
     * @param int $post_id WP_Post ID.
     *
     * @return false|string
     */
    public static function get_job_title( $post_id ) {
        return get_field( 'job_title', $post_id );
    }

    /**
     * Add custom fields to Contact instance
     *
     * @param \WP_Post $contact Instance of WP_Post.
     *
     * @return \WP_Post
     */
    public static function add_fields_to_contact( $contact ) {
        $contact->first_name     = static::get_first_name( $contact->ID );
        $contact->last_name      = static::get_last_name( $contact->ID );
        $contact->description    = static::get_description( $contact->ID );
        $contact->job_title      = static::get_job_title( $contact->ID );
        $contact->featured_image = get_the_post_thumbnail_url( $contact->ID, 'medium_large' );

        return $contact;
    }
}
