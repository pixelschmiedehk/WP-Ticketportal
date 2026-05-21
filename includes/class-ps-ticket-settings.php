<?php
if (!defined('ABSPATH')) exit;

class PS_Ticket_Settings {

    private static ?self $instance = null;

    public static function instance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media']);
    }

    public function enqueue_media(string $hook): void {
        if ($hook !== 'settings_page_ps-ticketportal') return;
        wp_enqueue_media();
    }

    public function add_menu(): void {
        add_options_page(
            'Ticketportal',
            'Ticketportal',
            'manage_options',
            'ps-ticketportal',
            [$this, 'render_page']
        );
    }

    public function register_settings(): void {
        $fields = $this->get_fields();
        foreach ($fields as $section) {
            foreach ($section['fields'] as $field) {
                register_setting('ps_ticket_settings', $field['id'], [
                    'sanitize_callback' => $field['sanitize'] ?? 'sanitize_text_field',
                ]);
            }
        }
    }

    private function get_fields(): array {
        return [
            [
                'title' => 'Empfänger & Absender',
                'id'    => 'sender',
                'fields' => [
                    [
                        'id'          => 'ps_ticket_recipient',
                        'label'       => 'Empfänger E-Mail',
                        'type'        => 'email',
                        'description' => 'An diese Adresse werden Tickets gesendet.',
                        'placeholder' => 'ticket@pixelschmiede.de',
                    ],
                    [
                        'id'          => 'ps_ticket_sender_name',
                        'label'       => 'Absender-Name',
                        'type'        => 'text',
                        'description' => 'Wird als Absender in E-Mails angezeigt.',
                        'placeholder' => 'Pixelschmiede',
                    ],
                    [
                        'id'          => 'ps_ticket_logo_url',
                        'label'       => 'Logo-URL',
                        'type'        => 'image',
                        'description' => 'Logo für die E-Mail-Templates (empfohlen: PNG, max. 180px breit, transparenter Hintergrund).',
                    ],
                    [
                        'id'          => 'ps_ticket_company',
                        'label'       => 'Firmenname',
                        'type'        => 'text',
                        'description' => 'Für die Bestätigungsmail-Signatur (optional).',
                        'placeholder' => 'Pixelschmiede GmbH',
                    ],
                    [
                        'id'          => 'ps_ticket_phone',
                        'label'       => 'Telefon',
                        'type'        => 'text',
                        'description' => 'Wird in der Mail-Signatur angezeigt (optional).',
                        'placeholder' => '+49 123 456789',
                    ],
                    [
                        'id'          => 'ps_ticket_address',
                        'label'       => 'Adresse',
                        'type'        => 'text',
                        'description' => 'Für die Signatur (optional).',
                        'placeholder' => 'Musterstr. 1, 12345 Musterstadt',
                    ],
                    [
                        'id'          => 'ps_ticket_website',
                        'label'       => 'Website',
                        'type'        => 'url',
                        'description' => 'Für die Signatur (optional).',
                        'placeholder' => 'https://pixelschmiede.de',
                    ],
                ],
            ],
            [
                'title' => 'Bestätigungsmail',
                'id'    => 'confirm',
                'fields' => [
                    [
                        'id'          => 'ps_ticket_confirm_active',
                        'label'       => 'Bestätigungsmail senden',
                        'type'        => 'checkbox',
                        'description' => 'Sendet dem Absender automatisch eine Eingangsbestätigung.',
                    ],
                    [
                        'id'          => 'ps_ticket_confirm_text',
                        'label'       => 'Text der Bestätigungsmail',
                        'type'        => 'textarea',
                        'description' => 'Platzhalter: {name}, {email}, {subject}, {ticket_id}, {sender_name}, {company}, {phone}, {address}, {website}',
                        'sanitize'    => 'sanitize_textarea_field',
                    ],
                ],
            ],
            [
                'title' => 'Formular',
                'id'    => 'form',
                'fields' => [
                    [
                        'id'          => 'ps_ticket_categories',
                        'label'       => 'Kategorien',
                        'type'        => 'textarea',
                        'description' => 'Eine pro Zeile, Format: slug|Anzeigename',
                        'sanitize'    => 'sanitize_textarea_field',
                        'rows'        => 6,
                    ],
                    [
                        'id'          => 'ps_ticket_privacy_url',
                        'label'       => 'Datenschutz-URL',
                        'type'        => 'text',
                        'description' => 'Link zur Datenschutzerklärung.',
                        'placeholder' => '/datenschutz/',
                    ],
                ],
            ],
            [
                'title' => 'Limits & Sicherheit',
                'id'    => 'limits',
                'fields' => [
                    [
                        'id'          => 'ps_ticket_max_files',
                        'label'       => 'Max. Dateien',
                        'type'        => 'number',
                        'description' => 'Maximale Anzahl Datei-Anhänge pro Ticket.',
                        'placeholder' => '5',
                        'sanitize'    => 'absint',
                    ],
                    [
                        'id'          => 'ps_ticket_max_file_size',
                        'label'       => 'Max. Dateigröße (MB)',
                        'type'        => 'number',
                        'description' => 'Maximale Größe pro Datei in Megabyte.',
                        'placeholder' => '10',
                        'sanitize'    => 'absint',
                    ],
                    [
                        'id'          => 'ps_ticket_rate_limit',
                        'label'       => 'Rate-Limit (pro 5 Min.)',
                        'type'        => 'number',
                        'description' => 'Maximale Ticket-Einsendungen pro IP in 5 Minuten.',
                        'placeholder' => '5',
                        'sanitize'    => 'absint',
                    ],
                ],
            ],
        ];
    }

    public function render_page(): void {
        if (!current_user_can('manage_options')) return;
        $fields = $this->get_fields();
        ?>
        <div class="wrap" style="max-width: 820px;">
            <h1 style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#ea7b3b;border-radius:4px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </span>
                Pixelschmiede Ticketportal
            </h1>
            <p class="description" style="margin-bottom: 24px; font-size: 13px; color: #646970;">
                Einstellungen für das Support-Ticket-Formular. Binde das Formular per Shortcode <code>[ps_ticketportal]</code> oder Elementor HTML-Widget ein.
                <?php if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php') || is_plugin_active('wp-mail-smtp-pro/wp_mail_smtp.php')): ?>
                    <br><span style="color: #00a32a;">&#10003; WP Mail SMTP erkannt — E-Mails werden über deine SMTP-Konfiguration versendet.</span>
                <?php else: ?>
                    <br><span style="color: #d63638;">&#9888; WP Mail SMTP nicht aktiv — E-Mails werden über PHP mail() versendet. Für zuverlässigen Versand empfehlen wir WP Mail SMTP.</span>
                <?php endif; ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields('ps_ticket_settings'); ?>

                <?php foreach ($fields as $section): ?>
                    <div class="postbox" style="margin-bottom: 20px;">
                        <div class="postbox-header" style="padding: 12px 16px; border-bottom: 1px solid #c3c4c7;">
                            <h2 style="margin: 0; font-size: 14px;"><?php echo esc_html($section['title']); ?></h2>
                        </div>
                        <div class="inside" style="padding: 16px;">
                            <table class="form-table" role="presentation" style="margin: 0;">
                                <?php foreach ($section['fields'] as $field): ?>
                                    <tr>
                                        <th scope="row" style="padding: 12px 10px 12px 0; width: 200px;">
                                            <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
                                        </th>
                                        <td style="padding: 12px 0;">
                                            <?php $this->render_field($field); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php submit_button('Einstellungen speichern'); ?>
            </form>

            <!-- Shortcodes -->
            <div class="postbox" style="margin-top: 10px;">
                <div class="postbox-header" style="padding: 12px 16px; border-bottom: 1px solid #c3c4c7;">
                    <h2 style="margin: 0; font-size: 14px;">Shortcodes & Einbindung</h2>
                </div>
                <div class="inside" style="padding: 16px;">
                    <table class="widefat striped" style="border: none;">
                        <tbody>
                            <tr>
                                <td style="width: 280px;"><code>[ps_ticketportal]</code></td>
                                <td>Ticket-Formular einbinden (Elementor, Block-Editor, PHP)</td>
                            </tr>
                            <tr>
                                <td><code>[ps_mail_preview type="ticket"]</code></td>
                                <td>Vorschau der Ticket-Benachrichtigung (Admin-Mail)</td>
                            </tr>
                            <tr>
                                <td><code>[ps_mail_preview type="confirmation"]</code></td>
                                <td>Vorschau der Bestätigungsmail (Kunden-Mail)</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="description" style="margin-top: 12px;">
                        <strong>Elementor:</strong> Shortcode-Widget oder HTML-Widget verwenden.<br>
                        <strong>PHP:</strong> <code>&lt;?php echo do_shortcode('[ps_ticketportal]'); ?&gt;</code>
                    </p>
                </div>
            </div>

            <!-- Mail-Vorschau direkt im Admin -->
            <div class="postbox" style="margin-top: 10px;">
                <div class="postbox-header" style="padding: 12px 16px; border-bottom: 1px solid #c3c4c7;">
                    <h2 style="margin: 0; font-size: 14px;">Mail-Vorschau</h2>
                </div>
                <div class="inside" style="padding: 0;">
                    <div style="display: flex; gap: 0; border-bottom: 1px solid #c3c4c7;">
                        <button type="button" class="ps-preview-tab" data-target="ticket" style="flex:1; padding: 10px; border: none; background: #f0f0f1; cursor: pointer; font-weight: 600; font-size: 13px; border-right: 1px solid #c3c4c7;">
                            Ticket-Mail (Admin)
                        </button>
                        <button type="button" class="ps-preview-tab" data-target="confirm" style="flex:1; padding: 10px; border: none; background: #fff; cursor: pointer; font-size: 13px;">
                            Bestätigungsmail (Kunde)
                        </button>
                    </div>
                    <div id="ps-preview-ticket" style="padding: 0;">
                        <iframe id="ps-iframe-ticket" style="width: 100%; height: 700px; border: none;"
                                src="<?php echo esc_url(admin_url('admin-ajax.php?action=ps_ticket_mail_preview&type=ticket&_wpnonce=' . wp_create_nonce('ps_mail_preview'))); ?>">
                        </iframe>
                    </div>
                    <div id="ps-preview-confirm" style="padding: 0; display: none;">
                        <iframe id="ps-iframe-confirm" style="width: 100%; height: 700px; border: none;"
                                src="<?php echo esc_url(admin_url('admin-ajax.php?action=ps_ticket_mail_preview&type=confirmation&_wpnonce=' . wp_create_nonce('ps_mail_preview'))); ?>">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.querySelectorAll('.ps-preview-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = this.dataset.target;
                document.querySelectorAll('.ps-preview-tab').forEach(function(b) {
                    b.style.background = '#fff';
                    b.style.fontWeight = 'normal';
                });
                this.style.background = '#f0f0f1';
                this.style.fontWeight = '600';
                document.getElementById('ps-preview-ticket').style.display = target === 'ticket' ? 'block' : 'none';
                document.getElementById('ps-preview-confirm').style.display = target === 'confirm' ? 'block' : 'none';
            });
        });
        </script>
        <?php
    }

    private function render_field(array $field): void {
        $value = get_option($field['id'], '');

        switch ($field['type']) {
            case 'textarea':
                $rows = $field['rows'] ?? 8;
                printf(
                    '<textarea id="%s" name="%s" class="large-text" rows="%d" placeholder="%s">%s</textarea>',
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    $rows,
                    esc_attr($field['placeholder'] ?? ''),
                    esc_textarea($value)
                );
                break;

            case 'checkbox':
                printf(
                    '<label><input type="checkbox" id="%s" name="%s" value="1" %s> Aktiviert</label>',
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    checked($value, '1', false)
                );
                break;

            case 'number':
                printf(
                    '<input type="number" id="%s" name="%s" value="%s" class="small-text" min="1" placeholder="%s">',
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    esc_attr($value),
                    esc_attr($field['placeholder'] ?? '')
                );
                break;

            case 'image':
                $this->render_image_field($field['id'], $value, $field['description'] ?? '');
                break;

            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" placeholder="%s">',
                    esc_attr($field['type']),
                    esc_attr($field['id']),
                    esc_attr($field['id']),
                    esc_attr($value),
                    esc_attr($field['placeholder'] ?? '')
                );
        }

        if ($field['type'] !== 'image' && !empty($field['description'])) {
            printf('<p class="description">%s</p>', wp_kses_post($field['description']));
        }
    }

    private function render_image_field(string $id, string $value, string $description): void {
        ?>
        <div id="<?php echo esc_attr($id); ?>_wrapper">
            <div id="<?php echo esc_attr($id); ?>_preview" style="margin-bottom: 10px; <?php echo $value ? '' : 'display:none;'; ?>">
                <img src="<?php echo esc_url($value); ?>" style="max-width: 200px; height: auto; background: #1d2327; padding: 12px; border-radius: 4px;">
            </div>
            <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>">
            <button type="button" class="button" id="<?php echo esc_attr($id); ?>_btn">Logo wählen</button>
            <button type="button" class="button" id="<?php echo esc_attr($id); ?>_remove" style="color: #b32d2e; <?php echo $value ? '' : 'display:none;'; ?>">Entfernen</button>
            <?php if ($description): ?>
                <p class="description"><?php echo wp_kses_post($description); ?></p>
            <?php endif; ?>
        </div>
        <script>
        (function() {
            var btn = document.getElementById('<?php echo esc_js($id); ?>_btn');
            var remove = document.getElementById('<?php echo esc_js($id); ?>_remove');
            var input = document.getElementById('<?php echo esc_js($id); ?>');
            var preview = document.getElementById('<?php echo esc_js($id); ?>_preview');

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var frame = wp.media({ title: 'Logo wählen', multiple: false, library: { type: 'image' } });
                frame.on('select', function() {
                    var url = frame.state().get('selection').first().toJSON().url;
                    input.value = url;
                    preview.querySelector('img').src = url;
                    preview.style.display = '';
                    remove.style.display = '';
                });
                frame.open();
            });

            remove.addEventListener('click', function(e) {
                e.preventDefault();
                input.value = '';
                preview.style.display = 'none';
                this.style.display = 'none';
            });
        })();
        </script>
        <?php
    }

    public static function get(string $key, $default = '') {
        return get_option($key, $default);
    }

    public static function get_categories(): array {
        $raw = get_option('ps_ticket_categories', '');
        $categories = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (!$line || strpos($line, '|') === false) continue;
            [$slug, $label] = explode('|', $line, 2);
            $categories[trim($slug)] = trim($label);
        }
        return $categories;
    }
}
