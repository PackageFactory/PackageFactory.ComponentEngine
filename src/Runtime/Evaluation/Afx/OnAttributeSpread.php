<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnAttributeSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $attributeSpread
     * @return \Iterator<int, mixed>
     */
    public static function evaluate(Runtime $runtime, Spread $attributeSpread): \Iterator 
    {
        throw new \Exception('@TODO: onAttributeSpread');
    }
}

