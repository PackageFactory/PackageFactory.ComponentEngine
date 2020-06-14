<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluate;

use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Expression
{
    /**
     * @param Context $context
     * @param Ast\Expression $expression
     * @return null|array|string|mixed
     */
    public static function evaluate(Context $context, Ast\Expression $expression)
    {
        if ($expression->getRoot() instanceof Ast\Negation) {
            throw new \RuntimeException('@TODO: Negation Expression');
        }
        elseif ($expression->getRoot() instanceof Ast\Chain) {
            return $context->evaluateChain($expression->getRoot());
        }
        elseif ($expression->getRoot() instanceof Ast\ObjectLiteral) {
            return self::evaluateObjectLiteral($context, $expression->getRoot());
        }
        elseif ($expression->getRoot() instanceof Ast\StringLiteral) {
            return (string) $expression->getRoot();
        }
        elseif ($expression->getRoot() instanceof Ast\Spread) {
            throw new \RuntimeException('@TODO: Spread Expression');
        }
        elseif ($expression->getRoot() === null) {
            return null;
        }
        else {
            throw new \RuntimeException('@TODO: Invalid Expression');
        }
    }

    /**
     * @param Context $context
     * @param Ast\ObjectLiteral $objectLiteral
     * @return array<string, mixed>
     */
    public static function evaluateObjectLiteral(
        Context $context, 
        Ast\ObjectLiteral $objectLiteral
    ): array {
        $result = [];

        foreach ($objectLiteral->getProperties() as $property) {
            /** @var Ast\Property $property */
            $key = null;
            if ($property->getKey() instanceof Ast\Identifier) {
                $key = (string) $property->getKey();
            }
            elseif ($property->getKey() instanceof Ast\Chain) {
                $key = $context->evaluateChain($property->getKey());
                if (!is_string($key)) {
                    throw new \RuntimeException('@TODO: property key must be string');
                }
            }
            elseif ($property->getKey() instanceof Ast\StringLiteral) {
                $key = (string) $property->getKey();
            }
            else {
                throw new \RuntimeException('@TODO: Invalid property key');
            }

            $value = null;
            if ($property->getValue() instanceof Ast\Negation) {
                throw new \RuntimeException('@TODO: Property value Negation');
            }
            elseif ($property->getValue() instanceof Ast\Chain) {
                $value = $context->evaluateChain($property->getValue());
            }
            elseif ($property->getValue() instanceof Ast\ObjectLiteral) {
                $value = self::evaluateObjectLiteral($context, $property->getValue());
            }
            elseif ($property->getValue() instanceof Ast\StringLiteral) {
                $value = (string) $property->getValue();
            }
            else {
                throw new \RuntimeException('@TODO: Invalid property value');
            }

            $result[$key] = $value;
        }
        
        return $result;
    }
}