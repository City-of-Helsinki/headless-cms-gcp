<?php
/**
 * This class is used in getting the default taxonomy 'category' slug.
 */

namespace Geniem\Theme\Taxonomy;

use \Geniem\Theme\Interfaces\Taxonomy;

/**
 * This class defines the taxonomy.
 *
 * @package Geniem\Theme\Taxonomy
 */
class Category implements Taxonomy {
    /**
     * This defines the slug of this taxonomy.
     */
    const SLUG = 'category';

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void { }
}
