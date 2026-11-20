<?php

namespace App\Enums;

/**
 * Internal tutors are hired by EduLink directly; external tutors join the
 * marketplace and must pass document verification before they are listed.
 */
enum StatusTutor: string
{
    case Internal = 'internal';
    case Eksternal = 'eksternal';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
