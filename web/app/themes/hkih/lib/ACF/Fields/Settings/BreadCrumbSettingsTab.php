<?php

namespace Geniem\Theme\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use \Geniem\Theme\Logger;
use \Geniem\Theme\Localization;
use \Geniem\Theme\PostType;

/**
 * Class BreadCrumbSettingsTab
 */
class BreadCrumbSettingsTab extends Tab {

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
        $label = __( 'Breadcrumbs', 'hkih' );

        parent::__construct( $label );

        \add_filter(
            'hkih_generate_page_choices',
            \Closure::fromCallable( [ $this, 'generate_page_choices' ] ),
        );

        $this->sub_fields( $key );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        try {
            $hidden_pages = ( new Field\Select( __( 'Hide selected pages from breadcrumbs', 'hkih' ) ) )
                ->set_key( $key . '_hidden_pages' )
                ->set_name( 'hidden_pages' )
                ->use_ui()
                ->set_choices( \apply_filters( 'hkih_generate_page_choices', [] ) )
                ->allow_multiple();

            $this->add_fields( \apply_filters(
                'hkih_theme_settings_breadcrumbs', [
                    $hidden_pages
                ],
                $key
            ) );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public static function generate_page_choices() {
        $settings_page_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

        $args = [
            'post_type'      => PostType\Page::SLUG,
            'post_status'    => 'publish',
            'posts_per_page' => '200',
            'order'          => 'ASC',
            'orderby'        => 'title',
            'lang'           => \pll_get_post_language( $settings_page_id ),
        ];

        $query = new \WP_Query( $args );
        $pages = $query->posts ?? [];

        if ( empty( $pages ) ) {
            return [];
        }

        foreach ( $pages as $page ) {
            $options[ $page->ID ] = $page->post_title;
        }

        return $options;
    }
}
