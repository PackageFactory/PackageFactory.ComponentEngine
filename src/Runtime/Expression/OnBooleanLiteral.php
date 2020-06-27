<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnBooleanLiteral
{
    /**
     * @param Runtime $runtime
     * @param BooleanLiteral $booleanLiteral
     * @return bool
     */
    public static function evaluate(Runtime $runtime, BooleanLiteral $booleanLiteral): bool
    {
        return $booleanLiteral->getBoolean();
    }
}

