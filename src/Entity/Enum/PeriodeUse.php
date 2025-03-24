<?php

namespace App\Entity;

enum PeriodeUse: string
{
    case OCTOBRE_MARS = 'OCTOBRE-MARS';
    case AVRIL_SEPTEMBRE = 'AVRIL-SEPTEMBRE';
    case SEPTEMBRE_MAI = 'SEPTEMBRE-MAI';
    case JUIN_AOUT = 'JUIN-AOUT';

}