<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnProps
{
    /**
     * @param Runtime $runtime
     * @param array $props
     * @return \Iterator<string, mixed>
     */
    public static function evaluate(Runtime $runtime, array $props): \Iterator 
    {
        foreach ($props as $prop) {
            if ($prop instanceof Attribute) {
                yield from OnProp::evaluate($runtime, $prop);
            } elseif ($prop instanceof Spread) {
                yield from OnPropSpread::evaluate($runtime, $prop);
            }
        }
    }
}

