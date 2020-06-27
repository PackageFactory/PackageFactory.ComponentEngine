<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

final class OnPropSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $propSpread
     * @return \Iterator<int|string, mixed>
     */
    public static function evaluate(Runtime $runtime, Spread $propSpread): \Iterator 
    {
        yield from Expression\OnSpread::evaluate($runtime, $propSpread);
    }
}

