<?php
/**
 * HKIH Utilities Class
 */

namespace Geniem\Theme;

/**
 * Class Utils
 *
 * @package Geniem\Theme
 */
class Utils {
    /**
     * GraphQL Type Resolve helper.
     *
     * @param array|string $layout Layout string or array.
     *
     * @return null|string|string[]
     */
    public static function resolve_layout_type( $layout = '' ) {
        if ( is_array( $layout ) && ! empty( $layout['module'] ) ) {
            return self::snake_to_camel( $layout['module'] );
        }

        if ( ! empty( $layout ) && is_string( $layout ) ) {
            return $layout;
        }

        return null;
    }

    /**
     * Convert snake_case to CamelCase.
     *
     * @param string $string                   String to convert from snake_case to CamelCase.
     * @param bool   $capitalizeFirstCharacter Should the first letter be uppercase.
     *
     * @return string|string[]
     */
    public static function snake_to_camel( string $string = '', bool $capitalizeFirstCharacter = true ) {
        $str = str_replace(
            ' ',
            '',
            ucwords(
                str_replace( [ '-', '_' ], ' ', $string )
            )
        );

        if ( ! $capitalizeFirstCharacter ) {
            $str[0] = strtolower( $str[0] );
        }

        return $str;
    }

    /**
     * Add to layouts.
     *
     * Used in layout registration hooks.
     *
     * @param array|string $fields Fields array, or whatever value you want to add.
     * @param string       $key    Layout or module name.
     *
     * @return \Closure
     */
    public static function add_to_layouts( $fields, string $key ) : \Closure {
        return static function ( array $layouts = [] ) use ( $fields, $key ) {
            $layouts[ $key ] = $fields;

            return $layouts;
        };
    }

    /**
     * Get Modules and run filters.
     *
     * @param int    $post_id WP_Post ID.
     * @param string $filter  Filter to run.
     * @param string $key     Field key.
     *
     * @return array
     */
    public static function get_modules( int $post_id, string $filter = '', string $key = 'modules' ) : array {
        $modules = get_field( $key, $post_id );
        $handled = [];

        if ( empty( $modules ) ) {
            return $handled;
        }

        // Filter unpublished items from modules.
        $modules = array_filter( $modules, function ( $module ) {
            try {
                $module = \Geniem\Theme\Utils::array_flatten( (array) $module );

                if ( \array_key_exists( 'post_status', $module ) ) {
                    return $module['post_status'] === 'publish';
                }

                // Fallback to other types
                return true;
            }
            catch ( \Exception $e ) {
                return false;
            }
        } );

        foreach ( $modules as $layout ) {
            $acf_layout = $layout['acf_fc_layout'] ?? '';
            $layout     = apply_filters( $filter, $layout );
            $layout     = apply_filters( "{$filter}_{$acf_layout}", $layout );

            $handled[] = $layout;
        }

        return $handled;
    }

    /**
     * Flatten multidimensional array to single dimension.
     *
     * @see https://stackoverflow.com/a/14972389
     *
     * @param array $array Array to flatten.
     *
     * @return array
     */
    public static function array_flatten( array $array ) : array {
        $return = [];
        foreach ( $array as $key => $value ) {
            if ( \is_object( $value ) ) {
                $value = (array) $value;
            }
            if ( is_array( $value ) ) {
                $return = array_merge( $return, self::array_flatten( $value ) );
            }
            else {
                $return[ $key ] = $value;
            }
        }

        return $return;
    }

    /**
     * Register PostType related GraphQL Types and UnionTypes.
     *
     * @see \Geniem\Theme\Utils::type_module_to_layout()
     *
     * @param string        $type_slug       PostType Slug.
     * @param array         $graphql_modules Modules.
     * @param array         $graphql_layouts Layouts.
     * @param callable|null $type_resolver   The GraphQL Field Resolver. get_modules() usually.
     * @param string        $key             Field key.
     *
     * @throws \Exception The register_graphql_union_type throws exception on error.
     */
    public static function register_modules_and_union_types(
        string $type_slug = '',
        array $graphql_modules = [],
        array $graphql_layouts = [],
        callable $type_resolver = null,
        string $key = 'modules'
    ) : void {
        if (
            empty( $type_slug ) ||
            ! is_callable( $type_resolver ) ||
            ( empty( $graphql_modules ) && empty( $graphql_layouts ) )
        ) {
            return;
        }

        $union_type_name = sprintf(
            '%s%sUnionType', ucfirst( $type_slug ), ucfirst( $key )
        );

        $type_names = array_unique( array_merge(
            array_keys( $graphql_modules ),
            array_keys( $graphql_layouts )
        ) );

        register_graphql_union_type( $union_type_name, [
            'typeNames'   => $type_names,
            'resolveType' => fn( $layout ) => self::resolve_union_type( $layout ),
        ] );

        register_graphql_field( $type_slug, $key, [
            'type'        => [ 'list_of' => $union_type_name ],
            'description' => __( 'List of modules', 'hkih' ),
            'resolve'     => $type_resolver,
        ] );
    }

