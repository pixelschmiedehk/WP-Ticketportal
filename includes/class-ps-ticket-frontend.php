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

    /**
     * Shortcode [ps_ticketportal] – Attribute (alle optional):
     * stil="karte" (eigene helle Karte, Standard) oder stil="eingebettet" (ohne Karte, für eine Elementor-Karte),
     * kopf="nein" blendet die Überschrift aus, titel/untertitel/zeile ersetzen die Texte im Kopf
     * (im Titel *Sternchen* für das orange Akzentwort).
     */
    public function render_shortcode($atts): string {
        $atts = shortcode_atts([
            'stil'      => 'karte',
            'kopf'      => 'ja',
            'zeile'     => 'Support',
            'titel'     => 'Ticket *erstellen.*',
            'untertitel'=> 'Beschreiben Sie kurz, worum es geht – wir melden uns schnellstmöglich.',
        ], is_array($atts) ? $atts : [], 'ps_ticketportal');
        $embedded  = in_array(strtolower((string) $atts['stil']), ['eingebettet', 'embedded'], true);
        $show_head = !in_array(strtolower((string) $atts['kopf']), ['nein', 'no', '0', 'false'], true);
        // Akzentwort: *so* → orange Verlauf (nur dieses eine Stilmittel, sonst reiner Text).
        $title = preg_replace('/\*([^*]+)\*/', '<span>$1</span>', esc_html((string) $atts['titel']));

        // Block-Themes rendern Inhalte vor „wp_enqueue_scripts“ – dann sind Stil und Skript hier noch nicht registriert.
        if (!wp_script_is('ps-ticketportal', 'registered')) {
            $this->enqueue_assets();
        }

        wp_enqueue_style('ps-ticketportal');
        wp_enqueue_script('ps-ticketportal');

        $categories  = PS_Ticket_Settings::get_categories();
        $privacy_url = PS_Ticket_Settings::get('ps_ticket_privacy_url', '/datenschutz/');
        $max_files   = (int) PS_Ticket_Settings::get('ps_ticket_max_files', 5);
        $max_size    = (int) PS_Ticket_Settings::get('ps_ticket_max_file_size', 10);

        ob_start();
        ?>
        <div class="ps-ticket<?php echo $embedded ? ' ps-ticket--eingebettet' : ''; ?>" id="psTicket">
            <?php if ($show_head): ?>
            <div class="ps-ticket__header">
                <?php if ((string) $atts['zeile'] !== ''): ?><p class="ps-ticket__eyebrow"><?php echo esc_html($atts['zeile']); ?></p><?php endif; ?>
                <h2 class="ps-ticket__title"><?php echo $title; ?></h2>
                <?php if ((string) $atts['untertitel'] !== ''): ?><p class="ps-ticket__subtitle"><?php echo esc_html($atts['untertitel']); ?></p><?php endif; ?>
            </div>
            <?php endif; ?>

            <form id="psTicketForm" novalidate
                  data-ajax="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                  data-max-files="<?php echo esc_attr($max_files); ?>"
                  data-max-size="<?php echo esc_attr($max_size); ?>">

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-name">Name <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="text" id="ps-name" name="name" placeholder="Vor- und Nachname" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-email">E-Mail <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="email" id="ps-email" name="email" placeholder="name@firma.de" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                </div>

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-phone">Telefon</label>
                        <input class="ps-ticket__input" type="tel" id="ps-phone" name="phone" placeholder="Für Rückfragen">
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-company">Firma</label>
                        <input class="ps-ticket__input" type="text" id="ps-company" name="company" placeholder="Firmenname">
                    </div>
                </div>

                <div class="ps-ticket__row">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-subject">Betreff <span class="ps-required">*</span></label>
                        <input class="ps-ticket__input" type="text" id="ps-subject" name="subject" placeholder="Worum geht es?" required>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-category">Kategorie <span class="ps-required">*</span></label>
                        <select class="ps-ticket__select" id="ps-category" name="category" required>
                            <option value="" disabled selected>Bitte wählen …</option>
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
                        <textarea class="ps-ticket__textarea" id="ps-message" name="message" placeholder="Was ist passiert, wo (Seite oder Adresse) und seit wann? Je genauer, desto schneller können wir helfen." required></textarea>
                        <span class="ps-ticket__error-msg"></span>
                    </div>
                </div>

                <div class="ps-ticket__row ps-ticket__row--full">
                    <div class="ps-ticket__field">
                        <label class="ps-ticket__label" for="ps-file">Anhänge</label>
                        <div class="ps-ticket__upload-zone" id="psUploadZone">
                            <input type="file" id="ps-file" name="files[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip,.txt,.csv,.xlsx">
                            <svg class="ps-ticket__upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span class="ps-ticket__upload-text">Screenshots oder Dateien hierher ziehen oder <strong>auswählen</strong></span>
                            <span class="ps-ticket__upload-hint">Bis <?php echo esc_html($max_files); ?> Dateien, je max. <?php echo esc_html($max_size); ?> MB · JPG, PNG, PDF, DOC, ZIP</span>
                        </div>
                        <div class="ps-ticket__file-list" id="psFileList"></div>
                    </div>
                </div>

                <div class="ps-ticket__privacy">
                    <input type="checkbox" id="ps-privacy" name="privacy" required>
                    <label for="ps-privacy">Ich stimme der Verarbeitung meiner Daten gemäß der <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Datenschutzerklärung</a> zu. <span class="ps-required">*</span></label>
                </div>

                <button type="submit" class="ps-ticket__submit" id="psSubmitBtn">
                    Ticket absenden <span class="ps-ticket__submit-arrow" aria-hidden="true">→</span>
                </button>

                <div class="ps-ticket__status" id="psStatus" role="status" aria-live="polite"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
