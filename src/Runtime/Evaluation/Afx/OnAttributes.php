<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnAttributes
{
    /**
     * Undocumented function
     *
     * @param Runtime $runtime
     * @param array<int, ParameterAssignment> $attributes
     * @return \Iterator<int, mixed>
     */
    public static function evaluate(Runtime $runtime, array $attributes): \Iterator
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Attribute) {
                yield from OnAttribute::evaluate($runtime, $attribute);
            } elseif ($attribute instanceof Spread) {
                yield from OnAttributeSpread::evaluate($runtime, $attribute);
            }
        }
    }
}

