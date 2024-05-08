<?php
/**
 * This file contains the controller interface
 */

namespace Geniem\Theme\Interfaces;

interface Controller {
    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void;
}
