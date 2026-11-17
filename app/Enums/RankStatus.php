<?php

namespace App\Enums;

enum RankStatus: string
{
    case SUBMITTED = 'diajukan';
    case APPROVED = 'disetujui';
    case REJECTED = 'ditolak';
    case CANCELLED = 'dibatalkan';

    public function isTerminal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }
}
