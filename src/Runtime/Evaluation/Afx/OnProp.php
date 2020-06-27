<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnProp
{
    /**
     * @param Runtime $runtime
     * @param Attribute $prop
     * @return \Iterator<string, mixed>
     */
    public static function evaluate(Runtime $runtime, Attribute $prop): \Iterator 
    {
        $name = $prop->getAttributeName()->getValue();
        $value = $prop->getValue();

        if (is_bool($value)) {
            yield $name => $value;
        } else {
            yield $name => Expression\OnExpression::evaluate($runtime, $value);
        }
    }
}
