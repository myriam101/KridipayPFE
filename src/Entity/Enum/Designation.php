<?php

namespace App\Entity\Enum;

enum Designation: int
{
    case LAVE_LINGE = 0;
    case SECHE_LINGE = 1;
    case LAVANTE_SECHANTE = 2;
    case REFRIGERATEUR = 3;
    case LAVE_VAISSELLE = 4;
    case FOUR = 5;
    case CLIMATISEUR = 6;
    case CAVE_A_VIN = 7;
    case CONGELATEUR = 8;
    case HOTTE = 9;
    case TABLE_CUISSON = 10;
    case ASPIRATEUR = 11;
    case CHAUFFAGE = 12;
    case CHAUFFE_EAU = 13;
    case CHAUDIERE = 14;
    case TV = 15;



}