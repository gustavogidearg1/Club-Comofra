<?php

namespace App\Mail;

use App\Models\Oferta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfertaPublicadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Oferta $oferta;

    public function __construct(Oferta $oferta)
    {
        $this->oferta = $oferta;
    }

    public function build()
    {
        return $this->subject('Nueva oferta disponible - ' . $this->oferta->titulo)
            ->view('emails.ofertas.publicada');
    }
}
