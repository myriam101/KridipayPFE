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


 public function label(): string
    {
        return match($this) {
            self::LAVE_LINGE => 'Lave linge',
            self::SECHE_LINGE => 'Sèche linge',
            self::LAVANTE_SECHANTE => 'Lavante séchante',
            self::REFRIGERATEUR => 'Réfrigérateur',
            self::LAVE_VAISSELLE => 'Lave vaisselle',
            self::FOUR => 'Four',
            self::CLIMATISEUR => 'Climatiseur',
            self::CAVE_A_VIN => 'Cave à vin',
            self::CONGELATEUR => 'Congélateur',
            self::HOTTE => 'Hotte',
            self::TABLE_CUISSON => 'Table de cuisson',
            self::ASPIRATEUR => 'Aspirateur',
            self::CHAUFFAGE => 'Chauffage',
            self::CHAUFFE_EAU => 'Chauffe eau',
            self::CHAUDIERE => 'Chaudière',
            self::TV => 'TV',
        };
    }
}