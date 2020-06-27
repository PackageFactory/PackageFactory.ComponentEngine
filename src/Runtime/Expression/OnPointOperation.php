<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnPointOperation
{
    /**
     * @param Runtime $runtime
     * @param PointOperation $pointOperation
     * @return float
     */
    public static function evaluate(Runtime $runtime, PointOperation $pointOperation): float 
    {
        throw new \Exception('@TODO: onPointOperation');
    }
}

