<?php
/**
 * Bestätigungsmail Template (an Absender/Kunde)
 * Variablen: $ticket_id, $name, $subject, $logo_url, $sender_name, $company, $phone, $address, $website, $confirm_text
 */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Deine Anfrage: <?php echo esc_html($ticket_id); ?></title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td { font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        img { border: 0; display: block; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 16px !important; }
            .content-cell { padding: 24px 20px !important; }
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
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($sender_name); ?>" width="220" style="max-width: 220px; height: auto; margin: 0 auto 4px;">
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

        <!-- Ticket badge + greeting -->
        <tr>
            <td class="content-cell" style="padding: 32px 32px 0; background-color: #101113; border-left: 1px solid #222528; border-right: 1px solid #222528;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td align="center">
                            <!-- Checkmark circle -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="width: 56px; height: 56px; background-color: rgba(76,175,80,0.12); border-radius: 50%; text-align: center; vertical-align: middle; font-size: 28px;" bgcolor="#1a2a1a">
                                        &#10003;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 20px; font-size: 22px; font-weight: 700; color: #f5f4f5; line-height: 1.3;">
                            Anfrage erhalten
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color: rgba(234,123,59,0.12); border: 1px solid rgba(234,123,59,0.25); border-radius: 3px; padding: 6px 16px; font-size: 13px; font-weight: 600; color: #ea7b3b; letter-spacing: 0.05em;" bgcolor="#2a1f16">
                                        <?php echo esc_html($ticket_id); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Body text -->
        <tr>
            <td class="content-cell" style="padding: 28px 32px; background-color: #101113; border-left: 1px solid #222528; border-right: 1px solid #222528;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="font-size: 15px; color: #f5f4f5; line-height: 1.7;">
                            <?php echo nl2br(esc_html($confirm_text)); ?>
                        </td>
                    </tr>
                </table>

                <!-- Ticket summary card -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 24px;">
                    <tr>
                        <td style="background-color: #16181a; border-radius: 3px; padding: 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; color: #ea7b3b; padding-bottom: 12px;">
                                        DEINE ANFRAGE
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td width="90" style="font-size: 13px; color: rgba(245,244,245,0.65); vertical-align: top; padding: 4px 0;">Ticket-ID</td>
                                                <td style="font-size: 14px; color: #f5f4f5; font-weight: 600; padding: 4px 0;"><?php echo esc_html($ticket_id); ?></td>
                                            </tr>
                                            <tr>
                                                <td width="90" style="font-size: 13px; color: rgba(245,244,245,0.65); vertical-align: top; padding: 4px 0;">Betreff</td>
                                                <td style="font-size: 14px; color: #f5f4f5; padding: 4px 0;"><?php echo esc_html($subject); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Signature / Footer -->
        <tr>
            <td style="padding: 20px 32px; background-color: #0b0c0d; border-left: 1px solid #222528; border-right: 1px solid #222528; border-bottom: 1px solid #222528; border-radius: 0 0 6px 6px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="border-top: 1px solid #16181a; padding-top: 16px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <?php if ($sender_name): ?>
                                <tr>
                                    <td style="font-size: 14px; font-weight: 600; color: #f5f4f5; padding-bottom: 2px;">
                                        <?php echo esc_html($sender_name); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($company && $company !== $sender_name): ?>
                                <tr>
                                    <td style="font-size: 13px; color: #f5f4f5; padding-bottom: 2px;">
                                        <?php echo esc_html($company); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($address): ?>
                                <tr>
                                    <td style="font-size: 12px; color: rgba(245,244,245,0.6); padding-bottom: 2px;">
                                        <?php echo esc_html($address); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="font-size: 12px; color: rgba(245,244,245,0.6); padding-top: 6px;">
                                        <?php if ($phone): ?>
                                            <?php echo esc_html($phone); ?> &nbsp;&middot;&nbsp;
                                        <?php endif; ?>
                                        <?php if ($website): ?>
                                            <a href="<?php echo esc_url($website); ?>" style="color: #ea7b3b; text-decoration: none;"><?php echo esc_html(preg_replace('#^https?://#', '', $website)); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Bottom branding -->
        <tr>
            <td align="center" style="padding: 24px 0 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size: 11px; color: rgba(245,244,245,0.45); text-align: center;">
                            Diese E-Mail wurde automatisch versendet. Bitte antworte nicht direkt auf diese Nachricht.
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
