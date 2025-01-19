<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use App\Exceptions\SessionException;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(callable $next)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException("Session already active!");
        }

        // proveravamo da li je HTML Headers vec poslat, ako jeste, podaci iz sesije ne mogu da se salju
        if (headers_sent($filename, $line)) {
            throw new SessionException("Headers already sent! Data output from {$filename} - Line: {$line}");
        }

        session_start();

        $next();
        session_write_close();
    }
}
