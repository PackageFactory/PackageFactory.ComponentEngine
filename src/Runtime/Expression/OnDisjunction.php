<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
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
        $left = OnExpression::evaluate($runtime, $disjunction->getLeft());
        if (is_string($left)) {
            $left = $left !== '';
        } elseif (is_numeric($left)) {
            $left = $left !== 0.0;
        } elseif (is_null($left)) {
            $left = false;
        } elseif (is_bool($left)) {
            $left = $left;
        } else {
            $left = (bool) $left;
        }

        if ($left) {
            return true;
        } else {
            return OnExpression::evaluate($runtime, $disjunction->getRight());
        }
    }
}

