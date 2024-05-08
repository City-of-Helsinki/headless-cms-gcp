<?php
/**
 * ACF fields for attachment
 *
 * @package Geniem\Theme\ACF
 */

namespace Geniem\Theme\ACF;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\Logger;

/**
 * Class Attachment
 *
 * @package Geniem\Theme\ACF
 */
class Attachment {

    /**
     * Attachment constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_fields' ] );
    }

    /**
     * Register fields
     */
    public function register_fields() : void {
        $group_title = _x( 'Additional Fields', 'theme ACF', 'hkih' );
        $field_group = ( new Group( $group_title ) )
            ->set_key( 'fg_posttype_attachment' );

        $rules = [
            [
                'key'      => 'attachment',
                'value'    => 'all',
                'operator' => '==',
            ],
        ];

        $rule_group = new RuleGroup();

        foreach ( $rules as $rule ) {
            try {
                $rule_group->add_rule( $rule['key'], $rule['operator'], $rule['value'] );
            }
            catch ( Exception $e ) {
                ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
            }
        }

        $field_group->add_rule_group( $rule_group );

        $strings = [
            'photographer' => [
                'title' => 'Valokuvaajan nimi',
                'help'  => 'Muista kuvaoikeudet!',
            ],
        ];

        try {
            $photographer_name = ( new Field\Text(
                $strings['photographer']['title'],
                $field_group->get_key() . '_photographer_name',
                'photographer_name'
            ) )
                ->set_instructions( $strings['photographer']['help'] )
                ->set_default_value( '' );

            $field_group->add_field( $photographer_name );

            $fields = apply_filters(
                'hkih_posttype_attachment_fields',
                $field_group->get_fields(),
                $field_group->get_key()
            );

            if ( ! empty( $fields ) ) {
                $field_group->set_fields( $fields );
            }

            $field_group = apply_filters(
                'hkih_acf_group_' . $field_group->get_key(),
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}

new Attachment();
