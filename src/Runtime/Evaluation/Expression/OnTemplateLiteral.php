<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnTemplateLiteral
{
    /**
     * @param Runtime $runtime
     * @param TemplateLiteral $templateLiteral
     * @return string
     */
    public static function evaluate(Runtime $runtime, TemplateLiteral $templateLiteral): string 
    {
        $result = '';

        foreach ($templateLiteral->getSegments() as $segment) {
            if (is_string($segment)) {
                $result .= $segment;
            } else {
                $value = OnTerm::evaluate($runtime, $segment);

                if (is_string($value)) {
                    $result .= $value;
                } elseif (is_numeric($value)) {
                    $valueAsInt = intval($value);
                    if ($valueAsInt == $value) {
                        $result .= $valueAsInt;
                    } else {
                        $result .= $value;
                    }
                } elseif (is_array($value)) {
                    $valueFlattened = [];
                    array_walk_recursive($value, function($item) use (&$valueFlattened) { 
                        $valueFlattened[] = $item; 
                    });
                    $result .= join(',', $valueFlattened);
                } elseif (is_bool($value)) {
                    $result .= $value ? 'true' : 'false';
                } elseif ($value === null) {
                    $result .= 'null';
                } else {
                    throw new \RuntimeException('@TODO: Failed string Conversion: ' . gettype($value));
                }
            }
        }

        return $result;
    }
}

