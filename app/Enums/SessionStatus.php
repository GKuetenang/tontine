<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
}
