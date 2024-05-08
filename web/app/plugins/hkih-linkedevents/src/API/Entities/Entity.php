<?php
/**
 * Entity
 */

namespace HKIH\LinkedEvents\API\Entities;

use Geniem\Theme\Localization;

/**
 * Class Entity
 *
 * @package HKIH\LinkedEvents\API\Entities
 */
class Entity {

    /**
     * Entity data
     *
     * @var mixed
     */
    protected $entity_data;

    /**
     * Entity constructor.
     *
     * @param mixed $entity_data Entity data.
     */
    public function __construct( $entity_data ) {
        $this->entity_data = $entity_data;
    }

    /**
     * Get key by language
     *
     * @param string      $key         Event object key.
     * @param bool|object $entity_data Entity data.
     *
     * @return string|null
     */
    protected function get_key_by_language( string $key, $entity_data = false ) {
        $current_language = Localization::get_current_language();
        $default_language = Localization::get_default_language();

        if ( ! $entity_data ) {
            $entity_data = $this->entity_data;
        }

        if ( isset( $entity_data->{$key} ) ) {
            if ( isset( $entity_data->{$key}->{$current_language} ) ) {
                return $entity_data->{$key}->{$current_language};
            }

            if ( isset( $entity_data->{$key}->{$default_language} ) ) {
                return $entity_data->{$key}->{$default_language};
            }
        }

        return null;
    }

    /**
     * Get key values
     *
     * @param string $key Entity object key.
     *
     * @return string|null
     */
    public function get_key_values( string $key ) {
        if ( isset( $this->entity_data->{$key} ) ) {
            return $this->entity_data->{$key};
        }

        return null;
    }
}
