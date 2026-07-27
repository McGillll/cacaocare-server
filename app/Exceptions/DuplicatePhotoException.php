<?php

namespace App\Exceptions;

use App\Models\Cacao;
use RuntimeException;

class DuplicatePhotoException extends RuntimeException
{
    public function __construct(string $message, private Cacao $cacao, private bool $limited = false)
    {
        parent::__construct($message);
    }

    public function getCacao()
    {
        return $this->cacao;
    }

    public function isLimited()
    {
        return $this->limited;
    }
}
