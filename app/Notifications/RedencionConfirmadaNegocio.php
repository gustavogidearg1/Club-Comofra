<?php

namespace App\Notifications;

use App\Models\PointMovement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RedencionConfirmadaNegocio extends Notification
{
    use Queueable;

    public function __construct(public PointMovement $movimiento) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $m = $this->movimiento;

        return (new MailMessage)
            ->subject('Consumo de puntos confirmado')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Se confirmó un consumo de puntos.')
            ->line('Empleado: ' . ($m->employee->name ?? '—'))
            ->line('Puntos: ' . abs((int)$m->points))
            ->line('Referencia: ' . ($m->reference ?? '—'))
            ->line('Fecha: ' . optional($m->occurred_at)->format('d/m/Y H:i'))
            ->line('Gracias.');
    }
}

