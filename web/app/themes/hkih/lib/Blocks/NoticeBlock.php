<?php
/**
 * Base for ACF blocks. Here we define useful method for all blocks.
 */

namespace Geniem\Theme\Blocks;

use Geniem\ACF\Field\Select;
use Geniem\ACF\Field\Text;
use Geniem\ACF\Field\Wysiwyg;
use Geniem\Theme\Logger;

/**
 * Class NoticeBlock.
 *
 * @property string title Block title.
 */
class NoticeBlock extends BaseBlock {
    /**
     * The block name, or actually the slug that is used to
     * register the block.
     *
     * @var string
     */
    const NAME = 'notice';

    /**
     * The block description. Used in WP block navigation.
     *
     * @var string
     */
    protected $description = 'Notice block';

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
    protected $icon = 'megaphone';

    /**
     * Create the block and register it.
     *
     * @throws \Exception Thrown if prerequisites fail.
     */
    public function __construct() {
        $this->title = 'Huomiotiedote';

        parent::__construct();
    }

    /**
     * Block fields
     *
     * @return array
     */
    public function fields() : array {
        $strings = [
            'title'       => [
                'label'        => 'Otsikko',
                'instructions' => '',
            ],
            'description' => [
                'label'        => 'Kuvaus',
                'instructions' => '',
            ],
            'icon'        => [
                'label'        => 'Ikoni',
                'instructions' => 'HDS ikoni https://hds.hel.fi/visual-assets/icons',
            ],
        ];

        $key = self::NAME;

        try {
            $title_field = ( new Text( $strings['title']['label'] ) )
                ->set_key( "{$key}_title" )
                ->set_name( 'title' )
                ->set_instructions( $strings['title']['instructions'] );

            $description_field = ( new Wysiwyg( $strings['description']['label'] ) )
                ->set_key( "{$key}_description" )
                ->set_name( 'description' )
                ->disable_media_upload()
                ->set_instructions( $strings['description']['instructions'] );

            $icon_field = ( new Select( $strings['icon']['label'] ) )
                ->set_key( "{$key}_icon" )
                ->set_name( 'icon' )
                ->set_choices( [
                    'angle-down'                   => 'Angle Down',
                    'angle-left'                   => 'Angle Left',
                    'angle-right'                  => 'Angle Right',
                    'angle-up'                     => 'Angle Up',
                    'arrow-down'                   => 'Arrow Down',
                    'arrow-left'                   => 'Arrow Left',
                    'arrow-right'                  => 'Arrow Right',
                    'arrow-up'                     => 'ArrowUp',
                    'cross'                        => 'Cross',
                    'cross-circle'                 => 'Cross Circle',
                    'cross-circle-fill'            => 'Cross Circle Fill',
                    'minus'                        => 'Minus',
                    'minus-circle'                 => 'Minus Circle',
                    'minus-circle-fill'            => 'Minus Circle Fill',
                    'plus'                         => 'Plus',
                    'plus-circle'                  => 'Plus Circle',
                    'plus-circle-fill'             => 'Plus Circle Fill',
                    'alert-circle'                 => 'Alert Circle',
                    'alert-circle-fill'            => 'Alert Circle Fill',
                    'check'                        => 'Check',
                    'check-circle'                 => 'Check Circle',
                    'check-circle-fill'            => 'Check Circle Fill',
                    'customer-bot-negative'        => 'Customer Bot Negative',
                    'customer-bot-neutral'         => 'Customer Bot Neutral',
                    'customer-bot-positive'        => 'Customer Bot Positive',
                    'info-circle'                  => 'Info Circle',
                    'info-circle-fill'             => 'Info Circle Fill',
                    'error'                        => 'Error',
                    'error-fill'                   => 'Error Fill',
                    'face-neutral'                 => 'Face Neutral',
                    'face-sad'                     => 'Face Sad',
                    'face-smile'                   => 'Face Smile',
                    'heart'                        => 'Heart',
                    'heart-fill'                   => 'Heart Fill',
                    'question-circle'              => 'Question Circle',
                    'question-circle-fill'         => 'Question Circle Fill',
                    'star'                         => 'Star',
                    'star-fill'                    => 'Star Fill',
                    'home'                         => 'Home',
                    'home-smoke'                   => 'Home Smoke',
                    'menu-hamburger'               => 'Menu Hamburger',
                    'menu-dots'                    => 'Menu Dots',
                    'refresh'                      => 'Refresh',
                    'signin'                       => 'Signin',
                    'signout'                      => 'Signout',
                    'search'                       => 'Search',
                    'user'                         => 'User',
                    'bell'                         => 'Bell',
                    'bell-crossed'                 => 'Bell Crossed',
                    'cogwheel'                     => 'Cogwheel',
                    'download'                     => 'Download',
                    'download-cloud'               => 'Download Cloud',
                    'drag'                         => 'Drag',
                    'eye'                          => 'Eye',
                    'eye-crossed'                  => 'Eye Crossed',
                    'lock'                         => 'Lock',
                    'lock-open'                    => 'Lock Open',
                    'save-diskette'                => 'Save Diskette',
                    'save-diskette-fill'           => 'Save Diskette Fill',
                    'share'                        => 'Share',
                    'upload'                       => 'Upload',
                    'upload-cloud'                 => 'Upload Cloud',
                    'zoom-in'                      => 'Zoom In',
                    'zoom-out'                     => 'Zoom Out',
                    'zoom-text'                    => 'Zoom Text',
                    'arrow-redo'                   => 'Arrow Redo',
                    'arrow-undo'                   => 'Arrow Undo',
                    'calendar'                     => 'Calendar',
                    'calendar-clock'               => 'Calendar Clock',
                    'calendar-cross'               => 'Calendar Cross',
                    'calendar-plus'                => 'Calendar Plus',
                    'calendar-recurring'           => 'Calendar Recurring',
                    'clock'                        => 'Clock',
                    'clock-cross'                  => 'ClockCross',
                    'clock-plus'                   => 'ClockPlus',
                    'document'                     => 'Document',
                    'envelope'                     => 'Envelope',
                    'globe'                        => 'Globe',
                    'group'                        => 'Group',
                    'layers'                       => 'Layers',
                    'link'                         => 'Link',
                    'link-external'                => 'Link External',
                    'locate'                       => 'Locate',
                    'location'                     => 'Location',
                    'map'                          => 'Map',
                    'paperclip'                    => 'Paperclip',
                    'pen'                          => 'Pen',
                    'pen-line'                     => 'Pen Line',
                    'person-female'                => 'Person Female',
                    'person-male'                  => 'Person Male',
                    'person-wheelchair'            => 'Person Wheelchair',
                    'phone'                        => 'Phone',
                    'photo'                        => 'Photo',
                    'photo-plus'                   => 'Photo Plus',
                    'sliders'                      => 'Sliders',
                    'sort'                         => 'Sort',
                    'sort-ascending'               => 'Sort Ascending',
                    'sort-descending'              => 'Sort Descending',
                    'sort-alphabetical-ascending'  => 'Sort Alphabetical Ascending',
                    'sort-alphabetical-descending' => 'Sort Alphabetical Descending',
                    'speechbubble'                 => 'Speechbubble',
                    'speechbubble-text'            => 'Speechbubble Text',
                    'text-bold'                    => 'Text Bold',
                    'text-italic'                  => 'Text Italic',
                    'text-tool'                    => 'Text Tool',
                    'trash'                        => 'Trash',
                    'camera'                       => 'Camera',
                    'display'                      => 'Display',
                    'headphones'                   => 'Headphones',
                    'microphone'                   => 'Microphone',
                    'microphone-crossed'           => 'Microphone Crossed',
                    'mobile'                       => 'Mobile',
                    'playback-fastforward'         => 'Playback Fastforward',
                    'playback-next'                => 'Playback Next',
                    'playback-pause'               => 'Playback Pause',
                    'playback-play'                => 'Playback Play',
                    'playback-previous'            => 'Playback Previous',
                    'playback-record'              => 'Playback Record',
                    'playback-rewind'              => 'Playback Rewind',
                    'playback-stop'                => 'Playback Stop',
                    'podcast'                      => 'Podcast',
                    'printer'                      => 'Printer',
                    'videocamera'                  => 'Videocamera',
                    'videocamera-crossed'          => 'Videocamera Crossed',
                    'volume-high'                  => 'Volume High',
                    'volume-low'                   => 'Volume Low',
                    'volume-minus'                 => 'Volume Minus',
                    'volume-mute'                  => 'Volume Mute',
                    'volume-plus'                  => 'Volume Plus',
                    'wifi'                         => 'Wifi',
                    'wifi-crossed'                 => 'Wifi Crossed',
                    'ticket'                       => 'Ticket',
                    'glyph-euro'                   => 'Glyph Euro',
                    'glyph-at'                     => 'Glyph At',
                    'cake'                         => 'Cake',
                    'shopping-cart'                => 'Shopping Cart',
                ] )
                ->set_instructions( $strings['icon']['instructions'] );

            return [
                $title_field,
                $description_field,
                $icon_field,
            ];
        }
        catch ( \Geniem\ACF\Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );

            return [];
        }
    }
}
