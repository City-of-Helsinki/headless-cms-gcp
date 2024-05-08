<?php
/**
 * Event entity
 *
 * @link: https://api.hel.fi/linkedevents/v1/event/helsinki:afxsfaqz44/?include=keywords,location
 * @link: https://dev.hel.fi/apis/linkedevents#documentation
 */

namespace HKIH\LinkedEvents\API\Entities;

use DateTime;
use Exception;
use Geniem\Theme\Logger;
use Geniem\Theme\Settings;

/**
 * Class Event
 *
 * @package HKIH\LinkedEvents\API\Entities
 */
class Event extends Entity {

    /**
     * Get Id
     *
     * @return mixed
     */
    public function get_id() {
        return $this->entity_data->id;
    }

    /**
     * Has super event
     *
     * @return bool
     */
    public function has_super_event() {
        return ! empty( $this->entity_data->super_event );
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
     * Get status
     *
     * @return mixed
     */
    public function get_status() {
        return $this->entity_data->event_status ?? null;
    }

    /**
     * Get short description
     *
     * @return string|null
     */
    public function get_short_description() {
        return $this->get_key_by_language( 'short_description' );
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function get_description() {
        return $this->get_key_by_language( 'description' );
    }

    /**
     * Get start time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function get_start_time() {
        if ( empty( $this->entity_data->start_time ) ) {
            return null;
        }

        try {
            return new DateTime( $this->entity_data->start_time );
        }
        catch ( Exception $e ) {
            ( new Logger() )->info( $e->getMessage(), $e->getTrace() );
        }

        return null;
    }

    /**
     * Get end time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function get_end_time() {
        if ( empty( $this->entity_data->end_time ) ) {
            return null;
        }

        try {
            return new DateTime( $this->entity_data->end_time );
        }
        catch ( Exception $e ) {
            ( new Logger() )->info( $e->getMessage(), $e->getTrace() );
        }

        return null;
    }

    /**
     * Get location
     *
     * @return Place
     */
    public function get_location() {
        return new Place( $this->entity_data->location ?? null );
    }
}
