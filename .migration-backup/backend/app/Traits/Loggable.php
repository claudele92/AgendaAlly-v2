<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Throwable;

trait Loggable
{
    /**
     * @param Throwable $e
     * @return void
     */
    public function error(Throwable $e): void
    {
        Log::error('An application exception was reported.', [
            'exception_class' => $e::class,
        ]);
    }
}

