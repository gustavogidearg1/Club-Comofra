<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $e, $request) {
            // Si es POST (ej: logout) o request web normal
            return redirect()
                ->route('login')
                ->with('status', 'Tu sesión expiró. Iniciá sesión nuevamente.');
        });
    }
}
