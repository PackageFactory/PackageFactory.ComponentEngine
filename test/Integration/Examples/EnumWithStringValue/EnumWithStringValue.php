<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

enum ImageLoadingMethod : string
{
    case EAGER = 'eager';
    case LAZY = 'lazy';
}
