<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnComparison
{
    /**
     * @param Runtime $runtime
     * @param Comparison $comparison
     * @return ValueInterface
     */
    public static function evaluate(Runtime $runtime, Comparison $comparison): ValueInterface
    {
        $left = OnTerm::evaluate($runtime, $comparison->getLeft());
        $right = OnTerm::evaluate($runtime, $comparison->getRight());

        switch ($comparison->getOperator()) {
            default:
            case Comparison::COMPARATOR_EQ:
                return $left->equals($right, $runtime);
            case Comparison::COMPARATOR_NEQ:
                return $left->equals($right, $runtime)->not();
            case Comparison::COMPARATOR_GT:
                return $left->greaterThan($right, $runtime);
            case Comparison::COMPARATOR_GTE:
                return $left->equals($right, $runtime)->or($left->greaterThan($right, $runtime));
            case Comparison::COMPARATOR_LT:
                return $left->lessThan($right, $runtime);
            case Comparison::COMPARATOR_LTE:
                return $left->equals($right, $runtime)->or($left->lessThan($right, $runtime));
        }
    }
}

