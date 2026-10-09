<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UsuarioCreadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $passwordTemporal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a SIDAN | Credenciales de acceso')
            ->view('emails.usuario-creado', [
                'nombre' => $notifiable->informacion_personal?->nombres ?? '',
                'correo' => $notifiable->correo,
                'passwordTemporal' => $this->passwordTemporal,
                'url' => route('login'),
            ]);
    }
}