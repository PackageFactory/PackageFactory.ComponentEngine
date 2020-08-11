<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NumberValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnNumberLiteral
{
    /**
     * @param Runtime $runtime
     * @param NumberLiteral $numberLiteral
     * @return NumberValue
     */
    public static function evaluate(Runtime $runtime, NumberLiteral $numberLiteral): NumberValue 
    {
        return NumberValue::fromNumberLiteral($numberLiteral);
    }
}

