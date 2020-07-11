<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Util;

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

        if (Util::isTrueish($left)) {
            return $left;
        } else {
            $right = OnTerm::evaluate($runtime, $disjunction->getRight());

            if (Util::isTrueish($right)) {
                return $right;
            } elseif ($right === null) {
                return null;
            } elseif ($right === 0.0) {
                return 0.0;
            } else {
                return false;
            }
        }
    }
}

