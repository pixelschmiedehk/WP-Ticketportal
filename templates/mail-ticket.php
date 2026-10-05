<?php
/**
 * Ticket-Notification Mail Template (an Admin)
 * Variablen: $ticket_id, $name, $email, $phone, $company, $subject, $category_label, $message, $date, $ip, $referer, $attachment_count, $logo_url, $sender_name
 */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Neues Ticket: <?php echo esc_html($ticket_id); ?></title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td { font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        img { border: 0; display: block; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 16px !important; }
            .content-cell { padding: 24px 20px !important; }
            .data-table td { display: block; width: 100% !important; padding: 6px 0 !important; }
            .data-table td:first-child { padding-bottom: 0 !important; font-weight: 700; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #020202; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

<!-- Outer wrapper -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #020202;">
<tr><td align="center" style="padding: 32px 16px;">

    <!-- Container -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="580" class="container" style="max-width: 580px; width: 100%;">

        <!-- Header -->
        <tr>
            <td align="center" style="padding: 24px 32px; background-color: #101113; border: 1px solid #222528; border-bottom: none; border-radius: 6px 6px 0 0;">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($sender_name); ?>" width="180" style="max-width: 180px; height: auto; margin-bottom: 8px;">
                <?php else: ?>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="font-size: 24px; font-weight: 700; color: #f5f4f5; letter-spacing: 0.02em;">
                                <span style="color: #ea7b3b;">P</span><?php echo esc_html(substr($sender_name, 1)); ?>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
            </td>
        </tr>

        <!-- Orange accent line -->
        <tr>
            <td style="background: linear-gradient(135deg, #ea7b3b, #f6a263); height: 3px; font-size: 0; line-height: 0;" bgcolor="#ea7b3b">&nbsp;</td>
        </tr>

        <!-- Ticket badge -->
        <tr>
            <td class="content-cell" style="padding: 32px 32px 0; background-color: #101113; border-left: 1px solid #222528; border-right: 1px solid #222528;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td align="center">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color: rgba(234,123,59,0.12); border: 1px solid rgba(234,123,59,0.25); border-radius: 3px; padding: 6px 16px; font-size: 12px; font-weight: 600; color: #ea7b3b; text-transform: uppercase; letter-spacing: 0.12em;" bgcolor="#2a1f16">
                                        NEUES TICKET
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 16px; font-size: 26px; font-weight: 700; color: #f5f4f5; line-height: 1.3;">
                            <?php echo esc_html($ticket_id); ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 6px; font-size: 18px; color: #a39e92; line-height: 1.4;">
                            <?php echo esc_html($subject); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Kontaktdaten -->
        <tr>
            <td class="content-cell" style="padding: 28px 32px; background-color: #101113; border-left: 1px solid #222528; border-right: 1px solid #222528;">

                <!-- Section: Kontakt -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
                    <tr>
                        <td style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; color: #ea7b3b; padding-bottom: 12px; border-bottom: 1px solid #16181a;">
                            KONTAKTDATEN
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="data-table">
                    <tr>
                        <td width="120" style="padding: 8px 0; font-size: 13px; color: rgba(244,244,242,0.4); vertical-align: top;">Name</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #f5f4f5; font-weight: 500;"><?php echo esc_html($name); ?></td>
                    </tr>
                    <tr>
                        <td width="120" style="padding: 8px 0; font-size: 13px; color: rgba(244,244,242,0.4); vertical-align: top;">E-Mail</td>
                        <td style="padding: 8px 0; font-size: 14px;">
                            <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #ea7b3b; text-decoration: none;"><?php echo esc_html($email); ?></a>
                        </td>
                    </tr>
                    <?php if ($phone): ?>
                    <tr>
                        <td width="120" style="padding: 8px 0; font-size: 13px; color: rgba(244,244,242,0.4); vertical-align: top;">Telefon</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #f5f4f5;"><?php echo esc_html($phone); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($company): ?>
                    <tr>
                        <td width="120" style="padding: 8px 0; font-size: 13px; color: rgba(244,244,242,0.4); vertical-align: top;">Firma</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #f5f4f5;"><?php echo esc_html($company); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td width="120" style="padding: 8px 0; font-size: 13px; color: rgba(244,244,242,0.4); vertical-align: top;">Kategorie</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #f5f4f5;">
                            <span style="background-color: #16181a; border-radius: 3px; padding: 3px 10px; font-size: 12px;"><?php echo esc_html($category_label); ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Beschreibung -->
        <tr>
            <td class="content-cell" style="padding: 0 32px 28px; background-color: #101113; border-left: 1px solid #222528; border-right: 1px solid #222528;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 16px;">
                    <tr>
                        <td style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; color: #ea7b3b; padding-bottom: 12px; border-bottom: 1px solid #16181a;">
                            BESCHREIBUNG
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="background-color: #16181a; border-radius: 3px; padding: 20px; font-size: 14px; color: #f5f4f5; line-height: 1.65;">
                            <!--ps-ticket-text--><?php echo nl2br(esc_html($message)); ?><!--/ps-ticket-text-->
                        </td>
                    </tr>
                </table>

                <?php if ($attachment_count > 0): ?>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 16px;">
                    <tr>
                        <td style="background-color: rgba(234,123,59,0.08); border: 1px solid rgba(234,123,59,0.2); border-radius: 3px; padding: 12px 16px; font-size: 13px; color: #ea7b3b;" bgcolor="#2a1f16">
                            &#128206; <?php echo esc_html($attachment_count); ?> Datei<?php echo $attachment_count > 1 ? 'en' : ''; ?> angehängt
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
            </td>
        </tr>

        <!-- Meta footer -->
        <tr>
            <td style="padding: 16px 32px; background-color: #0b0c0d; border-left: 1px solid #222528; border-right: 1px solid #222528; border-bottom: 1px solid #222528; border-radius: 0 0 6px 6px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="font-size: 11px; color: rgba(244,244,242,0.3); line-height: 1.8;">
                            Datum: <?php echo esc_html($date); ?> &nbsp;&middot;&nbsp; IP: <?php echo esc_html($ip); ?> &nbsp;&middot;&nbsp; Seite: <?php echo esc_html($referer); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Bottom spacing + branding -->
        <tr>
            <td align="center" style="padding: 24px 0 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size: 11px; color: rgba(244,244,242,0.2); text-align: center;">
                            Gesendet via <?php echo esc_html($sender_name); ?> Ticketportal
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
</td></tr>
</table>

</body>
</html>
