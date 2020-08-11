<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\DashOperation;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnDashOperation
{
    /**
     * @param Runtime $runtime
     * @param DashOperation $dashOperation
     * @return ValueInterface
     */
    public static function evaluate(Runtime $runtime, DashOperation $dashOperation): ValueInterface
    {
        $left = OnTerm::evaluate($runtime, $dashOperation->getLeft());
        $right = OnTerm::evaluate($runtime, $dashOperation->getRight());

        switch ($dashOperation->getOperator()) {
            default:
            case DashOperation::OPERATOR_ADD:
                return $left->add($right);
            case DashOperation::OPERATOR_SUBTRACT:
                return $left->subtract($right);
        }
    }
}
