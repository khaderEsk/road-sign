<?php

namespace App;

enum DiscountType: int
{
    case AMOUNT = 1;
    case PERCENTAGE = 2;
}
