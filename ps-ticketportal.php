<?php
/**
 * Plugin Name: Pixelschmiede Ticketportal
 * Description: Support-Ticket-Formular für Elementor — sendet Tickets per wp_mail. Einstellungen unter Einstellungen → Ticketportal.
 * Version: 1.0.0
 * Author: Pixelschmiede
 * Author URI: https://pixelschmiede.de
 * Text Domain: ps-ticketportal
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

define('PS_TICKET_VERSION', '1.0.0');
define('PS_TICKET_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PS_TICKET_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PS_TICKET_PLUGIN_DIR . 'includes/class-ps-ticket-settings.php';
require_once PS_TICKET_PLUGIN_DIR . 'includes/class-ps-ticket-handler.php';
require_once PS_TICKET_PLUGIN_DIR . 'includes/class-ps-ticket-frontend.php';
require_once PS_TICKET_PLUGIN_DIR . 'includes/class-ps-ticket-mail-preview.php';

// ─── Initialisierung ─────────────────────────────────────────
add_action('init', function () {
    PS_Ticket_Settings::instance();
    PS_Ticket_Handler::instance();
    PS_Ticket_Frontend::instance();
    PS_Ticket_Mail_Preview::instance();
});

// ─── Standardwerte bei Aktivierung setzen ────────────────────
register_activation_hook(__FILE__, function () {
    $defaults = [
        'ps_ticket_recipient'      => get_option('admin_email'),
        'ps_ticket_sender_name'    => get_option('blogname', 'Pixelschmiede'),
        'ps_ticket_company'        => '',
        'ps_ticket_phone'          => '',
        'ps_ticket_address'        => '',
        'ps_ticket_website'        => home_url(),
        'ps_ticket_logo_url'       => '',
        'ps_ticket_confirm_active' => '1',
        'ps_ticket_confirm_text'   => "Hallo {name},\n\nvielen Dank für Ihre Anfrage. Wir haben Ihr Ticket erhalten und melden uns schnellstmöglich.\n\nIhre Ticket-ID: {ticket_id}\nBetreff: {subject}\n\nMit freundlichen Grüßen\n{sender_name}",
        'ps_ticket_categories'     => "bug|Fehler / Bug\nfeature|Feature-Wunsch\nsupport|Allgemeiner Support\nbilling|Abrechnung / Vertrag\nother|Sonstiges",
        'ps_ticket_privacy_url'    => '/datenschutz/',
        'ps_ticket_max_files'      => 5,
        'ps_ticket_max_file_size'  => 10,
        'ps_ticket_rate_limit'     => 5,
    ];

    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
});
