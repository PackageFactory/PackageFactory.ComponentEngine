<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

enum TrafficLight : int
{
    case RED = 1;
    case YELLOW = 2;
    case GREEN = 3;
}
