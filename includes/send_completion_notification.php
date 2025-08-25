<?php
require_once __DIR__ . '/phpmailer_autoload.php';
require_once __DIR__ . '/db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendCompletionNotification(int $storyId, string $storyTitle): void {
    $db = connectDB();

    
    $stmt = $db->prepare("
        SELECT DISTINCT u.email
        FROM users u
        INNER JOIN collaborations c ON u.id = c.user_id
        WHERE c.story_id = ?
    ");
    $stmt->bind_param("i", $storyId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 25;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;

            $mail->CharSet = 'UTF-8';

            $mail->setFrom('noreply@cuentacuentos.local', 'Proyecto Cuentacuentos');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "¡Tu cuento '$storyTitle' ha sido finalizado!";
            $mail->Body = "
                <h2>Gracias por tu participación</h2>
                <p>El cuento <strong>$storyTitle</strong> ha llegado a su fin.</p>
                <p>Puedes consultarlo completo en la plataforma.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/debug_mail.txt', "Error al enviar a $email: {$mail->ErrorInfo}\n", FILE_APPEND);
        }
    }

    $stmt->close();
    $db->close();
}
