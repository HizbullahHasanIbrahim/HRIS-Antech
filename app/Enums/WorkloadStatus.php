<?php

namespace App\Enums;

enum WorkloadStatus: string
{
    case DRAFT = 'draf';
    case SUBMITTED = 'diajukan';
    case FINAL = 'final';
}
