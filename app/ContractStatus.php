<?php

namespace App;

enum ContractStatus: int
{
    case ONGOING = 1;
    case CANCELED = 2;
    case ENDED = 3;
}
