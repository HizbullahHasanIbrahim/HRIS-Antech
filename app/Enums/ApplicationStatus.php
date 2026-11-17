<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'diajukan';
    case IN_PROCESS = 'proses';
    case APPROVED = 'diterima';
    case REJECTED = 'ditolak';
    case EMPLOYEE_CREATED = 'pegawai_dibuat';

    public function label(): string
    {
        return match ($this) {
            self::APPLIED => 'Diajukan',
            self::IN_PROCESS => 'Proses',
            self::APPROVED => 'Diterima',
            self::REJECTED => 'Ditolak',
            self::EMPLOYEE_CREATED => 'Pegawai Dibuat',
        };
    }
}
