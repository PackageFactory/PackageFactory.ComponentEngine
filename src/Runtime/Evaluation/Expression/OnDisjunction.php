<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnDisjunction
{
    /**
     * @param Runtime $runtime
     * @param Disjunction $disjunction
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Disjunction $disjunction)
    {
        $left = OnTerm::evaluate($runtime, $disjunction->getLeft());

        if ($left->isTrueish($runtime)) {
            return $left;
        } else {
            $right = OnTerm::evaluate($runtime, $disjunction->getRight());

            if ($right->isTrueish($runtime) || $right->getValue($runtime) === null || $right->getValue($runtime) === 0.0) {
                return $right;
            } else {
                return BooleanValue::false();
            }
        }
    }
}

