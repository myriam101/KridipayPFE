<?php

namespace App\Enum;

enum TrancheGaz: string
{
    case TRANCHE_1_30 = '1-30';
    case TRANCHE_30_60 = '30-60';
    case TRANCHE_60_150 = '60-150';
    case TRANCHE_151_PLUS = '151+';
}
