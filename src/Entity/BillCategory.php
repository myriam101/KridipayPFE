<?php

namespace App\Entity;

enum BillCategory: string
{
    case MOIS = 'mois';
    case TRIMESTRE = 'trimestre';
    case ANS = 'ans';
   
}