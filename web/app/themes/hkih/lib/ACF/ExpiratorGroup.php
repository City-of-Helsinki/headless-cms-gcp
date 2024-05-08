<?php
/**
 * Expiration ACF group.
 */

namespace Geniem\Theme\ACF;

use \Geniem\ACF\Exception;
use \Geniem\ACF\Field;
use \Geniem\ACF\Group;
use \Geniem\ACF\RuleGroup;
use \Geniem\Theme\Logger;
use \Geniem\Theme\PostType;

/**
 * Class ExpiratorGroup
 *
 * @package Geniem\Theme\ACF
 */
class ExpiratorGroup {

    /**
     * ExpiratorGroup constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_fields' ], 101, 0 ); // This has to be later than plugins initialisation
        add_action( 'init', [ $this, 'register_expiration_as_graphql_field' ], 999 ); // This has to be among last
    }

    /**
     * Register Expirator Fields.
     */
    public function register_fields() : void {
        $group_title = _x( 'Timed expiration', 'theme ACF', 'hkih' );

        $rules      = [];
        $post_types = self::get_expiring_post_types();

        if ( ! empty( $post_types ) ) {
            foreach ( $post_types as $post_type_slug ) {
                $rules[ $post_type_slug ] = [
                    'key'      => 'post_type',
                    'value'    => $post_type_slug,
                    'operator' => '==',
                ];
            }
        }

        $rules = apply_filters( 'hkih_acf_group_expiration_rules', $rules );

        try {
            // Create a field group
            $field_group = ( new Group( $group_title ) )
                ->set_key( 'fg_expiration' )
                ->set_position( 'side' );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );

            return;
        }

        if ( ! empty( $rules ) ) {
            foreach ( $rules as $rule ) {
                try {
                    $rule_group = new RuleGroup();
                    $rule_group->add_rule( $rule['key'], $rule['operator'], $rule['value'] );
                    $field_group->add_rule_group( $rule_group );
                }
                catch ( Exception $e ) {
                    ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
                }
            }
        }

        // Strings for field titles and instructions.
        $strings = [
            'expiration_time' => [
                'title'        => 'Expiration time',
                'instructions' => 'Please pick a time when post will be returned to draft status.',
            ],
        ];

        try {
            // Expiration field
            $expiration_time_label = $strings['expiration_time']['title'];
            $expiration_time_field = ( new Field\DateTimePicker( $expiration_time_label ) )
                ->set_key( $field_group->get_key() . '_expiration_time' )
                ->set_name( 'expiration_time' )
                ->set_display_format( 'j.n.Y H.i' )
                ->set_return_format( 'j.n.Y H.i' )
                ->set_instructions( $strings['expiration_time']['instructions'] )
                ->hide_label();

            $field_group->add_field( $expiration_time_field );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }

        $field_group = apply_filters( 'hkih_acf_group_expiration', $field_group );

        // Register the field group
        $field_group->register();
    }

    /**
     * Get list of PostTypes allowed to expire.
     *
     * @return array
     */
    public static function get_expiring_post_types() : array {
        return apply_filters(
            'hkih_expirator_post_types',
            [
                PostType\Post::SLUG,
                PostType\Page::SLUG,
            ]
        );
    }

    /**
     * Automatically register expirationTime to all PostTypes we know to have such field.
     */
    public function register_expiration_as_graphql_field() : void {
        $expiring_post_types_array = self::get_expiring_post_types();

        if ( empty( $expiring_post_types_array ) ) {
            ( new Logger() )->error( 'No fields detected' );

            return;
        }

        foreach ( $expiring_post_types_array as $post_type_slug ) {
            $graphql_name = \Geniem\Theme\Utils::get_post_type_graphql_single_name( $post_type_slug );

            if ( empty( $graphql_name ) ) {
                ( new Logger() )->debug( 'No GraphQL name found for post type ' . $post_type_slug );

                continue;
            }

            \register_graphql_field( $graphql_name, 'expirationTime', [
                'type'        => 'String',
                'description' => __( 'Expiration time', 'hkih' ),
                'resolve'     => function ( $post ) {
                    $timestamp = get_field( 'expiration_time', $post->ID );

                    if ( empty( $timestamp ) ) {
                        return '';
                    }

                    // Convert the ACF DateTime field value to a timestamp.
                    $timestamp = \strtotime( $timestamp );

                    /**
                     * WP doesn't use localised time in scheduled events so we convert
                     * the localised timestamp to a GMT string and
                     * then back to a timestamp. This way all locales work correctly.
                     */
                    $offset_hours    = (int) \get_option( 'gmt_offset' );
                    $timezone_offset = $offset_hours * 3600;
                    $gmt_timestamp   = $timestamp - $timezone_offset;

                    return date( 'c', $gmt_timestamp ) ?? '';
                },
            ] );
        }
    }
}

new ExpiratorGroup();
