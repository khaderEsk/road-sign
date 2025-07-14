<?php

namespace App;

enum ProductType: int
{
    case LOCAL = 1;
    case FOREIGN = 2;
    case BOTH = 3;
}
