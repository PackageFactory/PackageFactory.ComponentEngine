<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Runtime\Context\Value\NumberValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnPointOperation
{
    /**
     * @param Runtime $runtime
     * @param PointOperation $pointOperation
     * @return NumberValue
     */
    public static function evaluate(Runtime $runtime, PointOperation $pointOperation): NumberValue 
    {
        $left = OnTerm::evaluate($runtime, $pointOperation->getLeft());
        if ($left->getValue() === 0) {
            return NumberValue::zero();
        }

        $right = OnTerm::evaluate($runtime, $pointOperation->getRight());
        if ($right->getValue() === 0) {
            return NumberValue::zero();
        }

        switch ($pointOperation->getOperator()) {
            default:
            case PointOperation::OPERATOR_MULTIPLY:
                $result = $left->multiply($right);
                break;
            case PointOperation::OPERATOR_DIVIDE:
                $result = $left->divide($right);
                break;
            case PointOperation::OPERATOR_MODULO:
                $result = $left->modulo($right);
                break;
        }

        /** @var NumberValue $result */
        return $result;
    }
}

