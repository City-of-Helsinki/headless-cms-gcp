<?php

namespace Geniem\Theme\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use \Geniem\Theme\Logger;
use \Geniem\Theme\Localization;

/**
 * Class RedirectSettingsTab
 */
class RedirectSettingsTab extends Tab {

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
        $label = __( 'Redirects', 'hkih' );

        parent::__construct( $label );

        $this->sub_fields( $key );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {

        $strings = [
            'tab'               => __( 'Redirects', 'hkih' ),
            'redirects'         => [
                'label'        => __( 'Redirects', 'hkih' ),
                'instructions' => '',
            ],
            'redirect_from_uri' => [
                'label'        => __( 'Redirect from', 'hkih' ),
            ],
            'redirect_to_uri'   => [
                'label'        => __( 'Redirect to', 'hkih' ),
            ],
        ];

        try {
            $tab = new Field\Tab( $strings['tab'] );
            $tab->set_placement( 'left' );

            $redirects = ( new Field\Repeater( $strings['redirects']['label'] ) )
                ->set_key( "{$key}_redirects" )
                ->set_name( 'redirects' )
                ->set_instructions( $strings['redirects']['instructions'] );

            $redirect_from_uri = ( new Field\Text( $strings['redirect_from_uri']['label'] ) )
                ->set_key( "{$key}_redirect_from_uri" )
                ->set_name( 'redirect_from_uri' );

            $redirect_to_uri = ( new Field\Text( $strings['redirect_to_uri']['label'] ) )
                ->set_key( "{$key}_redirect_to_uri" )
                ->set_name( 'redirect_to_uri' );

            $redirects->add_fields( [
                $redirect_from_uri,
                $redirect_to_uri,
            ] );

            $this->add_fields( \apply_filters(
                'hkih_theme_settings_redirects', [
                    $redirects
                ],
                $key
            ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
