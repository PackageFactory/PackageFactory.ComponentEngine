<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluate;

use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Prop
{
    /**
     * @param Context $context
     * @param Ast\Attribute $attribute
     * @return mixed
     */
    public static function evaluate(
        Context $context, 
        Ast\Attribute $attribute
    ) {
        if (is_bool($attribute->getValue())) {
            return $attribute->getValue();
        }
        elseif ($attribute->getValue() instanceof Ast\Expression) {
            return Expression::evaluate($context, $attribute->getValue());
        }
        elseif ($attribute->getValue() instanceof Ast\StringLiteral) {
            return (string) $attribute->getValue();
        }
        else {
            throw new \RuntimeException('@TODO: Invalid attribute value');
        }
    }
}