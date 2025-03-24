<?php

namespace App\Entity;

enum Type: string
{
    case Condensation = 'Condensation';
    case Evacuation = 'Evacuation';
    case Gaz = 'Gaz';
    case Electrique = 'Electrique';
    case Eau = 'Eau';
    case Air = 'Air';
}