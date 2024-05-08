<?php
/**
 * Place entity
 * https://api.hel.fi/linkedevents/v1/place/tprek:2692/
 */

namespace HKIH\LinkedEvents\API\Entities;

/**
 * Class Place
 *
 * @package HKIH\LinkedEvents\API\Entities
 */
class Place extends Entity {

    /**
     * Get id
     *
     * @return string
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

    /**
     * Get street address
     *
     * @return string|null
     */
    public function get_street_address() {
        return $this->get_key_by_language( 'street_address' );
    }

    /**
     * Get address locality
     *
     * @return string|null
     */
    public function get_address_locality() {
        return $this->get_key_by_language( 'address_locality' );
    }

    /**
     * Get postal code
     *
     * @return string|null
     */
    public function get_postal_code() {
        return $this->entity_data->postal_code ?? null;
    }

    /**
     * Get info url
     *
     * @return string|null
     */
    public function get_info_url() {
        return $this->get_key_by_language( 'info_url' );
    }

    /**
     * Get telephone
     *
     * @return string|null
     */
    public function get_telephone() {
        return $this->get_key_by_language( 'telephone' );
    }

    /**
     * Get neighborhood
     *
     * @return false|string|null
     */
    public function get_neighborhood() {
        if ( empty( $this->entity_data->divisions ) ) {
            return false;
        }

        foreach ( $this->entity_data->divisions as $division ) {
            if ( 'neighborhood' === $division->type ) {
                return $this->get_key_by_language( 'name', $division );
            }
        }

        return false;
    }

    /**
     * Get coordinates
     *
     * @return array|null
     */
    public function get_coordinates() {
        return $this->entity_data->position->coordinates ?? null;
    }
}
