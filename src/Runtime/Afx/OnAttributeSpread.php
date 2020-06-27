<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnAttributeSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $attributeSpread
     * @return \Iterator<string, mixed>
     */
    public static function evaluate(Runtime $runtime, Spread $attributeSpread): \Iterator 
    {
        throw new \Exception('@TODO: onAttributeSpread');
    }
}

