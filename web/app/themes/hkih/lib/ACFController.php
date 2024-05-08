<?php
/**
 * This file controls ACF functionality.
 */

namespace Geniem\Theme;
use Geniem\Theme\ACF\Layouts\ArticlesLayout;
use Geniem\Theme\ACF\Layouts\ArticlesCarouselLayout;
use Geniem\Theme\ACF\Layouts\ArticleHighlightsLayout;


/**
 * Define the controller class.
 */
class ACFController implements Interfaces\Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        \add_action(
            'acf/init',
            \Closure::fromCallable( [ $this, 'require_acf_files' ] ),
            0
        );

        // Hide ACF menu page.
        \add_filter( 'acf/settings/show_admin', '__return_false' );

        // Filter posts in the relationship field.
        \add_filter(
            'acf/fields/relationship/query',
            \Closure::fromCallable( [ $this, 'relationship_options_filter' ] ),
        );

        // Filters out drafts from relationship field.
        // Otherwise module won't return data.
        \add_filter( 'acf/load_value/key=fg_posttype_page_modules' . ArticlesLayout::KEY . '_articles',
            \Closure::fromCallable( [ $this, 'article_relationship_filter' ] ),
        );

        \add_filter( 'acf/load_value/key=fg_posttype_page_modules' . ArticlesCarouselLayout::KEY . '_articles',
            \Closure::fromCallable( [ $this, 'article_relationship_filter' ] ),
        );

        \add_filter( 'acf/load_value/key=fg_posttype_page_modules' . ArticleHighlightsLayout::KEY . '_articles',
            \Closure::fromCallable( [ $this, 'article_relationship_filter' ] ),
        );
    }

    /**
     * This method loops through all files in the
     * ACF directory and requires them.
     */
    private function require_acf_files() {
        $files = array_diff( scandir( __DIR__ . '/ACF' ), [ '.', '..', 'Fields', 'Layouts' ] );

        // Loop through all files and directories except Fields where we store utility fields.
        array_walk( $files, function ( $file ) {
            require_once __DIR__ . '/ACF/' . basename( $file );
        } );
    }

    /**
     * Filters the query $args used by WP_Query to display posts in the Relationship field.
     *
     * @param array $args The query args. See WP_Query for available args.
     * @return array
     */
    private function relationship_options_filter( $args ) {

        $args['post_status'] = [ 'publish' ];

        return $args;
    }

    /**
     * Filters the query $args used by WP_Query to display posts in the Relationship field.
     *
     * @param array $args The query args. See WP_Query for available args.
     * @return array
     */
    private function article_relationship_filter( $value ) {

        // In this case $value contains array of post ids.
        if( ! empty( $value )  )  {
            foreach ( $value as $key => $post_id ) {
                $post = \get_post( $post_id );
                if ( $post->post_status !== 'publish' ) {
                    unset( $value[ $key ] );
                }
            }
        }

        return $value;
    }
}
