<?php

namespace App\Entity\Enum;

enum Designation: string
{
    case LAVE_LINGE = 'lave linge';
    case SECHE_LINGE = 'seche linge';
    case LAVANTE_SECHANTE = 'lavante sechante';
    case REFRIGERATEUR = 'refrigerateur';
    case LAVE_VAISSELLE = 'lave vaisselle';
    case FOUR = 'four';
    case CLIMATISEUR = 'climatiseur';
    case CAVE_A_VIN = 'cave a vin';
    case CONGELATEUR = 'congelateur';
    case HOTTE = 'hotte';
    case TABLE_CUISSON = 'table cuisson';
    case ASPIRATEUR = 'aspirateur';
    case CHAUFFAGE = 'chauffage';
    case CHAUFFE_EAU = 'chauffe eau';
    case CHAUDIERE = 'chaudiere';
    case TV = 'tv';



}