<?php
if (!defined('ABSPATH')) exit;

class PS_Ticket_Mail_Preview {

    private static ?self $instance = null;

    public static function instance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('ps_mail_preview', [$this, 'render_shortcode']);
        add_action('wp_ajax_ps_ticket_mail_preview', [$this, 'ajax_preview']);
    }

    public function render_shortcode($atts): string {
        if (!current_user_can('manage_options')) {
            return '<p style="color:#ed5e5e;">Nur für Administratoren sichtbar.</p>';
        }

        $atts = shortcode_atts(['type' => 'ticket'], $atts);
        return $this->generate_preview($atts['type']);
    }

    public function ajax_preview(): void {
        if (!current_user_can('manage_options')) wp_die('Kein Zugriff.');
        check_ajax_referer('ps_mail_preview', '_wpnonce');

        $type = sanitize_text_field($_GET['type'] ?? 'ticket');
        echo $this->generate_preview($type);
        wp_die();
    }

    private function generate_preview(string $type): string {
        $sender_name = PS_Ticket_Settings::get('ps_ticket_sender_name', get_option('blogname', 'Pixelschmiede'));
        $logo_url    = PS_Ticket_Settings::get('ps_ticket_logo_url', '');
        $company     = PS_Ticket_Settings::get('ps_ticket_company', '');
        $phone       = PS_Ticket_Settings::get('ps_ticket_phone', '+49 123 456789');
        $address     = PS_Ticket_Settings::get('ps_ticket_address', '');
        $website     = PS_Ticket_Settings::get('ps_ticket_website', home_url());

        if ($type === 'confirmation') {
            $template_text = PS_Ticket_Settings::get('ps_ticket_confirm_text', '');
            $placeholders = [
                '{name}'        => 'Max Mustermann',
                '{email}'       => 'max@beispiel.de',
                '{subject}'     => 'Login funktioniert nicht',
                '{ticket_id}'   => 'PS-DEMO1234',
                '{sender_name}' => $sender_name,
                '{company}'     => $company,
                '{phone}'       => $phone,
                '{address}'     => $address,
                '{website}'     => $website,
            ];
            $confirm_text = str_replace(array_keys($placeholders), array_values($placeholders), $template_text);

            $vars = [
                'ticket_id'    => 'PS-DEMO1234',
                'name'         => 'Max Mustermann',
                'subject'      => 'Login funktioniert nicht',
                'logo_url'     => $logo_url,
                'sender_name'  => $sender_name,
                'company'      => $company,
                'phone'        => $phone,
                'address'      => $address,
                'website'      => $website,
                'confirm_text' => $confirm_text,
            ];

            return $this->render_template('mail-confirmation', $vars);
        }

        $vars = [
            'ticket_id'        => 'PS-DEMO1234',
            'name'             => 'Max Mustermann',
            'email'            => 'max@beispiel.de',
            'phone'            => '+49 170 1234567',
            'company'          => 'Musterfirma GmbH',
            'subject'          => 'Login funktioniert nicht',
            'category_label'   => 'Fehler / Bug',
            'message'          => "Hallo,\n\nich kann mich seit heute Morgen nicht mehr in meinem Account einloggen. Nach Eingabe meiner Zugangsdaten erscheint nur eine weiße Seite.\n\nBetriebssystem: macOS 15.1\nBrowser: Safari 18.2\n\nKönnten Sie sich das bitte anschauen?\n\nVielen Dank!",
            'date'             => wp_date('d.m.Y H:i:s'),
            'ip'               => '192.168.1.42',
            'referer'          => home_url('/support/'),
            'attachment_count' => 2,
            'logo_url'         => $logo_url,
            'sender_name'      => $sender_name,
        ];

        return $this->render_template('mail-ticket', $vars);
    }

    private function render_template(string $name, array $vars): string {
        extract($vars, EXTR_SKIP);
        ob_start();
        include PS_TICKET_PLUGIN_DIR . 'templates/' . $name . '.php';
        return ob_get_clean();
    }
}
