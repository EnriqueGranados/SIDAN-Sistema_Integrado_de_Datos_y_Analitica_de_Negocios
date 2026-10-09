<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GooglePasswordResetAttempt extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    // Envía la notificación de intento de restablecimiento de contraseña para cuentas de Google por correo electrónico.
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitud de recuperación de contraseña en SIDAN')
            ->view('emails.google-password-reset-attempt', [
                'nombre' => $notifiable->informacion_personal?->nombres,
                'loginUrl' => route('google.login'),
            ]);
    }
}