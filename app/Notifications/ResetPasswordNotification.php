<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $url
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    // Envía la notificación de restablecimiento de contraseña por correo electrónico.
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Restablece tu contraseña de SIDAN')
            ->view('emails.password-reset', [
                'url' => $this->url,
                'nombre' => $notifiable->informacion_personal?->nombres,
                'minutos' => config('auth.passwords.users.expire'),
            ]);
    }
}