<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrowFunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ArrowFunctionValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
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
            function (ListValue $arguments) use ($runtime, $arrowFunction) {
                $index = 0;
                $properties = DictionaryValue::empty();
                foreach ($arrowFunction->getParameters() as $parameter) {
                    $properties = $properties->withAddedProperty(
                        $parameter->getValue(), 
                        $arguments->get(Key::fromInteger($index), false, $runtime)
                    );
                }

                return OnTerm::evaluate(
                    $runtime->withContext($runtime->getContext()->merge($properties)),
                    $arrowFunction->getBody()
                );
            }
        );
    }
}

