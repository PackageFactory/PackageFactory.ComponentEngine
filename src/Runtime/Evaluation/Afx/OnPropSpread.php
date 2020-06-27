<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnPropSpread
{
    /**
     * @param Runtime $runtime
     * @param Spread $propSpread
     * @return array
     */
    public static function evaluate(Runtime $runtime, Spread $propSpread): array 
    {
        throw new \Exception('@TODO: onPropSpread');
    }
}

