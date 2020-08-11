<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NullValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;

final class Context
{
    /**
     * @return ValueInterface
     */
    public static function createEmpty(): ValueInterface
    {
        return NullValue::create();
    }

    /**
     * @param array<string, mixed> $data
     * @return ValueInterface
     */
    public static function fromArray(array $data): ValueInterface
    {
        return DictionaryValue::fromArray($data);
    }
}