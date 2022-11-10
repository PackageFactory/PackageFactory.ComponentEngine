<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class TemplateLiteral extends BaseClass
{
    public function __construct(
        public readonly string $expression,
        public readonly bool $isActive,
        public readonly int|float $someNumber
    ) {
    }

    public function render(): string
    {
        return 'A template literal may contain ' . $this->expression . 's.' . "\n\n" . '  It can span multiple lines.' . "\n\n" . '  Interpolated Expressions can be arbitrarily complex:' . "\n" . '  ' . ($this->isActive ? (27 * $this->someNumber) : ($this->someNumber % 17)) . "\n\n" . '  They can also contain other template literals:' . "\n" . '  ' . ($this->isActive ? 'Is 27? ' . (($this->someNumber === 27) ? 'yes' : 'no') : 'Number is ' . 27) . "\n\n" . '  Even markup:' . "\n" . '  ' . '<header><h1>Number is ' . $this->someNumber . '</h1></header>' . "\n" . '  ';
    }
}
