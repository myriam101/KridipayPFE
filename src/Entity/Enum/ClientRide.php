<?php

namespace App\Entity\Enum;

enum ClientRide: string
{
    case VOITURE_THERMIQUE = 'voiture thermique';
    case VOITURE_ELECTRIQUE = 'voiture electrique';
    case MARCHE = 'marche';
    case VELO = 'velo';
    case TRANSPORT_EN_COMMUN = 'transport en commun';

   
}