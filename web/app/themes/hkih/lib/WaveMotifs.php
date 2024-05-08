<?php
/**
 * WaveMotif functionality
 */

namespace Geniem\Theme;

/**
 * Class WaveMotifs
 *
 * @package Geniem\Theme
 */
class WaveMotifs implements Interfaces\Controller {

    /**
     * Hooks
     */
    public function hooks() : void {
        \add_filter( 'hkih_wave_motifs', [ $this, 'get_wave_motifs' ] );
    }

    /**
     * Get wave motifs.
     *
     * @return string[]
     */
    public function get_wave_motifs() {
        return [
            'calm'  => __( 'Calm', 'hkih' ),
            'basic' => __( 'Basic', 'hkih' ),
            'pulse' => __( 'Pulse', 'hkih' ),
            'beat'  => __( 'Beat', 'hkih' ),
            'storm' => __( 'Storm', 'hkih' ),
            'wave'  => __( 'Wave', 'hkih' ),
        ];
    }
}
