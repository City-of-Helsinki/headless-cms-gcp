<?php
/**
 * Plugin Name:  PHPMailer
 * Description:  Configure PHPMailer to use SMTP server configured with environment variables.
 * Author:       Frantic
 * Author URI:   https://www.frantic.com/
 * License:      MIT License
 */

use function Env\env;

function run_frc_phpmailer_sendgrid() {

    add_action('phpmailer_init', function ($phpmailer) {
        $phpmailer->Host = env('SMTP_HOST');
        $phpmailer->Port = env('SMTP_PORT');
        $phpmailer->Username = env('SMTP_USER');
        $phpmailer->Password = env('SMTP_PASSWORD');
        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->IsSMTP();
    });

    add_filter('wp_mail_from', function () {
        return env('SMTP_FROM');
    }, 100);

    add_action('wp_mail_failed', function (\WP_Error $error) {
        error_log('Error when sending mail: ' . implode(' - ', $error->get_error_messages()));
    }, 10, 1);
}

run_frc_phpmailer_sendgrid();