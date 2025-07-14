<?php

namespace App;

enum PaymentIsReceived: int
{
    case RECEIVED = 1;
    case NOTRECEIVED = 0;
}
