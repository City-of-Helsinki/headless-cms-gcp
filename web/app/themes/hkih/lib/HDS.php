<?php
/**
 * HDS functionality
 */

namespace Geniem\Theme;

/**
 * Class HDS
 *
 * @package Geniem\Theme
 */
class HDS implements Interfaces\Controller {

    /**
     * Hooks
     */
    public function hooks() : void {
        \add_filter( 'hkih_hds_brand_colors', [ $this, 'get_brand_colors' ] );
        \add_filter( 'hkih_hds_icons', [ $this, 'get_icons' ] );
    }

    /**
     * Get HDS brand colors
     *
     * @return string[]
     */
    public function get_brand_colors() {
        return [
            'coat-of-arms' => 'Vaakuna - sininen',
            'gold'         => 'Kulta - kulta',
            'silver'       => 'Hopea - hopea',
            'brick'        => 'Tiili - tummanpunainen',
            'bus'          => 'Bussi - tummansininen',
            'copper'       => 'Kupari - turkoosi',
            'engel'        => 'Engel -vaaleankeltainen',
            'fog'          => 'Sumu - vaaleansininen',
            'metro'        => 'Metro - oranssi',
            'summer'       => 'Kesä - keltainen',
            'suomenlinna'  => 'Suomenlinna - pinkki',
            'tram'         => 'Spåra - vihreä',
        ];
    }

