<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteralProperty;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnObjectLiteralProperty
{
    /**
     * @param Runtime $runtime
     * @param ObjectLiteralProperty $objectLiteralProperty
     * @return \Iterator<string, void>
     */
    public static function evaluate(Runtime $runtime, ObjectLiteralProperty $objectLiteralProperty): \Iterator
    {
        $value = $objectLiteralProperty->getValue();

        if ($value instanceof Spread) {
            yield from OnSpread::evaluate($runtime, $value);
        } else {
            $value = OnExpression::evaluate($runtime, $value);
            
            if ($value !== null) {
                $key = $objectLiteralProperty->getKey();

                if ($key === null) {
                    throw new \RuntimeException('@TODO: Object key cannot be null.');
                } elseif ($key instanceof Identifier) {
                    yield $key->getValue() => $value;
                } else {
                    yield OnExpression::evaluate($runtime, $key) => $value;
                }
            }
        }
    }
}

