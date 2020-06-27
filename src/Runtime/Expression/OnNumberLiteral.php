<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnNumberLiteral
{
    /**
     * @param Runtime $runtime
     * @param NumberLiteral $numberLiteral
     * @return float
     */
    public static function evaluate(Runtime $runtime, NumberLiteral $numberLiteral): float 
    {
        return $numberLiteral->getNumber();
    }
}