    /**
     * Get HDS icons
     *
     * @return string[]
     */
    public function get_icons() {
        $icons = [
            'angle-down'                   => 'IconAngleDown, Dropdown open',
            'angle-left'                   => 'IconAngleLeft',
            'angle-right'                  => 'IconAngleRight',
            'angle-up'                     => 'IconAngleUp, Dropdown close',
            'arrow-down'                   => 'IconArrowDown',
            'arrow-left'                   => 'IconArrowLeft, Previous',
            'arrow-right'                  => 'IconArrowRight, Next',
            'arrow-up'                     => 'IconArrowUp',
            'arrow-bottom-left'            => 'IconArrowBottomLeft',
            'arrow-bottom-right'           => 'IconArrowBottomRight',
            'arrow-top-left'               => 'IconArrowTopLeft',
            'arrow-top-right'              => 'IconArrowTopRight',
            'cross'                        => 'IconCross, Close',
            'cross-circle'                 => 'IconCrossCircle, Remove',
            'cross-circle-fill'            => 'IconCrossCircleFill, Remove alternative',
            'minus'                        => 'IconMinus',
            'minus-circle'                 => 'IconMinusCircle',
            'minus-circle-fill'            => 'IconMinusCircleFill',
            'plus'                         => 'IconPlus',
            'plus-circle'                  => 'IconPlusCircle, Add',
            'plus-circle-fill'             => 'IconPlusCircleFill,	Add alternative',
            'alert-circle'                 => 'IconAlertCircle, Alert',
            'alert-circle-fill'            => 'IconAlertCircleFill, Alert alternative',
            'check'                        => 'IconCheck',
            'check-circle'                 => 'IconCheckCircle, Success',
            'check-circle-fill'            => 'IconCheckCircleFill, Success alternative',
            'customer-bot-negative'        => 'IconCustomerBotNegative, Bot customer service, negative feedback',
            'customer-bot-neutral'         => 'IconCustomerBotNeutral,	Bot customer service, neutral feedback',
            'customer-bot-positive'        => 'IconCustomerBotPositive, Bot customer service, positive feedback',
            'info-circle'                  => 'IconInfoCircle,	Information',
            'info-circle-fill'             => 'IconInfoCircleFill,	Information alternative',
            'error'                        => 'IconError, Error',
            'error-fill'                   => 'IconErrorFill, Error alternative',
            'face-neutral'                 => 'IconFaceNeutral',
            'face-sad'                     => 'IconFaceSad',
            'face-smile'                   => 'IconFaceSmile',
            'heart'                        => 'IconHeart, Like',
            'heart-fill'                   => 'IconHeartFill, Like selected',
            'question-circle'              => 'IconQuestionCircle,	Help, Tooltip',
            'question-circle-fill'         => 'IconQuestionCircleFill,	Help alternative',
            'star'                         => 'IconStar, Rating',
            'star-fill'                    => 'IconStarFill, Rating selected',
            'thumbs-down'                  => 'IconThumbsDown,	Dislike, No (E.g. in voting)',
            'thumbs-down-fill'             => 'IconThumbsDownFill,	Dislike, No (E.g. in voting)',
            'thumbs-up'                    => 'IconThumbsUp, Like, Yes (E.g. in voting)',
            'thumbs-up-fill'               => 'IconThumbsUpFill, Like, Yes (E.g. in voting)',
            'home'                         => 'IconHome, Home',
            'home-smoke'                   => 'IconHomeSmoke',
            'menu-hamburger'               => 'IconMenuHamburger, Mobile menu',
            'menu-dots'                    => 'IconMenuDots, Additional operations',
            'refresh'                      => 'IconRefresh, Refresh page or content',
            'signin'                       => 'IconSignin, Sign in',
            'signout'                      => 'IconSignout, Sign out',
            'sitemap'                      => 'IconSitemap, Sitemap',
            'search'                       => 'IconSearch, Search',
            'user'                         => 'IconUser, Username',
            'swap-user'                    => 'IconSwapUser, Changing user account or profile',
            'bell'                         => 'IconBell, Notification',
            'bell-crossed'                 => 'IconBellCrossed, Disable notifications',
            'cogwheel'                     => 'IconCogwheel, Settings',
            'copy'                         => 'IconCopy, Copy',
            'download'                     => 'IconDownload, Download file to device',
            'download-cloud'               => 'IconDownloadCloud, Download from cloud service',
            'drag'                         => 'IconDrag, Drag handle for draggable elements',
            'eye'                          => 'IconEye, Show content',
            'eye-crossed'                  => 'IconEyeCrossed, Hide content',
            'lock'                         => 'IconLock, Content locked',
            'lock-open'                    => 'IconLockOpen, Content unlocked',
            'save-diskette'                => 'IconSaveDiskette, Saving',
            'save-diskette-fill'           => 'IconSaveDisketteFill, Saving alternative',
            'share'                        => 'IconShare, Share content or link',
            'upload'                       => 'IconUpload, Upload file to server',
            'upload-cloud'                 => 'IconUploadCloud, Upload to cloud service',
            'zoom-in'                      => 'IconZoomIn, Increase zoom level',
            'zoom-out'                     => 'IconZoomOut, Decrease zoom level',
            'zoom-text'                    => 'IconZoomText, Text zoom settings',
            'arrow-redo'                   => 'IconArrowRedo, Redo edits',
            'arrow-undo'                   => 'IconArrowUndo, Undo edits',
            'calendar'                     => 'IconCalendar, Date',
            'calendar-clock'               => 'IconCalendarClock, Date and time',
            'calendar-cross'               => 'IconCalendarCross, Remove date',
            'calendar-plus'                => 'IconCalendarPlus, Add date',
            'calendar-recurring'           => 'IconCalendarRecurring, Recurring date',
            'children'                     => 'IconChildren, Children',
            'clock'                        => 'IconClock, Time',
            'clock-cross'                  => 'IconClockCross, Remove time',
            'clock-plus'                   => 'IconClockPlus, Add time',
            'company'                      => 'IconCompany, Company name',
            'document'                     => 'IconDocument, Document / PDF',
            'entrepreneur'                 => 'IconEntrepreneur, Work status',
            'envelope'                     => 'IconEnvelope, Email address / message',
            'event'                        => 'IconEvent, Event',
            'family'                       => 'IconFamily, Information about family',
            'globe'                        => 'IconGlobe, Web page address',
            'group'                        => 'IconGroup, Group',
            'layers'                       => 'IconLayers, Choose layers',
            'link'                         => 'IconLink',
            'link-external'                => 'IconLinkExternal, Link to another website / opens in other tab',
            'locate'                       => 'IconLocate,	Show users location',
            'location'                     => 'IconLocation, Location',
            'map'                          => 'IconMap, Map',
            'mover'                        => 'IconMover, Immigrant information',
            'occupation'                   => 'IconOccupation, Occupation',
            'paperclip'                    => 'IconPaperclip, Attachment',
            'pen'                          => 'IconPen	Edit, content',
            'pen-line'                     => 'IconPenLine, Fill content',
            'person-female'                => 'IconPersonFemale, Gender female',
            'person-genderless'            => 'IconPersonGenderless, Non-binary gender',
            'person-male'                  => 'IconPersonMale, Gender male',
            'person-wheelchair'            => 'IconPersonWheelchair, Accessibility',
            'phone'                        => 'IconPhone, Phone number',
            'photo'                        => 'IconPhoto, Photo or image file',
            'photo-plus'                   => 'IconPhotoPlus, Add photo or image file',
            'senior'                       => 'IconSenior, Senior',
            'sliders'                      => 'IconSliders, Filter settings',
            'sort'                         => 'IconSort, Toggle sorting',
            'sort-ascending'               => 'IconSortAscending, Sorting from smallest to largest',
            'sort-descending'              => 'IconSortDescending, Sorting from largest to smallest',
            'sort-alphabetical-ascending'  => 'IconSortAlphabeticalAscending, Alphabetical sorting from A to Z',
            'sort-alphabetical-descending' => 'IconSortAlphabeticalDescending, Alphabetical sorting from Z to A',
            'speechbubble'                 => 'IconSpeechbubble, Chat / comment',
            'speechbubble-text'            => 'IconSpeechbubbleText, Chat / comment',
            'text-bold'                    => 'IconTextBold, Text bold',
            'text-italic'                  => 'IconTextItalic, Text italic',
            'text-tool'                    => 'IconTextTool, Enable text editing',
            'trash'                        => 'IconTrash, Trash',
            'traveler'                     => 'IconTraveler, Traveler',
            'youth'                        => 'IconYouth, Youth',
            'camera'                       => 'IconCamera, Take photo',
            'display'                      => 'IconDisplay, Display / desktop version',
            'headphones'                   => 'IconHeadphones, Human customer service',
            'microphone'                   => 'IconMicrophone, Microphone / sound recording',
            'microphone-crossed'           => 'IconMicrophoneCrossed, Microphone disabled',
            'mobile'                       => 'IconMobile, Mobile device / version',
            'playback-fastforward'         => 'IconPlaybackFastforward, Fast forward',
            'playback-next'                => 'IconPlaybackNext, Skip to next track',
            'playback-pause'               => 'IconPlaybackPause, Pause',
            'playback-play'                => 'IconPlaybackPlay, Play',
            'playback-previous'            => 'IconPlaybackPrevious, Skip to previous track',
            'playback-record'              => 'IconPlaybackRecord, Record',
            'playback-rewind'              => 'IconPlaybackRewind, Rewind',
            'playback-stop'                => 'IconPlaybackStop, Stop',
            'podcast'                      => 'IconPodcast, Podcast',
            'printer'                      => 'IconPrinter, Print',
            'videocamera'                  => 'IconVideocamera, Video',
            'videocamera-crossed'          => 'IconVideocameraCrossed, Video disabled',
            'volume-high'                  => 'IconVolumeHigh, Volume control / Volume level set to high',
            'volume-low'                   => 'IconVolumeLow, Volume level set to low',
            'volume-minus'                 => 'IconVolumeMinus, Decrease volume level',
            'volume-mute'                  => 'IconVolumeMute, Mute volume / volume level set to mute',
            'volume-plus'                  => 'IconVolumePlus, Increase volume level',
            'wifi'                         => 'IconWifi, Wifi connection',
            'wifi-crossed'                 => 'IconWifiCrossed, No wifi connection',
            'ticket'                       => 'IconTicket',
            'glyph-euro'                   => 'IconGlyphEuro',
            'glyph-at'                     => 'IconGlyphAt, E-mail, mention user',
            'cake'                         => 'IconCake, Date of birth',
            'coffee-cup-saucer'            => 'IconCoffeeCupSaucer, Coffee',
            'key'                          => 'IconKey, Key',
            'shopping-cart'                => 'IconShoppingCart, Shopping, retail',
            'restaurant'                   => 'IconRestaurant, Restaurant',
            'vaccine'                      => 'IconVaccine, Vaccine',
            'virus'                        => 'IconVirus, Virus',
            'discord'                      => 'IconDiscord',
            'facebook'                     => 'IconFacebook',
            'google'                       => 'IconGoogle, Google account login',
            'instagram'                    => 'IconInstagram',
            'linkedin'                     => 'IconLinkedin',
            'rss'                          => 'IconRss',
            'snapchat'                     => 'IconSnapchat',
            'tiktok'                       => 'IconTiktok',
            'twitch'                       => 'IconTwitch',
            'twitter'                      => 'IconTwitter',
            'vimeo'                        => 'IconVimeo',
            'whatsapp'                     => 'IconWhatsApp',
            'yle'                          => 'IconYle, Yle-tunnus account',
            'youtube'                      => 'IconYoutube',
        ];

        // Modify values by creating preview icon based on the key value.
        // Helps user to pickup right icon.
        array_walk( $icons, function ( &$item, $key ) {
            $item = sprintf(
                '<div class="icon-select__container">
                    <img class="icon-select__image" src="%s">
                    <span class="icon-select__title">%s</span>
                </div>',
                \get_stylesheet_directory_uri() . "/assets/icons/hds-icon-kit/{$key}.svg",
                $item,
            );
        });

        return $icons;

    }
}
