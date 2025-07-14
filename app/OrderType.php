<?php

namespace App;

enum OrderType: int
{
    case RELEASE = 1;
    case INSTALLATION = 2;
    case RELEASE_AND_INSTALLATION = 3;
}
