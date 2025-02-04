<?php
/**
 * Contact ACF Layout
 */

namespace HKIH\CPT\Contact\ACF;

use Geniem\ACF\Field;
use Geniem\Theme\Logger;
use HKIH\CPT\Contact\PostTypes\Contact;

/**
 * Class ContactLayout
 *
 * @package HKIH\CPT\Contact\ACF
 */
class ContactLayout extends \Geniem\ACF\Field\Flexible\Layout {

    /**
     * Layout key
     */
    const KEY = '_contacts';
    /**
     * Translations.
     *
     * @var array[]
     */
    private array $strings;
    /**
     * GraphQL Layout Key
     */
    const GRAPHQL_LAYOUT_KEY = 'LayoutContact';

    /**
     * Create the layout
     *
     * @param string $key Key from the flexible content.
     */
    public function __construct( $key ) {
        $label = __( 'Contacts', 'hkih-contact' );
        $key   = $key . self::KEY;
        $name  = 'contacts';

        parent::__construct( $label, $key, $name );

        $this->strings = [
            'title'       => [
                'label'        => __( 'Title', 'hkih-contact' ),
                'instructions' => '',
            ],
            'description' => [
                'label'        => __( 'Description', 'hkih-contact' ),
                'instructions' => '',
            ],
            'contacts'    => [
                'label'        => __( 'Contacts', 'hkih-contact' ),
                'instructions' => '',
            ],
        ];

        $this->add_layout_fields();

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_graphql_fields' ] )
        );
    }

    /**
     * Add layout fields
     *
     * @return void
     */
    private function add_layout_fields() : void {
        $key = $this->get_key();

        try {
            $title_field = ( new Field\Text( $this->strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_instructions( $this->strings['title']['instructions'] );

            $description_field = ( new Field\Textarea( $this->strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->set_new_lines( 'wpautop' )
                ->set_rows( 4 )
                ->set_instructions( $this->strings['description']['instructions'] );

            $contacts_field = ( new Field\Relationship( $this->strings['contacts']['label'] ) )
                ->set_key( "{$key}_contacts" )
                ->set_name( 'contacts' )
                ->set_post_types( [ Contact::get_post_type() ] )
                ->set_filters( [ 'search' ] )
                ->set_instructions( $this->strings['contacts']['instructions'] );

            $this->add_fields( [
                $title_field,
                $description_field,
                $contacts_field,
            ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Register Layout fields to GraphQL.
     */
    private function register_graphql_fields() : void {
        $key = self::GRAPHQL_LAYOUT_KEY;

        // If the layout is already known/initialized, no need to register it again.
        if ( array_key_exists( $key, \apply_filters( 'hkih_graphql_layouts', [] ) ) ) {
            return;
        }

        $fields = [
            'title'       => [
                'type'        => 'String',
                'description' => $this->strings['title']['label'],
            ],
            'description' => [
                'type'        => 'String',
                'description' => $this->strings['description']['label'],
            ],
            'contacts'    => [
                'type'        => [ 'list_of' => Contact::get_graphql_single_name() ],
                'description' => $this->strings['contacts']['label'],
            ],
        ];

        register_graphql_object_type( $key, [
            'description' => sprintf(
            /* translators: %s is layout name */
                __( 'Layout: %s', 'hkih' ),
                $key
            ),
            'fields'      => $fields,
        ] );

        \add_filter( 'hkih_graphql_layouts', function ( array $layouts = [] ) use ( $fields, $key ) {
            $layouts[ $key ] = $fields;

            return $layouts;
        } );
    }
}
