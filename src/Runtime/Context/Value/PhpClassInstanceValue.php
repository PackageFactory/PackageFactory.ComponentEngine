<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @implements ValueInterface<object>
 */
final class PhpClassInstanceValue extends Value
{
    /**
     * @var object
     */
    private $value;

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }
}