<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;

final class Context
{
    /**
     * @return DictionaryValue
     */
    public static function empty(): DictionaryValue
    {
        return DictionaryValue::fromArray([]);
    }

    /**
     * @param array<string, mixed> $data
     * @return DictionaryValue
     */
    public static function fromArray(array $data): DictionaryValue
    {
        return DictionaryValue::fromArray($data);
    }
}