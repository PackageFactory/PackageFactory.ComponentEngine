<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NullLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnNullLiteral
{
    /**
     * @param Runtime $runtime
     * @param NullLiteral $nullLiteral
     * @return null
     */
    public static function evaluate(Runtime $runtime, NullLiteral $nullLiteral) 
    {
        return null;
    }
}

