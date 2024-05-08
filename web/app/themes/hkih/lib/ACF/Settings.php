<?php
/**
 * Site settings fields for PostType\Settings.
 */

namespace Geniem\Theme\ACF;

use \Geniem\ACF\Group;
use \Geniem\ACF\RuleGroup;
use \Geniem\Theme\Logger;
use \Geniem\Theme\PostType;
use \Geniem\Theme\ACF\Fields\Settings as SettingsTabs;

/**
 * Class Settings
 *
 * @package Geniem\Theme\ACF
 */
class Settings {

    /**
     * Settings constructor.
     */
    public function __construct() {
        $this->fields();
    }

    /**
     * Register fields
     */
    public function fields() : void {
        $group_title = _x( 'Site settings', 'theme ACF', 'hkih' );

        try {
            $rule_group = ( new RuleGroup() )
                ->add_rule( 'post_type', '==', PostType\Settings::SLUG );

            $field_group = ( new Group( $group_title ) )
                ->set_key( 'fg_site_settings' )
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' )
                ->set_hidden_elements(
                    [
                        'discussion',
                        'comments',
                        'format',
                        'send-trackbacks',
                    ]
                );

            $default_fields = [
                new SettingsTabs\SiteIdentitySettingsTab( '', $field_group->get_key() ),
                new SettingsTabs\NotificationSettingsTab( '', $field_group->get_key() ),
                new SettingsTabs\DefaultImagesSettingsTab( '', $field_group->get_key() ),
                new SettingsTabs\CustomAdminStylesTab( '', $field_group->get_key() ),
                new SettingsTabs\BreadCrumbSettingsTab( '', $field_group->get_key() ),
            ];

            $field_group->set_fields(
                apply_filters( 'hkih_theme_settings', $default_fields, $field_group->get_key() )
            );

            $field_group->register();
        }
        catch ( \Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }
    }
}

( new Settings() );
