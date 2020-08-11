<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnStringLiteral
{
    /**
     * @param Runtime $runtime
     * @param StringLiteral $stringLiteral
     * @return StringValue
     */
    public static function evaluate(Runtime $runtime, StringLiteral $stringLiteral): StringValue 
    {
        return StringValue::fromStringLiteral($stringLiteral);
    }
}

