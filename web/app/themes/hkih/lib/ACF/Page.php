<?php
/**
 * ACF fields for page
 *
 * @package Geniem\Theme\ACF
 */

namespace Geniem\Theme\ACF;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\ACF\Layouts\ArticleHighlightsLayout;
use Geniem\Theme\ACF\Layouts\ArticlesCarouselLayout;
use Geniem\Theme\ACF\Layouts\ArticlesLayout;
use Geniem\Theme\ACF\Layouts\LinkListLayout;
use Geniem\Theme\ACF\Layouts\PagesLayout;
use Geniem\Theme\ACF\Layouts\PagesCarouselLayout;
use Geniem\Theme\ACF\Layouts\ContentLayout;
use Geniem\Theme\ACF\Layouts\CardLayout;
use Geniem\Theme\ACF\Layouts\ImageLayout;
use Geniem\Theme\ACF\Layouts\CardsLayout;
use Geniem\Theme\ACF\Layouts\StepsLayout;
use Geniem\Theme\ACF\Layouts\ImageGalleryLayout;
use Geniem\Theme\ACF\Layouts\SocialMediaFeedLayout;
use Geniem\Theme\Logger;
use Geniem\Theme\PostType;
use Geniem\Theme\Settings;

/**
 * Class Page
 *
 * @package Geniem\Theme\ACF
 */
class Page {

