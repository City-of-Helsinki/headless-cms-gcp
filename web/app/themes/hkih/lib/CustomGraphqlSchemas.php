<?php
/**
 * CustomGraphqlSchemas
 */

namespace Geniem\Theme;

use WP_Post;

/**
 * Class CustomGraphqlSchemas
 *
 * @package Geniem\Theme
 */
class CustomGraphqlSchemas implements Interfaces\Controller {

    /**
     * Runs Hooks on activation.
     */
    public function hooks() : void {
        \add_action(
            'graphql_register_types',
            \Closure::fromCallable( [ $this, 'register_page_by_template' ] )
        );

        \add_filter(
            'graphql_resolve_field',
            \Closure::fromCallable( [ $this, 'get_page_by_uri' ] ), 10, 9
        );
    }

    /**
     * Register Page By Template Graphql
     *
     * @return void
     */
    private function register_page_by_template() : void {

        $template_values = [
            'frontPage',
            'postsPage',
        ];

        register_graphql_enum_type( 'TemplateEnum', [
            'description' => __(
                'Get page object by template',
                'hkih'
            ),
            'values'      => $template_values,
        ] );

        $template_values = [
            'frontPage' => get_option( 'page_on_front' ),
            'postsPage' => get_option( 'page_for_posts' ),
        ];

        $config = [
            'type'    => 'Page',
            'description' => __( 'Returns ID of page that uses the given template', 'hkih' ),
            'args'    => [
                'language' => [
                    'type' => 'String',
                ],
                'template' => [
                    'type' => 'TemplateEnum',
                ],
            ],
            'resolve' => function ( $source, $args ) use ( $template_values ) {
                $language    = sanitize_text_field( $args['language'] );
                $template    = sanitize_text_field( $args['template'] );
                $page_object = $this->get_page_by_template( $template, $language, $template_values );

                return empty( $page_object ) ? null : new \WPGraphQL\Model\Post( $page_object );

            },
        ];

        \register_graphql_field( 'RootQuery', 'pageByTemplate', $config );
    }

    /**
     * Get page by template.
     *
     * @param string $template Template name.
     * @param string $language Language.
     * @param array  $templates Templates array.
     *
     * @return \WP_Post|null
     */
    private function get_page_by_template(
        string $template = '',
        string $language = 'fi',
        array $templates = []
    ) : ?WP_Post {
        if ( empty( $template ) || ! array_key_exists( $template, $templates ) ) {
            return null;
        }

        $lang_code = empty( $language ) ? 'fi' : $language;

        $page = pll_get_post( $templates[ $template ], $lang_code );

        return empty( $page ) ? null : get_post( $page );
    }

    /**
     * Get correct page by uri.
     *
     * @param mixed           $result The result of the field resolution
     * @param mixed           $source The source passed down the Resolve Tree
     * @param array           $args The args for the field
     * @param AppContext      $context The AppContext passed down the ResolveTree
     * @param ResolveInfo     $info The ResolveInfo passed down the ResolveTree
     * @param string          $type_name The name of the type the fields belong to
     * @param string          $field_key The name of the field
     * @param FieldDefinition $field The Field Definition for the resolving field
     * @param mixed           $field_resolver The default field resolver
     * @return \WPGraphQL\Model\Post|mixed
     */
    private function get_page_by_uri(
        $result,
        $source,
        $args,
        $context,
        $info,
        $type_name,
        $field_key,
        $field,
        $field_resolver
    ) {

        if ( $type_name === 'RootQuery' && $field_key === 'page' && $args['idType'] === 'uri' ) {

            $url     = $args['id'];
            $pattern = '/^\/([a-z]{2})\/(.*)/';

            // Use preg_match to find matches in the URL
            if ( preg_match( $pattern, $url, $matches ) ) {
                $lang = $matches[1];
                $slug = $matches[2];
            } else {
                return $result;
            }

            // Check if language exists.
            if ( in_array( $lang, \pll_languages_list() ) ) {

                $page = new \WP_Query( [
                    'post_type'      => 'page',
                    'lang'           => $lang,
                    'posts_per_page' => 1,
                    'pagename'       => $slug
                ] );

                if ( ! $page->found_posts ) {
                    return $result;
                }

                $result = new \WPGraphQL\Model\Post( $page->post );

            }

        }

        return $result;
    }
}
