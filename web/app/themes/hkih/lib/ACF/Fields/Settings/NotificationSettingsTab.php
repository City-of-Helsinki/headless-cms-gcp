<?php

namespace Geniem\Theme\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use Geniem\Theme\PostType\Settings;
use \Geniem\Theme\Logger;

/**
 * Class NotificationSettingsTab
 */
class NotificationSettingsTab extends Tab {

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
        'tab'        => 'Huomiotiedote',
        'title'      => [
            'label'        => 'Otsikko',
            'instructions' => '',
        ],
        'content'    => [
            'label'        => 'Tiedote',
            'instructions' => '',
        ],
        'link'       => [
            'label'        => 'Lisätietolinkki',
            'instructions' => '',
        ],
        'level'      => [
            'label'        => 'Kriittisyys',
            'instructions' => 'Määritä huomiotiedotteen kriittisyys',
            'choices'      => [
                'info' => 'Info',
                'low'  => 'Matala',
                'high' => 'Korkea',
            ],
        ],
        'start_date' => [
            'label'        => 'Tiedote näkyvissä alkaen',
            'instructions' => '',
        ],
        'end_date'   => [
            'label'        => 'Tiedotteen viimeinen voimassaolopäivä',
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

        add_action(
            'rest_api_init',
            \Closure::fromCallable( [ $this, 'register_rest_fields' ] )
        );

        add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_notification_type' ] )
        );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        $strings = $this->strings;

        try {
            $title = ( new Field\Text( $strings['title']['label'] ) )
                ->set_key( "{$key}_notification_title" )
                ->set_name( 'notification_title' )
                ->set_instructions( $strings['title']['instructions'] );

            $content = ( new Field\Textarea( $strings['content']['label'] ) )
                ->set_key( "{$key}_notification_content" )
                ->set_name( 'notification_content' )
                ->set_new_lines( 'wpautop' )
                ->set_instructions( $strings['content']['instructions'] );

            $link = ( new Field\Link( $strings['link']['label'] ) )
                ->set_key( "{$key}_notification_link" )
                ->set_name( 'notification_link' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['link']['instructions'] );

            $level = ( new Field\Select( $strings['level']['label'] ) )
                ->set_key( "{$key}_notification_level" )
                ->set_name( 'notification_level' )
                ->set_choices( $strings['level']['choices'] )
                ->use_ui()
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['level']['instructions'] );

            $start_date = ( new Field\DatePicker( $strings['start_date']['label'] ) )
                ->set_key( "{$key}_notification_start_date" )
                ->set_name( 'notification_start_date' )
                ->set_display_format( 'd.m.Y' )
                ->set_return_format( 'c' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['start_date']['instructions'] );

            $end_date = ( new Field\DatePicker( $strings['end_date']['label'] ) )
                ->set_key( "{$key}_notification_end_date" )
                ->set_name( 'notification_end_date' )
                ->set_display_format( 'd.m.Y' )
                ->set_return_format( 'c' )
                ->set_wrapper_width( 50 )
                ->set_instructions( $strings['end_date']['instructions'] );

            $this->add_fields( [
                $title,
                $content,
                $link,
                $level,
                $start_date,
                $end_date,
            ] );
        }
        catch ( Exception $e ) {
            ( new Logger() )->debug( $e->getMessage() );
        }
    }

    /**
     * Register REST fields
     */
    protected function register_rest_fields() : void {
        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_title',
            [
                'get_callback' => fn( $object ) => self::get_notification_title(), // phpcs:ignore
            ]
        );

        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_content',
            [
                'get_callback' => fn( $object ) => self::get_notification_content(), // phpcs:ignore
            ]
        );

        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_link',
            [
                'get_callback' => fn( $object ) => self::get_notification_link(), // phpcs:ignore
            ]
        );

        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_level',
            [
                'get_callback' => fn( $object ) => self::get_notification_level(), // phpcs:ignore
            ]
        );

        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_start_date',
            [
                'get_callback' => fn( $object ) => self::get_notification_start_date(), // phpcs:ignore
            ]
        );

        register_rest_field(
            [ Settings::get_post_type() ],
            'notification_end_date',
            [
                'get_callback' => fn( $object ) => self::get_notification_end_date(), // phpcs:ignore
            ]
        );
    }

    /**
     * Register notification Graphql type
     */
    protected function register_notification_type() : void {
        register_graphql_object_type( 'Notification', [
            'description' => __( 'Describe what a CustomType is', 'hkih' ),
            'fields'      => [
                'title'     => [
                    'type'        => 'String',
                    'description' => __( 'Notification title', 'hkih' ),
                ],
                'content'   => [
                    'type'        => 'String',
                    'description' => __( 'Notification content', 'hkih' ),
                ],
                'linkText'  => [
                    'type'        => 'String',
                    'description' => __( 'Notification link text', 'hkih' ),
                ],
                'linkUrl'   => [
                    'type'        => 'String',
                    'description' => __( 'Notification link url', 'hkih' ),
                ],
                'level'     => [
                    'type'        => 'String',
                    'description' => __( 'Notification level', 'hkih' ),
                ],
                'startDate' => [
                    'type'        => 'String',
                    'description' => __( 'Notification start date', 'hkih' ),
                ],
                'endDate'   => [
                    'type'        => 'String',
                    'description' => __( 'Notification end date', 'hkih' ),
                ],
            ],
        ] );

        register_graphql_field(
            'RootQuery',
            'notification',
            [
                'type'    => 'Notification',
                'args'    => [
                    'language' => [
                        'type' => [
                            'non_null' => 'String',
                        ],
                    ],
                ],
                'resolve' => function ( $source, $args ) { // phpcs:ignore
                    $lang = $args['language'] ?? '';
                    $link = self::get_notification_link( $lang );

                    return [
                        'title'     => self::get_notification_title( $lang ),
                        'content'   => self::get_notification_content( $lang ),
                        'linkText'  => $link['title'] ?? '',
                        'linkUrl'   => $link['url'] ?? '',
                        'level'     => self::get_notification_level( $lang ),
                        'startDate' => self::get_notification_start_date( $lang ),
                        'endDate'   => self::get_notification_end_date( $lang ),
                    ];
                },
            ]
        );
    }

    /**
     * Get notification title
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_title( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_title', $lang );
    }

    /**
     * Get notification content
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_content( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_content', $lang );
    }

    /**
     * Get notification link
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_link( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_link', $lang );
    }

    /**
     * Get notification level
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_level( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_level', $lang );
    }

    /**
     * Get notification start date
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_start_date( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_start_date', $lang );
    }

    /**
     * Get notification end date
     *
     * @param string $lang Current language.
     *
     * @return mixed
     */
    public static function get_notification_end_date( $lang = '' ) {
        return \Geniem\Theme\Settings::get_setting( 'notification_end_date', $lang );
    }
}
