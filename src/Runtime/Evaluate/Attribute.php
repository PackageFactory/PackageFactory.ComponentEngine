<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluate;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Attribute
{
    public static function evaluate(
        Context $context, 
        Ast\Attribute $attribute
    ): ?VirtualDOM\Attribute {
        if (is_bool($attribute->getValue())) {
            return VirtualDOM\Attribute::createBooleanFromName(
                (string) $attribute->getName()
            );
        }
        elseif ($attribute->getValue() instanceof Ast\Expression) {
            $value = Expression::evaluate($context, $attribute->getValue());

            if (is_bool($value)) {
                if ($value) {
                    return VirtualDOM\Attribute::createBooleanFromName(
                        (string) $attribute->getName()
                    );
                }
                else {
                    return null;
                }
            }
            elseif (is_string($value) || is_array($value)) {
                return VirtualDOM\Attribute::createFromNameAndValue(
                    (string) $attribute->getName(),
                    $value
                );
            }
            elseif ($value === null) {
                return null;
            }
            else {
                throw new \RuntimeException('@TODO: Invalid Attribute Value');
            }
        }
        elseif ($attribute->getValue() instanceof Ast\StringLiteral) {
            return VirtualDOM\Attribute::createFromNameAndValue(
                (string) $attribute->getName(),
                (string) $attribute->getValue()
            );
        }
        else {
            throw new \RuntimeException('@TODO: Invalid attribute value');
        }
    }
}