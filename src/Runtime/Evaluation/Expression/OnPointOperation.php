<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnPointOperation
{
    /**
     * @param Runtime $runtime
     * @param PointOperation $pointOperation
     * @return float
     */
    public static function evaluate(Runtime $runtime, PointOperation $pointOperation): float 
    {
        $left = OnTerm::evaluate($runtime, $pointOperation->getLeft());
        if ($left === 0) {
            return 0;
        }

        $right = OnTerm::evaluate($runtime, $pointOperation->getRight());
        if ($right === 0) {
            return 0;
        }

        if ($pointOperation->getOperator() === PointOperation::OPERATOR_MULTIPLY) {
            return $left * $right;
        } elseif ($pointOperation->getOperator() === PointOperation::OPERATOR_DIVIDE) {
            return $left / $right;
        } elseif ($pointOperation->getOperator() === PointOperation::OPERATOR_MODULO) {
            return $left % $right;
        } else {
            throw new \RuntimeException('@TODO: Unknown operator');
        }
    }
}

