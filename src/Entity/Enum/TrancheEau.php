<?php

namespace App\Entity;

enum TrancheEau: string
{
    case ZERO_TWENTY = '0-20';
    case TWENTY_ONE_FORTY = '21-40';
    case FORTY_ONE_SEVENTY = '41-70';
    case SEVENTY_ONE_HUNDRED = '71-100';
    case HUNDRED_ONE_HUNDRED_FIFTY = '101-150';
    case HUNDRED_FIFTY_PLUS = '151+';
   



}