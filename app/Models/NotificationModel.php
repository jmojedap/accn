<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\MailPml;

class NotificationModel extends Model
{
    /**
     * Enviar por correo electrónico un link de inicio de sesión
     * 
     * @param array|object $user
     * @param string $appName
     * @return array
     */
    public function sendLoginLink($user, string $appName = 'main'): array
    {
        $data = [
            'status' => 0,
            'message' => 'No fue posible enviar el correo electrónico',
            'link' => ''
        ];

        // Convertir a array si viene como objeto
        if (is_object($user)) {
            $user = (array) $user;
        }

        if (empty($user['email'])) {
            $data['message'] = 'El usuario no tiene correo electrónico registrado';
            return $data;
        }

        // Generar un token temporal o código seguro
        $token = bin2hex(random_bytes(16));

        // Aquí podrías guardar el token en base de datos si fuera necesario
        // Por ahora construimos el link directamente
        $loginUrl = base_url("accounts/validate_login/{$token}");

        // Cuerpo del mensaje (vista HTML)
        $htmlMessage = view('emails/login_link', [
            'name' => $user['display_name'] ?? $user['email'],
            'link' => $loginUrl,
            'appName' => $appName
        ]);

        // Enviar el correo usando PHPMailer
        $mailer = new MailPml();
        $sendResult = $mailer->send([
            'to' => $user['email'],
            'subject' => "Tu acceso a {$appName}",
            'html_message' => $htmlMessage
        ]);

        if ($sendResult['status'] == 1) {
            $data['status'] = 1;
            $data['message'] = 'Correo enviado con éxito';
            $data['link'] = $loginUrl;
        } else {
            $data['message'] = 'Error al enviar correo: ' . $sendResult['error'];
        }

        return $data;
    }
}