    /**
     * Resolve UnionType
     *
     * @see \Geniem\Theme\Utils::type_module_to_layout()
     *
     * @param mixed $obj Input.
     *
     * @return string|string[]
     */
    public static function resolve_union_type( $obj ) {
        if ( is_array( $obj ) && ! empty( $obj['module'] ) ) {
            $obj['module'] = self::type_module_to_layout( $obj['module'] );

            return self::snake_to_camel( $obj['module'] );
        }

        if ( $obj instanceof \WP_Post ) {
            $type = get_post_type( $obj );

            return self::get_post_type_graphql_single_name( $type );
        }

        if ( ! empty( $obj ) && is_string( $obj ) ) {
            return $obj;
        }

        return (string) $obj;
    }

    /**
     * ACF Group Name to GraphQL Type.
     *
     * @param string $module ACF Group Name.
     *
     * @return string
     */
    public static function type_module_to_layout( string $module = '' ) : string {
        switch ( $module ) {
            case 'pages':
                return 'LayoutPages';
            case 'pages_carousel':
                return 'LayoutPagesCarousel';
            case 'articles':
                return 'LayoutArticles';
            case 'article_highlights':
                return 'LayoutArticleHighlights';
            case 'articles_carousel':
                return 'LayoutArticlesCarousel';
            case 'collection':
                return 'LayoutCollection';
            case 'contacts':
                return 'LayoutContact';
            case 'event_selected':
                return 'EventSelected';
            case 'event_search':
                return 'EventSearch';
            case 'event_search_carousel':
                return 'EventSearchCarousel';
            case 'event_selected_carousel':
                return 'EventSelectedCarousel';
            case 'location_selected':
            case 'locations_selected':
                return 'LocationsSelected';
            case 'locations_selected_carousel':
                return 'LocationsSelectedCarousel';
            case 'link_list':
                return 'LayoutLinkList';
            case 'content':
                return 'LayoutContent';
            case 'card':
                return 'LayoutCard';
            case 'image':
                return 'LayoutImage';
            case 'cards':
                return 'LayoutCards';
            case 'steps':
                return 'LayoutSteps';
            case 'gallery':
                return 'LayoutImageGallery';
            case 'social_media_feed':
                return 'LayoutSocialMediaFeed';
            default:
                return $module;
        }
    }

    /**
     * Returns the graphql_single_name attribute from WP_Post_Type Object.
     *
     * @param string|null $slug WordPress PostType Slug.
     *
     * @return string
     */
    public static function get_post_type_graphql_single_name( string $slug = null ) : string {
        if ( empty( trim( $slug ) ) ) {
            return '';
        }

        global $wp_post_types;
        $post_type_object = $wp_post_types[ $slug ] ?? false;

        if ( empty( $post_type_object ) || ! ( $post_type_object instanceof \WP_Post_Type ) ) {
            return '';
        }

        return $post_type_object->graphql_single_name ?? '';
    }

    /**
     * Create a GraphQL Image Connection Resolver.
     *
     * @see https://www.wpgraphql.com/recipes/register-connection-to-attached-media/
     *
     * @param \WPGraphQL\Model\Post                $source     Source.
     * @param array                                $args       Array of arguments as part of the GraphQL query.
     * @param \WPGraphQL\AppContext                $context    Object containing app context.
     * @param \GraphQL\Type\Definition\ResolveInfo $info       Info about fields passed down the resolve tree.
     * @param string                               $field_name ACF Field name.
     *
     * @return array|\GraphQL\Deferred|mixed
     * @throws \Exception If PostObjectConnectionResolver doesn't get connection.
     */
    public static function resolve_image(
        \WPGraphQL\Model\Post $source,
        array $args,
        \WPGraphQL\AppContext $context,
        \GraphQL\Type\Definition\ResolveInfo $info,
        string $field_name
    ) {
        $field_data = get_field( $field_name, $source->ID );

        $resolver = new \WPGraphQL\Data\Connection\PostObjectConnectionResolver(
            $source,
            $args,
            $context,
            $info,
            'attachment'
        );
        $resolver->set_query_arg( 'post__in', [ $field_data['ID'] ] );

        return $resolver->get_connection();
    }
}
