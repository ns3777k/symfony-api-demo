<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class CategoryNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('category not found');
    }
}
