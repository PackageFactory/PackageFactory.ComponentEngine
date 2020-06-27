<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Evaluation\ExpressionEvaluatorInterface;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrayLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ArrowFunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Call;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Chain;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Comparison;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Conjunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\DashOperation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Disjunction;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Negation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\NullLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\ObjectLiteralProperty;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\PointOperation;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\TemplateLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Ternary;

/**
 * @implements ExpressionEvaluatorInterface<string|array|object|bool|float|null>
 */
final class ExpressionEvaluator implements ExpressionEvaluatorInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     * @return void
     */
    private function __construct(Context $context = null)
    {
        $this->context = $context ?? Context::createEmpty();
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * @param Context $context
     * @return self
     */
    public function withContext(Context $context): self
    {
        return new self($context);
    }

    /**
     * @param Operand $name
     * @return string|array<mixed>|object|int|bool|float|null
     */
    public function evaluate($root)
    {
        $context = $this->context;
        return $this->onOperand($root, $context);
    }

    /**
     * @param Operand $operand
     * @param Context $context
     * @return string|array<mixed>|object|int|bool|float|null
     */
    public function onOperand($operand, Context $context)
    {
        if ($operand instanceof NullLiteral) {
            return $this->onNullLiteral($operand);
        } elseif ($operand instanceof BooleanLiteral) {
            return $this->onBooleanLiteral($operand);
        } elseif ($operand instanceof NumberLiteral) {
            return $this->onNumberLiteral($operand);
        } elseif ($operand instanceof StringLiteral) {
            return $this->onStringLiteral($operand);
        } elseif ($operand instanceof TemplateLiteral) {
            return $this->onTemplateLiteral($operand, $context);
        } elseif ($operand instanceof ArrayLiteral) {
            return $this->onArrayLiteral($operand, $context);
        } elseif ($operand instanceof ObjectLiteral) {
            return $this->onObjectLiteral($operand, $context);
        } elseif ($operand instanceof Negation) {
            return $this->onNegation($operand, $context);
        } elseif ($operand instanceof Spread) {
            return $this->onSpread($operand, $context);
        } elseif ($operand instanceof Conjunction) {
            return $this->onConjunction($operand, $context);
        } elseif ($operand instanceof Disjunction) {
            return $this->onDisjunction($operand, $context);
        } elseif ($operand instanceof PointOperation) {
            return $this->onPointOperation($operand, $context);
        } elseif ($operand instanceof DashOperation) {
            return $this->onDashOperation($operand, $context);
        } elseif ($operand instanceof Comparison) {
            return $this->onComparison($operand, $context);
        } elseif ($operand instanceof Ternary) {
            return $this->onTernary($operand, $context);
        } elseif ($operand instanceof ArrowFunction) {
            return $this->onArrowFunction($operand, $context);
        } elseif ($operand instanceof Chain) {
            return $this->onChain($operand, $context);
        } else {
            throw new \RuntimeException('@TODO: Illegal Operand ' . get_class($operand));
        }
    }

    /**
     * @param ArrayLiteral $arrayLiteral
     * @param Context $context
     * @return array<mixed>
     */
    public function onArrayLiteral(ArrayLiteral $arrayLiteral, Context $context): array
    {
        $result = [];

        foreach ($arrayLiteral->getItems() as $item) {
            if ($item instanceof Spread) {
                $index = 0;
                foreach ($this->onSpread($item, $context) as $key => $value) {
                    if ($key === $index) {
                        $result[] = $value;
                        $index++;
                    } else {
                        throw new \RuntimeException('@TODO: Cannot spread non-numerical array');
                    }
                }
            } else {    
                $result[] = $this->onOperand($item, $context);
            }
        }

        return $result;
    }

    /**
     * @param ArrowFunction $arrowFunction
     * @param Context $context
     * @return array<mixed>
     */
    public function onArrowFunction(ArrowFunction $arrowFunction, Context $context): array
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onArrowFunction() is not implemented yet!');
    }

    /**
     * @param BooleanLiteral $booleanLiteral
     * @return bool
     */
    public function onBooleanLiteral(BooleanLiteral $booleanLiteral): bool
    {
        $value = $booleanLiteral->getValue();

        if ($value === 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Chain $chain
     * @param Context $context
     * @return null|mixed
     */
    public function onChain(Chain $chain, Context $context)
    {
        $segments = $chain->getSegments();
        $root = array_shift($segments);

        if ($root->getSubject() instanceof Identifier) {
            array_unshift($segments, $root);
            $value = $context;
        } else {
            $value = $root->evaluate($context);
        }

        foreach ($segments as $segment) {
            $key = $this->withContext($context)->evaluate($segment);
            if (!is_scalar($key)) {
                throw new \RuntimeException('@TODO: Invalid key');
            }

            if ($value instanceof Context) {
                if (!is_string($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif ($value->hasProperty($key)) {
                    $value = $value->getProperty($key);
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_string($value)) {
                if (!is_numeric($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif ($key < mb_strlen($value)) {
                    $value = $value[$key];
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_array($value)) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } elseif ($key === 'map') {
                    $items = $value;
                    $value = function(callable $callback) use ($items) {
                        $result = [];
                        foreach ($items as $item) {
                            $result[] = $callback($item);
                        }
                        return $result;
                    };
                } elseif ($segment->getIsOptional()) {
                    return null;
                } else {
                    throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                }
            } elseif (is_object($value)) {
                if (!is_string($key)) {
                    throw new \RuntimeException('@TODO: Invalid key');
                } elseif (isset($value->{ $key })) {
                    return $value->{ $key };
                } else {
                    $getter = 'get' . ucfirst($key);

                    if (is_callable([$value, $getter])) {
                        try {
                            return $value->{ $getter }();
                        } catch (\Throwable $err) {
                            throw new \RuntimeException('@TODO: An error occured during PHP execution: ' . $err->getMessage());
                        }
                    } elseif ($segment->getIsOptional()) {
                        return null;
                    } else {
                        throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
                    }
                }
            } else {
                throw new \RuntimeException('@TODO: Invalid value type: ' . gettype($value));
            }
        }

        return $value;
    }

    /**
     * @param Comparison $comparison
     * @param Context $context
     * @return bool
     */
    public function onComparison(Comparison $comparison, Context $context): bool
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onComparison() is not implemented yet!');
    }

    /**
     * @param Conjunction $conjunction
     * @param Context $context
     * @return mixed
     */
    public function onConjunction(Conjunction $conjunction, Context $context)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onConjunction() is not implemented yet!');
    }

    /**
     * @param DashOperation $dashOperation
     * @param Context $context
     * @return float|string
     */
    public function onDashOperation(DashOperation $dashOperation, Context $context)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onDashOperation() is not implemented yet!');
    }

    /**
     * @param Disjunction $disjunction
     * @param Context $context
     * @return mixed
     */
    public function onDisjunction(Disjunction $disjunction, Context $context)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onDisjunction() is not implemented yet!');
    }

    /**
     * @param Negation $negation
     * @param Context $context
     * @return bool
     */
    public function onNegation(Negation $negation, Context $context): bool
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onNegation() is not implemented yet!');
    }

    /**
     * @param NullLiteral $nullLiteral
     * @return null
     */
    public function onNullLiteral(NullLiteral $nullLiteral)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onNullLiteral() is not implemented yet!');
    }

    /**
     * @param NumberLiteral $numberLiteral
     * @return float
     */
    public function onNumberLiteral(NumberLiteral $numberLiteral)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onNumberLiteral() is not implemented yet!');
    }

    /**
     * @param ObjectLiteral $objectLiteral
     * @param Context $context
     * @return \stdClass
     */
    public function onObjectLiteral(ObjectLiteral $objectLiteral, Context $context): \stdClass
    {
        $properties = [];
        foreach ($objectLiteral->getProperties() as $property) {
            foreach ($this->onObjectLiteralProperty($property, $context) as $key => $value) {
                $properties[$key] = $value;
            }
        }

        return (object) $properties;
    }

    /**
     * @param ObjectLiteral $objectLiteral
     * @param Context $context
     * @return \Iterator<string, mixed>
     */
    public function onObjectLiteralProperty(
        ObjectLiteralProperty $objectLiteralProperty, 
        Context $context
    ): \Iterator {
        $value = $objectLiteralProperty->getValue();

        if ($value instanceof Spread) {
            foreach ($this->onSpread($value, $context) as $key => $value) {
                if ($value !== null) {
                    yield $key => $value;
                }
            }
        } else {
            $value = $this->onOperand($value, $context);
            
            if ($value !== null) {
                $key = $objectLiteralProperty->getKey();

                if ($key === null) {
                    throw new \RuntimeException('@TODO: Object key cannot be null.');
                } elseif ($key instanceof Identifier) {
                    yield $key->getValue() => $value;
                } else {
                    yield $key->evaluate($context) => $value;
                }
            }
        }
    }

    /**
     * @param PointOperation $pointOperation
     * @param Context $context
     * @return float
     */
    public function onPointOperation(PointOperation $pointOperation, Context $context): float
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onPointOperation() is not implemented yet!');
    }

    /**
     * @param Spread $spread
     * @param Context $context
     * @return \Iterator<string, mixed>|\Iterator<int, mixed>
     */
    public function onSpread(Spread $spread, Context $context): \Iterator
    {
        $subject = $this->onOperand($spread->getSubject(), $context);

        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                yield $key => $value;
            }
        } else if (is_object($subject)) {
            foreach ((array) $subject as $key => $value) {
                yield $key => $value;
            }
        } else {
            throw new \RuntimeException('@TODO: Cannot spread value of type ' . gettype($subject));
        }
    }

    /**
     * @param StringLiteral $stringLiteral
     * @return string
     */
    public function onStringLiteral(StringLiteral $stringLiteral): string
    {
        return $stringLiteral->getValue();
    }

    /**
     * @param TemplateLiteral $templateLiteral
     * @param Context $context
     * @return string
     */
    public function onTemplateLiteral(TemplateLiteral $templateLiteral, Context $context): string
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onTemplateLiteral() is not implemented yet!');
    }

    /**
     * @param Ternary $ternary
     * @param Context $context
     * @return mixed
     */
    public function onTernary(Ternary $ternary, Context $context)
    {
        throw new \Exception('@TODO: ExpressionEvaluator->onTernary() is not implemented yet!');
    }
}