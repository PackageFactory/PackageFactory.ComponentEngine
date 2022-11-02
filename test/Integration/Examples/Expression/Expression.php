<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class Expression extends BaseClass
{
    public function __construct(
        private readonly int|float $a,
        private readonly int|float $b
    ) {
    }

    public function render(): string
    {
        return (string) ($this->a <= 120
            ? $this->b * $this->a + (17 % $this->b)
            : $this->b / $this->a);
    }
}
