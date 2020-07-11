<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Util;

final class OnNegation
{
    /**
     * @param Runtime $runtime
     * @param Negation $negation
     * @return bool
     */
    public static function evaluate(Runtime $runtime, Negation $negation): bool 
    {
        $subject = OnTerm::evaluate($runtime, $negation->getSubject());

        return !Util::isTrueish($subject);
    }
}

