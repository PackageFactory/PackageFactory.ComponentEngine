<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\DashOperation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnDashOperation
{
    /**
     * @param Runtime $runtime
     * @param DashOperation $dashOperation
     * @return float|string
     */
    public static function evaluate(Runtime $runtime, DashOperation $dashOperation)
    {
        $left = OnExpression::evaluate($runtime, $dashOperation->getLeft());
        $right = OnExpression::evaluate($runtime, $dashOperation->getRight());

        if ($dashOperation->getOperator() === DashOperation::OPERATOR_ADD) {
            if (is_string($left) || is_string($right)) {
                return $left . $right;
            } else {
                return $left + $right;
            }
        } elseif ($dashOperation->getOperator() === DashOperation::OPERATOR_SUBTRACT) {
            return $left - $right;
        } else {
            throw new \RuntimeException('@TODO: Unknown operator');
        }
    }
}

