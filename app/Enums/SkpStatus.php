<?php

declare(strict_types=1);

namespace App\Enums;

enum SkpStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'diajukan';
    case APPROVED = 'disetujui';
    case REJECTED = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Diajukan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }
}
