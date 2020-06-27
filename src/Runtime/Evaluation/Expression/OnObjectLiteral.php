<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnObjectLiteral
{
    /**
     * @param Runtime $runtime
     * @param ObjectLiteral $objectLiteral
     * @return \stdClass
     */
    public static function evaluate(Runtime $runtime, ObjectLiteral $objectLiteral): \stdClass 
    {
        $properties = [];
        foreach ($objectLiteral->getProperties() as $property) {
            foreach (OnObjectLiteralProperty::evaluate($runtime, $property) as $key => $value) {
                $properties[$key] = $value;
            }
        }

        return (object) $properties;
    }
}

