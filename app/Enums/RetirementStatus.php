<?php

declare(strict_types=1);

namespace App\Enums;

enum RetirementStatus: string
{
    case PROPOSED = 'diajukan';
    case PROCESSING = 'diproses';
    case COMPLETED = 'selesai';
    case CANCELLED = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::PROPOSED => 'Diusulkan',
            self::PROCESSING => 'Diproses',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
