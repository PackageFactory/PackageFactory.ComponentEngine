<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTernary
{
    /**
     * @param Runtime $runtime
     * @param Ternary $ternary
     * @return ValueInterface<mixed>
     */
    public static function evaluate(Runtime $runtime, Ternary $ternary): ValueInterface
    {
        $condition = OnTerm::evaluate($runtime, $ternary->getCondition());
        
        if ($condition->asBooleanValue()->getValue()) {
            return OnTerm::evaluate($runtime, $ternary->getTrueBranch());
        } else {
            return OnTerm::evaluate($runtime, $ternary->getFalseBranch());
        }
    }
}

