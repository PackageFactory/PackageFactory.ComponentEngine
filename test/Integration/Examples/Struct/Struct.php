<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class Link extends BaseClass
{
    public function __construct(
        public readonly string $href,
        public readonly string $target
    ) {
    }
}
