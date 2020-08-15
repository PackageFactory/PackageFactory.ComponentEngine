<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnProp
{
    /**
     * @param Runtime $runtime
     * @param Attribute $prop
     * @return \Iterator<string, ValueInterface<mixed>>
     */
    public static function evaluate(Runtime $runtime, Attribute $prop): \Iterator 
    {
        $name = $prop->getAttributeName()->getValue();
        $value = $prop->getValue();

        if (is_null($value)) {
            yield $name => BooleanValue::true();
        } else {
            yield $name => Expression\OnTerm::evaluate($runtime, $value);
        }
    }
}

