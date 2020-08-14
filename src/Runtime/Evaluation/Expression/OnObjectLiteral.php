<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnObjectLiteral
{
    /**
     * @param Runtime $runtime
     * @param ObjectLiteral $objectLiteral
     * @return DictionaryValue
     */
    public static function evaluate(Runtime $runtime, ObjectLiteral $objectLiteral): DictionaryValue
    {
        $result = DictionaryValue::empty();

        foreach ($objectLiteral->getProperties() as $property) {
            foreach (OnObjectLiteralProperty::evaluate($runtime, $property) as $key => $value) {
                $result = $result->withAddedProperty((string) $key, $value);
            }
        }

        return $result;
    }
}

