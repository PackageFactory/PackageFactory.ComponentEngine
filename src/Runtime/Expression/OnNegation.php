<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnNegation
{
    /**
     * @param Runtime $runtime
     * @param Negation $negation
     * @return bool
     */
    public static function evaluate(Runtime $runtime, Negation $negation): bool 
    {
        throw new \Exception('@TODO: onNegation');
    }
}

