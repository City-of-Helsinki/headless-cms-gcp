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
class CustomAdminStylesTab extends Tab {

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
        'tab'    => 'Hallintanäkymän käytettävyysmuutokset',
        'admin_css'    => [
            'label'        => 'CSS -tyylit',
            'instructions' => '',
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
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        $strings = $this->strings;

        try {

            $admin_css = ( new Field\Textarea( $strings['admin_css']['label'] ) )
            ->set_key( "{$key}_admin_css" )
            ->set_name( 'admin_css' )
            ->set_instructions( $strings['admin_css']['instructions'] );

            $this->add_field( $admin_css );
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }
}
