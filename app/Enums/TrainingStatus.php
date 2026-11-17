<?php

namespace App\Enums;

enum TrainingStatus: string
{
    case SCHEDULED = 'dijadwalkan';
    case COMPLETED = 'selesai';
}
