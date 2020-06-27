<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnAttribute
{
    /**
     * @param Runtime $runtime
     * @param Attribute $attribute
     * @return \Iterator<int, mixed>
     */
    public static function evaluate(Runtime $runtime, Attribute $attribute): \Iterator 
    {
        $name = $attribute->getAttributeName()->getValue();
        $value = $attribute->getValue();

        if (is_bool($value)) {
            yield VirtualDOM\Attribute::createBooleanFromName($name);
        } else {
            yield VirtualDOM\Attribute::createFromNameAndValue(
                $name,
                Expression\OnExpression::evaluate($runtime, $value)
            );
        }
    }
}
