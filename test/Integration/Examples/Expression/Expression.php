<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Std;

final class Expression extends BaseClass
{
    public function __construct(
        private readonly array $nums
    ) {
    }

    public function render(): int|float
    {
        return Std::array($this->nums)->reduce(
            function (int|float $acc, int|float $cur) {
                return $acc <= 120
                    ? $cur * $acc + (17 % $cur)
                    : round($cur / $acc);
            },
            0
        );
    }
}
