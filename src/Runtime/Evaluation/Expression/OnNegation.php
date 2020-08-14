<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnNegation
{
    /**
     * @param Runtime $runtime
     * @param Negation $negation
     * @return BooleanValue
     */
    public static function evaluate(Runtime $runtime, Negation $negation): BooleanValue 
    {
        return OnTerm::evaluate($runtime, $negation->getSubject())->asBooleanValue()->not();
    }
}

