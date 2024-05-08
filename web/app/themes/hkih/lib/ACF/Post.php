<?php
/**
 * ACF fields for post
 *
 * @package Geniem\Theme\ACF
 */

namespace Geniem\Theme\ACF;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\Theme\ACF\Layouts\ArticleHighlightsLayout;
use Geniem\Theme\ACF\Layouts\ArticlesLayout;
use Geniem\Theme\Acf\Layouts\ArticlesCarouselLayout;
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

/**
 * Class Post
 *
 * @package Geniem\Theme\ACF
 */
class Post {

    /**
     * Page constructor.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_fields' ] );

        add_filter(
            'hkih_rest_acf_post_modules_layout_articles',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_articles',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_articles_carousel',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_articles_carousel',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_article_highlights',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_article_highlights',
            [ $this, 'articles_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_link_list',
            [ $this, 'link_list_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_link_list',
            [ $this, 'link_list_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_content',
            [ $this, 'content_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_content',
            [ $this, 'content_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_card',
            [ $this, 'card_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_card',
            [ $this, 'card_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_image',
            [ $this, 'image_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_image',
            [ $this, 'image_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_cards',
            [ $this, 'cards_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_cards',
            [ $this, 'cards_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_steps',
            [ $this, 'steps_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_steps',
            [ $this, 'steps_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_gallery',
            [ $this, 'gallery_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_gallery',
            [ $this, 'gallery_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_page_modules_layout_social_media_feed',
            [ $this, 'social_media_feed_rest_callback' ]
        );

        add_filter(
            'hkih_rest_acf_post_modules_layout_social_media_feed',
            [ $this, 'social_media_feed_rest_callback' ]
        );

        add_filter(
            'hkih_posttype_post_graphql_layouts',
            [ $this, 'register_graphql_layouts' ]
        );

        add_filter(
            'hkih_posttype_post_sidebar_graphql_layouts',
            [ $this, 'register_sidebar_graphql_layouts' ]
        );
    }

    /**
     * Register fields
     */
    public function register_fields() : void {
        $group_title = _x( 'Additional Fields', 'theme ACF', 'hkih' );
        $field_group = ( new Group( $group_title ) )
            ->set_key( 'fg_posttype_post' );

        $rules = [
            [
                'key'      => 'post_type',
                'value'    => PostType\Post::SLUG,
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
            'lead'      => [
                'title' => __( 'Lead Paragraph', 'hkih' ),
                'help'  => __( 'A lead paragraph is the opening paragraph that summarizes its main ideas.', 'hkih' ),
            ],
            'hide_published_date' => [
                'title' => __( 'Hide Published Date', 'hkih' ),
                'help'  => __( 'Status flag', 'hkih' ),
            ],
            'modules'   => [
                'title' => __( 'Modules', 'hkih' ),
                'help'  => __( 'Add content modules', 'hkih' ),
            ],
            'sidebar'   => [
                'title' => __( 'Sidebar Modules', 'hkih' ),
                'help'  => __( 'Add sidebar modules', 'hkih' ),
            ],
        ];

        try {
            $lead = ( new Field\Textarea( $strings['lead']['title'] ) )
                ->set_key( $field_group->get_key() . '_lead' )
                ->set_name( 'lead' )
                ->set_default_value( false )
                ->set_rows( 2 )
                ->set_new_lines()
                ->update_value( function ( $value, $post_id ) {
                    \wp_update_post( [
                        'ID'           => $post_id,
                        'post_excerpt' => $value,
                    ] );

                    return $value;
                } )
                ->set_instructions( $strings['lead']['help'] );

            $field_group->add_field( $lead );

            $hide_published_date = ( new Field\TrueFalse( $strings['hide_published_date']['title'] ) )
                ->set_key( $field_group->get_key() . 'hide_published_date' )
                ->set_name( 'hide_published_date' )
                ->set_default_value( false )
                ->use_ui()
                ->set_instructions( $strings['hide_published_date']['help'] );

            $field_group->add_field( $hide_published_date );

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
                new ImageGalleryLayout( $field_group->get_key() . '_modules' )
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
                'hkih_acf_post_modules_layouts',
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
                'hkih_posttype_post_fields',
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

        $layouts[ $articles_key ]           = $articles_key;
        $layouts[ $articles_carousel_key ]  = $articles_carousel_key;
        $layouts[ $article_highlights_key ] = $article_highlights_key;
        $layouts[ $pages_key ]              = $pages_key;
        $layouts[ $pages_carousel_key ]     = $pages_carousel_key;
        $layouts[ $content_key ]            = $content_key;
        $layouts[ $card_key ]               = $card_key;
        $layouts[ $image_key ]              = $image_key;
        $layouts[ $cards_key ]              = $cards_key;
        $layouts[ $steps_key ]              = $steps_key;
        $layouts[ $gallery_key ]            = $gallery_key;
        $layouts[ $social_media_feed_key ]  = $social_media_feed_key;

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
     * Articles REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function articles_rest_callback( array $layout ) : array {
        if ( empty( $layout['articles'] ) ) {
            $args = [
                'post_type'      => PostType\Post::SLUG,
                'posts_per_page' => $layout['limit'] ?? 6,
                'no_found_rows'  => true,
            ];

            if ( ! empty( $layout['offset'] ) ) {
                $args['offset'] = $layout['offset'];
            }

            if ( ! empty( $layout['category'] ) ) {
                $args['category__in'] = $layout['category'];
            }

            if ( ! empty( $layout['tag'] ) ) {
                $args['tag__in'] = $layout['tag'];
            }

            $the_query = new \WP_Query( $args );
            $articles  = $the_query->posts;
        }
        else {
            $articles = $layout['articles'];
        }

        if ( ! empty( $articles ) ) {
            $articles = array_map( function ( $article ) {
                $article->featured_image = get_the_post_thumbnail_url(
                    $article->ID,
                    'medium_large'
                );

                return $article;
            }, $articles );
        }

        $show_more = ! empty( $layout['show_more'] ) ? $layout['show_more'] : [];

        return [
            'title'            => $layout['title'],
            'anchor'           => $layout['anchor'],
            'background_color' => $layout['background_color'],
            'articles'         => $articles,
            'show_more'        => $show_more,
            'show_all_link'    => $layout['show_all_link'],
            'module'           => $layout['acf_fc_layout'],
        ];
    }

    /**
     * Link list REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function link_list_rest_callback( array $layout ) : array {
        return [
            'title'            => $layout['title'],
            'description'      => $layout['description'],
            'anchor'           => $layout['anchor'],
            'background_color' => $layout['background_color'],
            'links'            => $layout['links'] ?? [],
            'module'           => 'link_list',
        ];
    }

    /**
     * Content REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function content_rest_callback( array $layout ) : array {
        return [
            'title'            => $layout['title'],
            'background_color' => $layout['background_color'],
            'content'          => $layout['content'],
            'module'           => 'content',
        ];
    }

    /**
     * Card REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function card_rest_callback( array $layout ) : array {

        $image                 = [];
        $image['title']        = $layout['image']['title'] ?? '';
        $image['caption']      = $layout['image']['caption'] ?? '';
        $image['description']  = $layout['image']['description'] ?? '';
        $image['thumbnail']    = $layout['image']['sizes']['thumbnail'] ?? '';
        $image['medium']       = $layout['image']['sizes']['medium'] ?? '';
        $image['medium_large'] = $layout['image']['sizes']['medium_large'] ?? '';
        $image['large']        = $layout['image']['sizes']['large'] ?? '';

        return [
            'image'            => $image,
            'alignment'        => $layout['alignment'],
            'title'            => $layout['title'],
            'description'      => $layout['description'],
            'link'             => $layout['link'] ?? [],
            'background_color' => $layout['background_color'],
            'module'           => 'card',
        ];
    }

    /**
     * Image REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function image_rest_callback( array $layout ) : array {

        $image                 = [];
        $image['title']        = $layout['image']['title'];
        $image['caption']      = $layout['image']['caption'];
        $image['description']  = $layout['image']['description'];
        $image['thumbnail']    = $layout['image']['sizes']['thumbnail'];
        $image['medium']       = $layout['image']['sizes']['medium'];
        $image['medium_large'] = $layout['image']['sizes']['medium_large'];
        $image['large']        = $layout['image']['sizes']['large'];
        $photographer_name     = $layout['photographer_name'] ? $layout['photographer_name'] : \get_field( 'photographer_name', $layout['image']['ID'] );

        return [
            'image'             => $image,
            'border'            => $layout['border'],
            'photographer_name' => $photographer_name,
            'show_on_lightbox'  => $layout['show_on_lightbox'],
            'module'            => 'image',
        ];
    }

    /**
     * Cards REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function cards_rest_callback( array $layout ) : array {

        return [
            'cards'  => $layout['cards'],
            'module' => 'cards',
        ];
    }

    /**
     * Steps REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function steps_rest_callback( array $layout ) : array {

        $steps = [];

        if ( ! empty( $layout['steps'] ) ) {
            foreach ( $layout['steps'] as $step ) {
                $steps[] = [
                    'title'   => $step['step_title'],
                    'content' => $step['step_content'],
                ];
            }
        }

        return [
            'title'       => $layout['title'],
            'description' => $layout['description'],
            'type'        => $layout['type'],
            'color'       => $layout['color'],
            'steps'       => $steps,
            'module'      => 'steps',
         ];
    }

    /**
     * Image Gallery REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function gallery_rest_callback( array $layout ) : array {
        $gallery = [];

        if ( ! empty( $layout['gallery'] ) ) {
            foreach ( $layout['gallery'] as $image ) {
                $gallery[] = [
                    'title'        => $image['title'] ?? '',
                    'caption'      => $image['caption'] ?? '',
                    'description'  => $image['description'] ?? '',
                    'thumbnail'    => $image['sizes']['thumbnail'] ?? '',
                    'medium'       => $image['sizes']['medium'] ?? '',
                    'medium_large' => $image['sizes']['medium_large'] ?? '',
                    'large'        => $image['sizes']['large'] ?? '',
                ];
            }
        }

        return [
            'gallery' => $gallery,
            'module'  => 'gallery',
        ];
    }

    /**
     * Social Media Feed REST callback
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function social_media_feed_rest_callback( array $layout ) : array {
        return [
            'title'            => $layout['title'],
            'anchor'           => $layout['anchor'],
            'script'           => $layout['script'],
            'module'           => 'social_media_feed',
        ];
    }
}

( new Post() );
