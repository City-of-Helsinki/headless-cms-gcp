<?php
/**
 * Keyword entity
 *
 * @link: https://api.hel.fi/linkedevents/v1/keyword/yso:p5121/
 * @link: https://dev.hel.fi/apis/linkedevents#documentation
 */

namespace HKIH\LinkedEvents\API\Entities;

/**
 * Class Keyword
 *
 * @package HKIH\LinkedEvents\API\Entities
 */
class Keyword extends Entity {

    /**
     * Get id
     *
     * @return mixed
     */
    public function get_id() {
        return $this->entity_data->id;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function get_name() {
        return $this->get_key_by_language( 'name' );
    }
}
