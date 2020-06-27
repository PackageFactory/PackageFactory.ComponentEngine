<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnIdentifier
{
    /**
     * @param Runtime $runtime
     * @param Identifier $identifier
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Identifier $identifier)
    {
        return $runtime->getContext()->getProperty($identifier->getValue());
    }
}

