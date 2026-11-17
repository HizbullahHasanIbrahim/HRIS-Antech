<?php

namespace App\Enums;

enum LeaveRequestStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'diajukan';
    case APPROVED = 'disetujui';
    case REJECTED = 'ditolak';
    case CANCELLED = 'dibatalkan';

    public function isTerminal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }

    public function canBeCancelled(): bool
    {
        return $this === self::SUBMITTED;
    }
}