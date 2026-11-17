<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'terkirim';
    case SELECTED = 'terpilih';
    case REJECTED = 'ditolak';
    case APPROVED = 'diterima';
    case EMPLOYEE_CREATED = 'pegawai_dibuat';
}