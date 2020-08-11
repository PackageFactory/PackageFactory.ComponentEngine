<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnBooleanLiteral
{
    /**
     * @param Runtime $runtime
     * @param BooleanLiteral $booleanLiteral
     * @return BooleanValue
     */
    public static function evaluate(Runtime $runtime, BooleanLiteral $booleanLiteral): BooleanValue
    {
        return BooleanValue::fromBooleanLiteral($booleanLiteral);
    }
}

