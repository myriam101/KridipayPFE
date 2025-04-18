<?php

namespace App\Entity\Enum;

enum TrancheGaz: string
{
    case TRANCHE_1_30 = '1-30';
    case TRANCHE_31_60 = '31-60';
    case TRANCHE_61_150 = '61-150';
    case TRANCHE_151_PLUS = '151+';
}
