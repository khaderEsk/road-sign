<?php

namespace App;

enum BookingType: int
{
    case TEMPORARY = 1;
    case PERMANENT = 2;
}
