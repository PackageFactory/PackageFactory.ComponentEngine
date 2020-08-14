<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrowFunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ArrowFunctionValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnArrowFunction
{
    /**
     * @param Runtime $runtime
     * @param ArrowFunction $arrowFunction
     * @return ArrowFunctionValue
     */
    public static function evaluate(Runtime $runtime, ArrowFunction $arrowFunction): ArrowFunctionValue
    {
        return ArrowFunctionValue::fromClosure(
            function (...$arguments) use ($runtime, $arrowFunction) {
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

                return OnTerm::evaluate(
                    $runtime->withContext(
                        $runtime->getContext()->merge(DictionaryValue::fromArray($properties))
                    ),
                    $arrowFunction->getBody()
                );
            }
        );
    }
}

