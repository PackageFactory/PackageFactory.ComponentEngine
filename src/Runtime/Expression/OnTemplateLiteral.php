<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTemplateLiteral
{
    /**
     * @param Runtime $runtime
     * @param TemplateLiteral $templateLiteral
     * @return string
     */
    public static function evaluate(Runtime $runtime, TemplateLiteral $templateLiteral): string 
    {
        throw new \Exception('@TODO: onTemplateLiteral');
    }
}

