<?php

namespace App\Notifications;

use App\Models\PointMovement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MovimientoPuntosEditado extends Notification
{
    use Queueable;

    public function __construct(
        public PointMovement $movimiento,
        public ?array $changes = null // opcional: antes/después
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->movimiento;

        $tipoTexto = match ($m->type) {
            'earn'   => 'Carga de puntos',
            'redeem' => 'Canje de puntos',
            'adjust' => 'Ajuste de puntos',
            'expire' => 'Vencimiento de puntos',
            default  => 'Movimiento de puntos',
        };

        $pts = (int) ($m->points ?? 0);
        $ptsTexto = $pts >= 0 ? ('+' . number_format($pts)) : ('-' . number_format(abs($pts)));

        $mail = (new MailMessage)
            ->subject('Club Comofra - Corrección de movimiento')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Se corrigió un movimiento de puntos registrado previamente en tu cuenta.')
            ->line('Tipo: ' . $tipoTexto)
            ->line('Puntos: ' . $ptsTexto)
            ->line('Fecha: ' . optional($m->occurred_at)->format('d/m/Y H:i'))
            ->when(!empty($m->reference), fn ($msg) => $msg->line('Referencia: ' . $m->reference))
            ->when(!empty($m->note), fn ($msg) => $msg->line('Detalle: ' . $m->note));

        // opcional: mostrar cambios
        if (!empty($this->changes)) {
            $mail->line('Cambios aplicados:');
            foreach ($this->changes as $k => $v) {
                $mail->line("- {$k}: {$v['before']} → {$v['after']}");
            }
        }

        return $mail
            ->action('Ver mis movimientos', route('points.index'))
            ->salutation('Saludos, Club Comofra');
    }
}
