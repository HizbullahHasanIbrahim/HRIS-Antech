<?php

declare(strict_types=1);

namespace App\Enums;

enum BpjsStatus: string
{
    case ACTIVE = 'aktif';
    case INACTIVE = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Nonaktif',
        };
    }
}