    /**
     * Page constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_fields' ] );

        add_filter(
            'hkih_rest_acf_post_modules_layout_pages',
            [ $this, 'pages_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_pages',
            [ $this, 'pages_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_pages_carousel',
            [ $this, 'pages_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_pages_carousel',
            [ $this, 'pages_rest_callback' ]
        );

        add_filter(
            'hkih_posttype_page_graphql_layouts',
            [ $this, 'register_graphql_layouts' ]
        );

        add_filter(
            'hkih_posttype_page_sidebar_graphql_layouts',
            [ $this, 'register_sidebar_graphql_layouts' ]
        );
    }

    /**
     * Register fields
     */
    public function register_fields() : void {

        // Hero Fields
        $hero_group_title = _x( 'Hero', 'theme ACF', 'hkih' );
        $hero_field_group = ( new Group( $hero_group_title ) )
            ->set_key( 'fg_posttype_page_hero' );

        $hero_rules = [
            [
                'key'      => 'post_type',
                'value'    => PostType\Page::SLUG,
                'operator' => '==',
            ],
        ];

        $hero_rule_group = new RuleGroup();

        foreach ( $hero_rules as $rule ) {
            try {
                $hero_rule_group->add_rule( $rule['key'], $rule['operator'], $rule['value'] );
            }
            catch ( Exception $e ) {
                ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
            }
        }

        $hero_field_group->add_rule_group( $hero_rule_group );

        try {
            $hero_field_group->set_position( 'normal' )
                ->set_menu_order( 5 )
                ->set_hidden_elements( [
                    'discussion',
                    'comments',
                    'format',
                    'send-trackbacks',
                ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }

        $hero_strings = [
            'hero_title'       => [
                'title' => __( 'Title', 'hkih' ),
                'help'  => __( 'Insert title', 'hkih' ),
            ],
            'hero_description' => [
                'title' => __( 'Description', 'hkih' ),
                'help'  => __( 'Insert description', 'hkih' ),
            ],
            'hero_bg_color'    => [
                'title' => __( 'Background color', 'hkih' ),
                'help'  => __( 'Choose background color', 'hkih' ),
            ],
            'hero_link'        => [
                'title' => __( 'Link', 'hkih' ),
                'help'  => __( 'Choose link', 'hkih' ),
            ],
            'hero_wave_motif'  => [
                'title' => __( 'Wave motif', 'hkih' ),
                'help'  => __( 'Choose wave motif', 'hkih' ),
            ],
        ];

        try {
            $hero_title = ( new Field\Text(
                $hero_strings['hero_title']['title'],
                $hero_field_group->get_key() . '_hero_title',
                'hero_title'
            ) )
                ->set_instructions( $hero_strings['hero_title']['help'] );

            $hero_field_group->add_field( $hero_title );

            $hero_description = ( new Field\Textarea(
                $hero_strings['hero_description']['title'],
                $hero_field_group->get_key() . '_hero_description',
                'hero_description'
            ) )
                ->set_instructions( $hero_strings['hero_description']['help'] );

            $hero_field_group->add_field( $hero_description );

            $hero_background_color = ( new Field\Select( $hero_strings['hero_bg_color']['title'] ) )
                ->set_key( $hero_field_group->get_key() . '_hero_bg_color' )
                ->set_name( 'hero_bg_color' )
                ->allow_null()
                ->set_choices( \apply_filters( 'hkih_hds_brand_colors', [] ) )
                ->set_wrapper_width( 50 );

            $hero_field_group->add_field( $hero_background_color );

            $hero_link = ( new Field\Link( $hero_strings['hero_link']['title'] ) )
                ->set_key( $hero_field_group->get_key() . '_hero_link' )
                ->set_name( 'hero_link' )
                ->set_wrapper_width( 50 );

            $hero_field_group->add_field( $hero_link );

            $hero_wave_motifs = ( new Field\Select( $hero_strings['hero_wave_motif']['title'] ) )
                ->set_key( $hero_field_group->get_key() . '_hero_wave_motif' )
                ->set_name( 'hero_wave_motif' )
                ->allow_null()
                ->set_default_value( Settings::get_setting( 'wave_motif' ) )
                ->set_choices( \apply_filters( 'hkih_wave_motifs', [] ) )
                ->set_wrapper_width( 50 );

            $hero_field_group->add_field( $hero_wave_motifs );

            $fields = apply_filters(
                'fg_posttype_page_hero_fields',
                $hero_field_group->get_fields(),
                $hero_field_group->get_key()
            );

            if ( ! empty( $fields ) ) {
                $hero_field_group->set_fields( $fields );
            }

            $field_group = apply_filters(
                'hkih_acf_group_' . $hero_field_group->get_key(),
                $hero_field_group
            );

            $hero_field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        // Additional Fields.
        $group_title = _x( 'Additional Fields', 'theme ACF', 'hkih' );
        $field_group = ( new Group( $group_title ) )
            ->set_key( 'fg_posttype_page' );

        $rules = [
            [
                'key'      => 'post_type',
                'value'    => PostType\Page::SLUG,
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

        try {
            $field_group->set_position( 'normal' )
                ->set_menu_order( 10 )
                ->set_hidden_elements( [
                    'discussion',
                    'comments',
                    'format',
                    'send-trackbacks',
                ] );
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }

        $strings = [
            'lead'             => [
                'title' => __( 'Lead Paragraph', 'hkih' ),
                'help'  => __( 'A lead paragraph is the opening paragraph that summarizes its main ideas.', 'hkih' ),
            ],
            'show_child_pages' => [
                'title' => __( 'Show Child Pages', 'hkih' ),
                'help'  => __( 'Status flag', 'hkih' ),
            ],
            'modules'          => [
                'title' => __( 'Modules', 'hkih' ),
                'help'  => __( 'Add content modules', 'hkih' ),
            ],
            'sidebar'          => [
                'title' => __( 'Sidebar Modules', 'hkih' ),
                'help'  => __( 'Add sidebar modules', 'hkih' ),
            ],
        ];

        try {
            $lead = ( new Field\Textarea(
                $strings['lead']['title'],
                $field_group->get_key() . '_lead',
                'lead'
            ) )
                ->set_default_value( false )
                ->set_rows( 2 )
                ->set_new_lines()
                ->set_instructions( $strings['lead']['help'] );

            $field_group->add_field( $lead );

            $show_child_pages = ( new Field\TrueFalse( $strings['show_child_pages']['title'] ) )
                ->set_key( $field_group->get_key() . '_show_child_pages' )
                ->set_name( 'show_child_pages' )
                ->set_default_value( false )
                ->use_ui()
                ->set_instructions( $strings['show_child_pages']['help'] );

            $field_group->add_field( $show_child_pages );

            $modules = ( new Field\FlexibleContent( __( 'Modules', 'hkih' ) ) )
                ->set_key( $field_group->get_key() . '_modules' )
                ->set_name( 'modules' );

            $modules->add_layout(
                new ArticlesLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new ArticlesCarouselLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new ArticleHighlightsLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new PagesLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new PagesCarouselLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new ContentLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new CardLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new ImageLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new CardsLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new StepsLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new ImageGalleryLayout( $field_group->get_key() . '_modules' )
            );

            $modules->add_layout(
                new SocialMediaFeedLayout( $field_group->get_key() . '_modules' )
            );

            $modules = apply_filters(
                'hkih_acf_page_modules_layouts',
                $modules
            );

            $field_group->add_field( $modules );

            $sidebar = ( new Field\FlexibleContent( __( 'Sidebar', 'hkih' ) ) )
                ->set_key( $field_group->get_key() . '_sidebar' )
                ->set_name( 'sidebar' );

            $sidebar->add_layout(
                new ArticlesLayout( $field_group->get_key() . '_sidebar' )
            );

            $sidebar->add_layout(
                new PagesLayout( $field_group->get_key() . '_sidebar' )
            );

            $sidebar->add_layout(
                new LinkListLayout( $field_group->get_key() . '_sidebar' )
            );

            $sidebar->add_layout(
                new CardsLayout( $field_group->get_key() . '_sidebar' )
            );

            $sidebar = apply_filters(
                'hkih_acf_page_sidebar_layouts',
                $sidebar
            );

            $field_group->add_field( $sidebar );

            $fields = apply_filters(
                'hkih_posttype_page_fields',
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

    /**
     * Register GraphQL Layouts for this PostType.
     *
     * @param array $layouts GraphQL Layouts.
     *
     * @return array
     */
    public function register_graphql_layouts( array $layouts = [] ) : array {
        $articles_key           = Layouts\ArticlesLayout::GRAPHQL_LAYOUT_KEY;
        $articles_carousel_key  = Layouts\ArticlesCarouselLayout::GRAPHQL_LAYOUT_KEY;
        $article_highlights_key = Layouts\ArticleHighlightsLayout::GRAPHQL_LAYOUT_KEY;
        $pages_key              = Layouts\PagesLayout::GRAPHQL_LAYOUT_KEY;
        $pages_carousel_key     = Layouts\PagesCarouselLayout::GRAPHQL_LAYOUT_KEY;
        $content_key            = Layouts\ContentLayout::GRAPHQL_LAYOUT_KEY;
        $card_key               = Layouts\CardLayout::GRAPHQL_LAYOUT_KEY;
        $image_key              = Layouts\ImageLayout::GRAPHQL_LAYOUT_KEY;
        $cards_key              = Layouts\CardsLayout::GRAPHQL_LAYOUT_KEY;
        $steps_key              = Layouts\StepsLayout::GRAPHQL_LAYOUT_KEY;
        $gallery_key            = Layouts\ImageGalleryLayout::GRAPHQL_LAYOUT_KEY;
        $social_media_feed_key  = Layouts\SocialMediaFeedLayout::GRAPHQL_LAYOUT_KEY;

        $layouts[ $articles_key ]             = $articles_key;
        $layouts[ $articles_carousel_key ]    = $articles_carousel_key;
        $layouts[ $article_highlights_key ]   = $article_highlights_key;
        $layouts[ $pages_key ]                = $pages_key;
        $layouts[ $pages_carousel_key ]       = $pages_carousel_key;
        $layouts[ $content_key ]              = $content_key;
        $layouts[ $card_key ]                 = $card_key;
        $layouts[ $image_key ]                = $image_key;
        $layouts[ $cards_key ]                = $cards_key;
        $layouts[ $steps_key ]                = $steps_key;
        $layouts[ $gallery_key ]              = $gallery_key;
        $layouts[ $social_media_feed_key ]    = $social_media_feed_key;
        $layouts['EventSearch']               = 'EventSearch';
        $layouts['EventSelected']             = 'EventSelected';
        $layouts['EventSearchCarousel']       = 'EventSearchCarousel';
        $layouts['EventSelectedCarousel']     = 'EventSelectedCarousel';
        $layouts['LocationsSelectedCarousel'] = 'LocationsSelectedCarousel';

        return $layouts;
    }

    /**
     * Register GraphQL Layouts for this PostType sidebar.
     *
     * @param array $layouts GraphQL Layouts.
     *
     * @return array
     */
    public function register_sidebar_graphql_layouts( array $layouts = [] ) : array {
        $link_list_key = Layouts\LinkListLayout::GRAPHQL_LAYOUT_KEY;
        $articles_key  = Layouts\ArticlesLayout::GRAPHQL_LAYOUT_KEY;
        $pages_key     = Layouts\PagesLayout::GRAPHQL_LAYOUT_KEY;
        $cards_key     = Layouts\CardsLayout::GRAPHQL_LAYOUT_KEY;

        $layouts[ $link_list_key ] = $link_list_key;
        $layouts[ $articles_key ]  = $articles_key;
        $layouts[ $pages_key ]     = $pages_key;
        $layouts[ $cards_key ]     = $cards_key;

        return $layouts;
    }

    /**
     * Pages REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function pages_rest_callback( array $layout ) : array {
        if ( ! empty( $layout['pages'] ) ) {
            $pages = array_map( function ( $page ) {
                $page->featured_image = get_the_post_thumbnail_url(
                    $page->ID,
                    'medium_large'
                );

                return $page;
            }, $layout['pages'] );
        }

        return [
            'title'            => $layout['title'],
            'anchor'           => $layout['anchor'],
            'description'      => $layout['description'],
            'background_color' => $layout['background_color'],
            'show_all_link'    => $layout['show_all_link'],
            'pages'            => $pages ?? [],
            'module'           => $layout['acf_fc_layout'],
        ];
    }
}

( new Page() );
