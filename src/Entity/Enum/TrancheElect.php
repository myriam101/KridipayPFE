<?php

namespace App\Entity\Enum;

enum TrancheElect: string
{
    case TRANCHE_1_50 = '1-50';
    case TRANCHE_51_100 = '51-100';
    case TRANCHE_101_200 = '101-200';
    case TRANCHE_201_300 = '201-300';
    case TRANCHE_301_500 = '301-500';
    case TRANCHE_501_PLUS = '501+';

}
