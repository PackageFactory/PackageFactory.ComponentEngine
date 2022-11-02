<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class Numbers extends BaseClass
{
    public function render(): int|float
    {
        return 0 + 1234567890 + 42 + 0b10000000000000000000000000000000 + 0b01111111100000000000000000000000 + 0b00000000011111111111111111111111 + 0o755 + 0o644 + 0xFFFFFFFFFFFFFFFFF + 0x123456789ABCDEF + 0xA + 1E3 + 2e6 + 123.456 + 0.1e2 + .22;
    }
}
