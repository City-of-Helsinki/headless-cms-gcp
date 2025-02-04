<?php
/**
 * Base for ACF blocks. Here we define useful method for all blocks.
 */

namespace Geniem\Theme\Blocks;

use Exception;
use \Geniem\ACF\Block;
use \Geniem\ACF\Renderer\CallableRenderer;
use Geniem\ACF\Renderer\PHP;

/**
 * Class BaseBlock.
 *
 * @property string title Block title.
 */
class BaseBlock {
    /**
     * The block name, or actually the slug that is used to
     * register the block.
     *
     * @var string
     */
    const NAME = '';

    /**
     * The block description. Used in WP block navigation.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The block title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * The block category. Used in WP block navigation.
     *
     * @var string
     */
    protected $category = 'common';

    /**
     * The block icon. Used in WP block navigation.
     *
     * @var string
     */
    protected $icon = 'menu';

    /**
     * The block mode. ACF has a few different options.
     * Edit opens the block always in edit mode for example.
     *
     * @var string
     */
    protected $mode = 'edit';

    /**
     * The block supports. You can add all ACF support attributes here.
     *
     * @var array
     */
    protected $supports = [
        'align'  => false,
        'anchor' => true,
    ];

    /**
     * Class constructor.
     */
    public function __construct() {
        $block = new Block( $this->title, static::NAME );
        $block->set_category( $this->category );
        $block->set_icon( $this->icon );
        $block->set_description( $this->description );
        $block->set_mode( $this->mode );
        $block->set_supports( $this->supports );
        $block->set_renderer( $this->get_renderer() );

        // Maybe add block fields.
        if ( method_exists( static::class, 'fields' ) ) {
            $block->add_fields( $this->fields() );
        }

        $block->add_data_filter( [ $this, 'base_filter_data' ], 5 );

        // Maybe filter the block data.
        if ( method_exists( static::class, 'filter_data' ) ) {
            $block->add_data_filter( [ $this, 'filter_data' ] );
        }

        $block->register();
    }

    /**
     * This filters the block ACF data.
     *
     * @param array  $data       Block's ACF data.
     * @param Block  $instance   The block instance.
     * @param array  $block      The original ACF block array.
     * @param string $content    The HTML content.
     * @param bool   $is_preview A flag that shows if we're in preview.
     * @param int    $post_id    The parent post's ID.
     *
     * @return array The block data.
     */
    public function base_filter_data( $data, $instance, $block, $content, $is_preview, $post_id ) : array { // phpcs:ignore
        if ( isset( $this->supports['anchor'] ) && $this->supports['anchor'] ) {
            $data['anchor'] = $block['anchor'] ?? '';
        }

        return $data;
    }

    /**
     * Get the renderer.
     * If dust partial is not found in child theme, we will use the parent theme partial.
     *
     * @throws Exception Thrown if template is not found.
     *
     * @param string $name Dust partial name, defaults to block name.
     *
     * @return PHP|CallableRenderer
     */
    protected function get_renderer( string $name = '' ) {
        $name = $name ?: static::NAME;
        $file = get_stylesheet_directory() . '/partials/blocks/' . $name . '.php';

        if ( file_exists( $file ) ) {
            return new PHP( $file );
        }

        return new CallableRenderer( function ( $data ) {
            return print_r( $data, true ); // phpcs:ignore
        } );
    }

    /**
     * Getter for block name.
     *
     * @return string
     */
    public function get_name() : string {
        return static::NAME;
    }
}
