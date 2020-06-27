<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTernary
{
    /**
     * @param Runtime $runtime
     * @param Ternary $ternary
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Ternary $ternary) 
    {
        $condition = OnExpression::evaluate($runtime, $ternary->getCondition());
        
        if ($condition) {
            return OnExpression::evaluate($runtime, $ternary->getTrueBranch());
        } else {
            return OnExpression::evaluate($runtime, $ternary->getFalseBranch());
        }
    }
}

