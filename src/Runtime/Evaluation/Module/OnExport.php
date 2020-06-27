<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Export;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

final class OnExport
{
    /**
     * @param Runtime $runtime
     * @param Export $export
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Export $export) 
    {
        $value = $export->getValue();
    
        if ($value instanceof Tag) {
            return Afx\OnTag::evaluate($runtime, $value);
        } else {
            return Expression\OnExpression::evaluate($runtime, $value);
        }
    }
}

