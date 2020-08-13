<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @implements ValueInterface<array<mixed>>
 */
final class PhpArrayValue extends Value
{
    /**
     * @var array<mixed>
     */
    private $value;

    /**
     * @return array<mixed>
     */
    public function getValue()
    {
        return $this->value;
    }
}