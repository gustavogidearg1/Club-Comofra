<?php

namespace App\Jobs;

use App\Mail\OfertaPublicadaMail;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarOfertaPublicadaJob implements ShouldQueue
{
    use Queueable;

    public int $ofertaId;

    public function __construct(int $ofertaId)
    {
        $this->ofertaId = $ofertaId;
    }

    public function handle(): void
    {
        try {
            $oferta = Oferta::with(['company', 'user'])->find($this->ofertaId);

            if (!$oferta) {
                Log::warning('EnviarOfertaPublicadaJob: oferta no encontrada', [
                    'oferta_id' => $this->ofertaId,
                ]);
                return;
            }

            if (!$oferta->enviar_correo || !$oferta->publicada || $oferta->estado !== 'publicada') {
                Log::info('EnviarOfertaPublicadaJob: oferta no requiere envío', [
                    'oferta_id' => $oferta->id,
                    'enviar_correo' => $oferta->enviar_correo,
                    'publicada' => $oferta->publicada,
                    'estado' => $oferta->estado,
                ]);
                return;
            }

            if ($oferta->correo_enviado) {
                Log::info('EnviarOfertaPublicadaJob: correo ya enviado', [
                    'oferta_id' => $oferta->id,
                ]);
                return;
            }

            $empleados = User::query()
                ->where('company_id', $oferta->company_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'empleado');
                })
                ->whereNotNull('email')
                ->get();

            Log::info('EnviarOfertaPublicadaJob: empleados encontrados', [
                'oferta_id' => $oferta->id,
                'cantidad' => $empleados->count(),
            ]);

            foreach ($empleados as $empleado) {
                Log::info('EnviarOfertaPublicadaJob: enviando mail', [
                    'oferta_id' => $oferta->id,
                    'user_id' => $empleado->id,
                    'email' => $empleado->email,
                ]);

                Mail::to($empleado->email)->send(new OfertaPublicadaMail($oferta));
            }

            $oferta->update([
                'correo_enviado' => true,
                'fecha_envio_correo' => now(),
            ]);

            Log::info('EnviarOfertaPublicadaJob: envío completado', [
                'oferta_id' => $oferta->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('EnviarOfertaPublicadaJob: error al enviar', [
                'oferta_id' => $this->ofertaId,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);

            throw $e;
        }
    }
}
