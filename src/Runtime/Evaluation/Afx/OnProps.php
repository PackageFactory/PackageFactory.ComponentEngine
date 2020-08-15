<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnProps
{
    /**
     * @param Runtime $runtime
     * @param array<int, ParameterAssignment> $props
     * @return \Iterator<int|string, ValueInterface<mixed>>
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

