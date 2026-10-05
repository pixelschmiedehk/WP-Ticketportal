<?php
if (!defined('ABSPATH')) exit;

class PS_Ticket_Handler {

    private static ?self $instance = null;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'text/plain',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public static function instance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_ps_ticket_submit', [$this, 'handle']);
        add_action('wp_ajax_nopriv_ps_ticket_submit', [$this, 'handle']);
        // Frisches Sicherheits-Token beim Absenden: Seiten-Caches (WP Rocket) würden sonst ein abgelaufenes ausliefern.
        add_action('wp_ajax_ps_ticket_nonce', [$this, 'nonce']);
        add_action('wp_ajax_nopriv_ps_ticket_nonce', [$this, 'nonce']);
    }

    public function nonce(): void {
        nocache_headers();
        wp_send_json_success(['nonce' => wp_create_nonce('ps_ticket_nonce')]);
    }

    public function handle(): void {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ps_ticket_nonce')) {
            wp_send_json_error('Sicherheitsprüfung fehlgeschlagen.');
        }

        $this->check_rate_limit();

        $name     = sanitize_text_field($_POST['name'] ?? '');
        $email    = sanitize_email($_POST['email'] ?? '');
        $phone    = sanitize_text_field($_POST['phone'] ?? '');
        $company  = sanitize_text_field($_POST['company'] ?? '');
        $subject  = sanitize_text_field($_POST['subject'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $message  = sanitize_textarea_field($_POST['message'] ?? '');

        if (!$name || !$email || !$subject || !$category || !$message) {
            wp_send_json_error('Bitte füll alle Pflichtfelder aus.');
        }

        if (!is_email($email)) {
            wp_send_json_error('Ungültige E-Mail-Adresse.');
        }

        $categories = PS_Ticket_Settings::get_categories();
        if (!isset($categories[$category])) {
            wp_send_json_error('Ungültige Kategorie.');
        }

        $ticket_id = 'PS-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));

        $attachments = $this->process_uploads($ticket_id);

        $sent = $this->send_ticket_mail($ticket_id, compact(
            'name', 'email', 'phone', 'company', 'subject', 'category', 'message'
        ), $categories, $attachments);

        if ($sent && PS_Ticket_Settings::get('ps_ticket_confirm_active') === '1') {
            $this->send_confirmation($ticket_id, compact('name', 'email', 'subject'));
        }

        if ($sent) {
            wp_send_json_success(['ticket_id' => $ticket_id]);
        } else {
            wp_send_json_error('E-Mail konnte nicht gesendet werden. Bitte versuch es später noch einmal.');
        }
    }

    private function check_rate_limit(): void {
        $limit = (int) PS_Ticket_Settings::get('ps_ticket_rate_limit', 5);
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $key = 'ps_ticket_rate_' . md5($ip);
        $count = (int) get_transient($key);

        if ($count >= $limit) {
            wp_send_json_error('Zu viele Anfragen. Bitte versuch es später noch einmal.');
        }

        set_transient($key, $count + 1, 300);
    }

    private function process_uploads(string $ticket_id): array {
        $attachments = [];
        if (empty($_FILES['files']) || empty($_FILES['files']['name'][0])) {
            return $attachments;
        }

        $max_files = (int) PS_Ticket_Settings::get('ps_ticket_max_files', 5);
        $max_size  = (int) PS_Ticket_Settings::get('ps_ticket_max_file_size', 10) * 1024 * 1024;
        $files     = $_FILES['files'];
        $count     = min(count($files['name']), $max_files);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($files['size'][$i] > $max_size) continue;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $files['tmp_name'][$i]);
            finfo_close($finfo);

            if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) continue;

            $safe_name  = sanitize_file_name($files['name'][$i]);
            $upload_dir = wp_upload_dir();
            $ticket_dir = $upload_dir['basedir'] . '/ps-tickets/' . $ticket_id;

            if (!file_exists($ticket_dir)) {
                wp_mkdir_p($ticket_dir);
            }

            $dest = $ticket_dir . '/' . $safe_name;
            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $attachments[] = $dest;
            }
        }

        return $attachments;
    }

    private function render_template(string $template_name, array $vars): string {
        extract($vars, EXTR_SKIP);
        ob_start();
        include PS_TICKET_PLUGIN_DIR . 'templates/' . $template_name . '.php';
        return ob_get_clean();
    }

    private function send_ticket_mail(string $ticket_id, array $data, array $categories, array $attachments): bool {
        $recipient   = PS_Ticket_Settings::get('ps_ticket_recipient', get_option('admin_email'));
        $sender_name = PS_Ticket_Settings::get('ps_ticket_sender_name', get_option('blogname'));
        $logo_url    = PS_Ticket_Settings::get('ps_ticket_logo_url', '');
        $ip          = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        $category_label = $categories[$data['category']] ?? $data['category'];

        $body = $this->render_template('mail-ticket', [
            'ticket_id'        => $ticket_id,
            'name'             => $data['name'],
            'email'            => $data['email'],
            'phone'            => $data['phone'],
            'company'          => $data['company'],
            'subject'          => $data['subject'],
            'category_label'   => $category_label,
            'message'          => $data['message'],
            'date'             => wp_date('d.m.Y H:i:s'),
            'ip'               => $ip,
            'referer'          => esc_url(wp_get_referer() ?: home_url()),
            'attachment_count' => count($attachments),
            'logo_url'         => $logo_url,
            'sender_name'      => $sender_name,
        ]);

        $headers = [
            'From: ' . $sender_name . ' <' . get_option('admin_email') . '>',
            'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>',
            'Content-Type: text/html; charset=UTF-8',
        ];

        // Für die Agentur-Zentrale: die Angaben als unterschriebene Kennung. Die Zentrale legt nur bei
        // gültiger Unterschrift automatisch ein Ticket an – eine gefälschte Mail bleibt eine Nachricht.
        $secret = (string) PS_Ticket_Settings::get('ps_ticket_zentrale_secret', '');

        if ($secret !== '') {
            $payload = base64_encode((string) wp_json_encode([
                'v'        => 1,
                'id'       => $ticket_id,
                'name'     => mb_substr($data['name'], 0, 120),
                'email'    => $data['email'],
                'phone'    => mb_substr($data['phone'], 0, 60),
                'company'  => mb_substr($data['company'], 0, 160),
                'category' => mb_substr($category_label, 0, 80),
                'subject'  => mb_substr($data['subject'], 0, 200),
                'site'     => home_url(),
            ]));
            $signature = hash_hmac('sha256', $payload, $secret);
            $headers[] = 'X-PS-Ticket: ' . $payload;
            $headers[] = 'X-PS-Ticket-Signature: ' . $signature;
            // Zusätzlich unsichtbar im Text: Versanddienste (z. B. „Email Deliverability“ von Elementor)
            // geben eigene Kopfzeilen nicht weiter – der Text kommt an.
            $marker = '<!--ps-ticket:' . $payload . '.' . $signature . '-->';
            $body = stripos($body, '</body>') !== false ? preg_replace('~</body>~i', $marker . '</body>', $body, 1) : $body . $marker;
        }

        return wp_mail(
            $recipient,
            "[Ticket {$ticket_id}] {$data['subject']}",
            $body,
            $headers,
            $attachments
        );
    }

    private function send_confirmation(string $ticket_id, array $data): void {
        $template_text = PS_Ticket_Settings::get('ps_ticket_confirm_text', '');
        if (!$template_text) return;

        $sender_name = PS_Ticket_Settings::get('ps_ticket_sender_name', get_option('blogname'));
        $company     = PS_Ticket_Settings::get('ps_ticket_company', '');
        $phone       = PS_Ticket_Settings::get('ps_ticket_phone', '');
        $address     = PS_Ticket_Settings::get('ps_ticket_address', '');
        $website     = PS_Ticket_Settings::get('ps_ticket_website', '');
        $logo_url    = PS_Ticket_Settings::get('ps_ticket_logo_url', '');

        $placeholders = [
            '{name}'        => $data['name'],
            '{email}'       => $data['email'],
            '{subject}'     => $data['subject'],
            '{ticket_id}'   => $ticket_id,
            '{sender_name}' => $sender_name,
            '{company}'     => $company,
            '{phone}'       => $phone,
            '{address}'     => $address,
            '{website}'     => $website,
        ];

        $confirm_text = str_replace(array_keys($placeholders), array_values($placeholders), $template_text);

        $body = $this->render_template('mail-confirmation', [
            'ticket_id'    => $ticket_id,
            'name'         => $data['name'],
            'subject'      => $data['subject'],
            'logo_url'     => $logo_url,
            'sender_name'  => $sender_name,
            'company'      => $company,
            'phone'        => $phone,
            'address'      => $address,
            'website'      => $website,
            'confirm_text' => $confirm_text,
        ]);

        $headers = [
            'From: ' . $sender_name . ' <' . get_option('admin_email') . '>',
            'Content-Type: text/html; charset=UTF-8',
        ];

        wp_mail($data['email'], "Deine Anfrage: {$ticket_id}", $body, $headers);
    }
}
