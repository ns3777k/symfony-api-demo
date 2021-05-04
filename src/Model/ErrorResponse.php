<?php

declare(strict_types=1);

namespace App\Model;

class ErrorResponse
{
    private string $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
