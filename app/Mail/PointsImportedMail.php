<?php

namespace App\Mail;

use App\Models\User;
use App\Models\PointImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PointsImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public PointImportBatch $batch,
        public int $pointsDelta,       // lo que se le sumó/restó en esta importación
        public ?string $reference = null
    ) {}

    public function build()
    {
        return $this
            ->subject('Se actualizaron tus puntos en Club Comofra')
            ->markdown('emails.points.imported', [
                'user'       => $this->user,
                'batch'      => $this->batch,
                'pointsDelta'=> $this->pointsDelta,
                'reference'  => $this->reference,
            ]);
    }
}
