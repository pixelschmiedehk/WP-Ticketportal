<?php
if (!defined('ABSPATH')) exit;

class PS_Ticket_Frontend {

    private static ?self $instance = null;

    public static function instance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('ps_ticketportal', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        wp_register_style(
            'ps-ticketportal',
            PS_TICKET_PLUGIN_URL . 'assets/css/ticketportal.css',
            [],
            PS_TICKET_VERSION
        );

        wp_register_script(
            'ps-ticketportal',
            PS_TICKET_PLUGIN_URL . 'assets/js/ticketportal.js',
            [],
            PS_TICKET_VERSION,
            true
        );
    }

    public function render_shortcode($atts): string {
        wp_enqueue_style('ps-ticketportal');
        wp_enqueue_script('ps-ticketportal');
        wp_localize_script('ps-ticketportal', 'psTicketVars', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ps_ticket_nonce'),
        ]);

        $categories  = PS_Ticket_Settings::get_categories();
        $privacy_url = PS_Ticket_Settings::get('ps_ticket_privacy_url', '/datenschutz/');
        $max_files   = (int) PS_Ticket_Settings::get('ps_ticket_max_files', 5);
        $max_size    = (int) PS_Ticket_Settings::get('ps_ticket_max_file_size', 10);

        ob_start();
        ?>
        <div class="ps-ticket" id="psTicket">
            <div class="ps-ticket__header">
                <h2 class="ps-ticket__title">Support-Ticket erstellen</h2>
                <p class="ps-ticket__subtitle">Wir melden uns schnellstmöglich</p>
            </div>

            <form id="psTicketForm" novalidate
                  data-max-files="<?php echo esc_attr($max_files); ?>"
                  data-max-size="<?php echo esc_attr($max_size); ?>">

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-name">Name <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="text" id="ps-name" name="name" placeholder="Max Mustermann" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-email">E-Mail <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="email" id="ps-email" name="email" placeholder="max@beispiel.de" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                </div>

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-phone">Telefon</label>
                        <input class="ps-ticket__input" type="tel" id="ps-phone" name="phone" placeholder="+49 123 456789">
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-company">Firma</label>
                        <input class="ps-ticket__input" type="text" id="ps-company" name="company" placeholder="Firma GmbH">
                    </div>
                </div>

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-subject">Betreff <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="text" id="ps-subject" name="subject" placeholder="Kurze Zusammenfassung" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-category">Kategorie <span class="ps-required">*</span></label>
                        <select class="ps-ticket__select" id="ps-category" name="category" required>
                            <option value="" disabled selected>Bitte wählen...</option>
                            <?php foreach ($categories as $slug => $label): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                </div>

                <div class="ps-ticket__row ps-ticket__row--full">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-message">Beschreibung <span class="ps-required">*</span></label>
                        <textarea class="ps-ticket__textarea" id="ps-message" name="message" placeholder="Beschreiben Sie Ihr Anliegen so detailliert wie möglich..." required></textarea>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                </div>

                <div class="ps-ticket__row ps-ticket__row--full" style="margin-bottom: 0;">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label">Datei-Anhang</label>
                        <div class="ps-ticket__upload-zone" id="psUploadZone">
                            <input type="file" id="ps-file" name="files[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip,.txt,.csv,.xlsx">
                            <svg class="ps-ticket__upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span class="ps-ticket__upload-text">Dateien hier ablegen oder <strong>durchsuchen</strong></span>
                            <br><span class="ps-ticket__upload-text" style="font-size:11px; margin-top: 4px; display: inline-block;">Max. <?php echo esc_html($max_size); ?> MB pro Datei &middot; JPG, PNG, PDF, DOC, ZIP</span>
                        </div>
                        <div class="ps-ticket__file-list" id="psFileList"></div>
                    </div>
                </div>

                <div class="ps-ticket__privacy">
                    <input type="checkbox" id="ps-privacy" name="privacy" required>
                    <label for="ps-privacy">Ich stimme der Verarbeitung meiner Daten gemäß der <a href="<?php echo esc_url($privacy_url); ?>" target="_blank">Datenschutzerklärung</a> zu. <span class="ps-required" style="color: var(--ps-orange);">*</span></label>
                </div>

                <button type="submit" class="ps-ticket__submit" id="psSubmitBtn">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Ticket absenden
                </button>

                <div class="ps-ticket__status" id="psStatus"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
