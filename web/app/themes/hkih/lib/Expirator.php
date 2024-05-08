<?php
/**
 * This file handles automated post expiration.
 */

namespace Geniem\Theme;

/**
 * Class Expirator
 *
 * @package Geniem\Theme
 */
class Expirator implements Interfaces\Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        // Hook to post saving.
        \add_action( 'save_post', \Closure::fromCallable( [ $this, 'schedule_expiration' ] ), 102, 2 );

        // Add the post expiring method to the corresponding cron event hook.
        \add_action( 'expire_post', \Closure::fromCallable( [ $this, 'expire' ] ), 10, 1 );
    }

    /**
     * This schedules expiration events for each post
     * that has an expiration time set. First we remove previous events
     * and then set a new one if the time is set.
     *
     * The expiration is checked and set only for offers and deals.
     *
     * @param int      $post_id The post ID.
     * @param \WP_Post $post    The post object.
     *
     * @return void
     */
    private function schedule_expiration( int $post_id, \WP_Post $post ) : void {
        $allowed_post_types = \Geniem\Theme\ACF\ExpiratorGroup::get_expiring_post_types();

        if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
            return;
        }

        // Remove a previous event for this specific post.
        \wp_clear_scheduled_hook( 'expire_post', [ $post_id ] );

        // Try to schedule an expiration event only if the post is still published.
        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $expiration_time = \get_field( 'expiration_time', $post_id );

        // Schedule an expiration event only if there's a value in the ACF field.
        if ( ! empty( $expiration_time ) ) {

            // Convert the ACF DateTime field value to a timestamp.
            $timestamp = \strtotime( $expiration_time );

            /**
             * WP doesn't use localised time in scheduled events so we convert
             * the localised timestamp to a GMT string and
             * then back to a timestamp. This way all locales work correctly.
             */
            $offset_hours    = (int) \get_option( 'gmt_offset' );
            $timezone_offset = $offset_hours * 3600;
            $gmt_timestamp   = $timestamp - $timezone_offset;

            // Schedule the new cron event.
            \wp_schedule_single_event( $gmt_timestamp, 'expire_post', [ $post_id ] );
        }
    }

    /**
     * This changes the posts statuses to drafts.
     *
     * @param int $post_id The post ID.
     *
     * @return void
     */
    private function expire( int $post_id ) : void {
        $post = \get_post( $post_id );

        // Check that the post exists and is published.
        if ( ! empty( $post ) && $post->post_status === 'publish' ) {

            // Set post as draft.
            $success = \wp_update_post(
                [
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                ]
            );

            if ( ! empty( $success ) ) {
                ( new \Geniem\Theme\Logger() )->info(
                    sprintf( 'Post with ID %d set as draft.', $post_id )
                );
            }
            else {
                ( new \Geniem\Theme\Logger() )->error(
                    sprintf( 'Post with ID %d could not be set as draft.', $post_id )
                );
            }
        }
    }
}
