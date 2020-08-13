<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Conjunction;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnConjunction
{
    /**
     * @param Runtime $runtime
     * @param Conjunction $conjunction
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Conjunction $conjunction) 
    {
        $left = OnTerm::evaluate($runtime, $conjunction->getLeft());

        if ($left->isTrueish($runtime)) {
            return OnTerm::evaluate($runtime, $conjunction->getRight());
        } else {
            return BooleanValue::false();
        }
    }
}

