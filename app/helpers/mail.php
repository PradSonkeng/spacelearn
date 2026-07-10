<?php
require_once APP_PATH . '/../vendor/autoload.php'; // Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail(string $email, string $fullName, string $token): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        
        // Important pour Gmail
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $fullName);

        $verifyLink = full_url("auth/verifyEmail?token=" . $token);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmez votre adresse email - ' . APP_NAME;
        $mail->Body    = "
            <h2>Bienvenue sur " . APP_NAME . "</h2>
            <p>Bonjour $fullName,</p>
            <p>Cliquez sur le lien ci-dessous pour vérifier votre email :</p>
            <p><a href='$verifyLink' style='font-size:18px;'>Vérifier mon email</a></p>
            <p>Ce lien expire dans 1 heure.</p>
            <hr>
            <small>Si vous n'avez pas demandé cette inscription, ignorez cet email.</small>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
        return false;
    }
}
