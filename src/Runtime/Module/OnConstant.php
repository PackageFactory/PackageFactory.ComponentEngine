<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Constant;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Afx;
use PackageFactory\ComponentEngine\Runtime\Expression;

final class OnConstant
{
    /**
     * @param Runtime $runtime
     * @param Constant $constant
     * @return void
     */
    public static function evaluate(Runtime $runtime, Constant $constant) 
    {
        $value = $constant->getValue();
    
        if ($value instanceof Tag) {
            return Afx\OnTag::evaluate($runtime, $value);
        } else {
            return Expression\OnExpression::evaluate($runtime, $value);
        }
    }
}
