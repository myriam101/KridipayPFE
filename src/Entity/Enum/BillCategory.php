<?php

namespace App\Entity\Enum;

enum BillCategory: string
{
    case MOIS = 'mois';
    case TRIMESTRE = 'trimestre';
    case ANS = 'ans';
   
}