<?php

namespace App;

enum BookingStatus: int
{
    case PENDING = 1;
    case INSTALLED = 2;
    case COMPLETED = 3;
}
