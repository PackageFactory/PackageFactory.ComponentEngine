<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrowFunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnArrowFunction
{
    /**
     * @param Runtime $runtime
     * @param ArrowFunction $arrayLiteral
     * @return \Closure
     */
    public static function evaluate(Runtime $runtime, ArrowFunction $arrowFunction): \Closure
    {
        return function (...$arguments) use ($runtime, $arrowFunction) {
            $index = 0;
            $properties = [];
            foreach ($arrowFunction->getParameters() as $parameter) {
                /** @var Identifier $parameter */
                if (isset($arguments[$index])) {
                    $properties[$parameter->getValue()] = $arguments[$index];
                } else {
                    throw new \Exception('@TODO: Missing argument: ' . $parameter->getValue());
                }
            }

            return OnExpression::evaluate(
                $runtime->withContext(
                    $runtime->getContext()->withMergedProperties($properties)
                ),
                $arrowFunction->getBody()
            );
        };
    }
}

