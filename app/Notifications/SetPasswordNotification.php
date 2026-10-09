<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $url
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Establece tu contraseña de SIDAN')
            ->view('emails.set-password', [
                'url' => $this->url,
                'nombre' => $notifiable->informacion_personal?->nombres,
                'minutos' => config('auth.passwords.users.expire'),
            ]);
    }
}