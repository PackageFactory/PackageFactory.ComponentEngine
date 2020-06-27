<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Conjunction;
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
        $left = OnExpression::evaluate($runtime, $conjunction->getLeft());
        if (is_string($left)) {
            $left = $left !== '';
        } elseif (is_numeric($left)) {
            $left = $left !== 0;
        } elseif (is_null($left)) {
            $left = false;
        } elseif (is_bool($left)) {
            $left = $left;
        } else {
            $left = (bool) $left;
        }

        if ($left === true) {
            return OnExpression::evaluate($runtime, $conjunction->getRight());
        } else {
            return false;
        }
    }
}

