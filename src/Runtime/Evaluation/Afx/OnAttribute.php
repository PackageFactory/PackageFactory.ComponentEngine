<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model as VirtualDOM;

final class OnAttribute
{
    /**
     * @param Runtime $runtime
     * @param Attribute $attribute
     * @return \Iterator<string, mixed>
     */
    public static function evaluate(Runtime $runtime, Attribute $attribute): \Iterator 
    {
        $name = $attribute->getAttributeName()->getValue();
        $value = $attribute->getValue();

        if (is_null($value)) {
            yield $name => VirtualDOM\Attribute::fromNameAndValue($name, true);
        } else {
            yield $name => VirtualDOM\Attribute::fromNameAndValue(
                $name,
                Expression\OnTerm::evaluate($runtime, $value)
            );
        }
    }
}

