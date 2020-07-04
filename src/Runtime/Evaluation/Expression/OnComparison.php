<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnComparison
{
    /**
     * @param Runtime $runtime
     * @param Comparison $comparison
     * @return bool
     */
    public static function evaluate(Runtime $runtime, Comparison $comparison): bool 
    {
        $left = OnTerm::evaluate($runtime, $comparison->getLeft());
        $right = OnTerm::evaluate($runtime, $comparison->getRight());

        if ($comparison->getOperator() === Comparison::COMPARATOR_EQ) {
            return $left === $right;
        } elseif ($comparison->getOperator() === Comparison::COMPARATOR_GT) {
            return $left > $right;
        } elseif ($comparison->getOperator() === Comparison::COMPARATOR_GTE) {
            return $left >= $right;
        } elseif ($comparison->getOperator() === Comparison::COMPARATOR_LT) {
            return $left < $right;
        } elseif ($comparison->getOperator() === Comparison::COMPARATOR_LTE) {
            return $left <= $right;
        } else {
            throw new \RuntimeException('@TODO: Unknown operator');
        }
    }
}

