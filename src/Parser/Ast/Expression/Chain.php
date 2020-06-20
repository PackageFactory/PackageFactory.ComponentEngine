<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Chain implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $start;

    /**
     * @var Token
     */
    private $end;

    /**
     * @var array<int, ChainSegment>
     */
    private $segments;

    /**
     * @param Token $start
     * @param Token $end
     * @param array<int, ChainSegment> $segments
     */
    private function __construct(
        Token $start,
        Token $end,
        array $segments
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->segments = $segments;
    }

    /**
     * @param Operand $root
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(
        $root,
        TokenStream $stream
    ): self {
        Util::skipWhiteSpaceAndComments($stream);
        $start = $stream->current();
        $end = $start;
        
        $segments = [];
        $operand = $root;
        $append = false;
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                break;
            }

            switch ($stream->current()->getType()) {
                case TokenType::OPERATOR_OPTCHAIN():
                    if ($operand !== null) {
                        $end = $stream->current();
                        $segments[] = ChainSegment::createFromOperand(true, $operand);
                        $stream->next();
                        $operand = null;
                        $append = true;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                case TokenType::PERIOD():
                    if ($operand !== null) {
                        $end = $stream->current();
                        $segments[] = ChainSegment::createFromOperand(false, $operand);
                        $stream->next();
                        $operand = null;
                        $append = false;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                case TokenType::BRACKETS_SQUARE_OPEN():
                    if ($operand !== null) {
                        $segments[] = ChainSegment::createFromOperand(false, $operand);
                    } else if (!$append) {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    $end = $stream->current();
                    $stream->next();
                    $operand = Expression::createFromTokenStream($stream);
                    Util::skipWhiteSpaceAndComments($stream);
                    Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                    $append = true;
                    break;

                case TokenType::IDENTIFIER():
                    if ($operand === null) {
                        $end = $stream->current();
                        $operand = Identifier::createFromTokenStream($stream);
                        $append = true;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                default:
                    if ($operand !== null) {
                        $segments[] = ChainSegment::createFromOperand(false, $operand);
                        return new self($start, $end, $segments);
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
            }
        }

        if ($operand !== null) {
            $segments[] = ChainSegment::createFromOperand(false, $operand);
        }

        return new self($start, $end, $segments);
    }

    /**
     * @return Token
     */
    public function getStart(): Token
    {
        return $this->start;
    }

    /**
     * @return Token
     */
    public function getEnd(): Token
    {
        return $this->end;
    }

    /**
     * @return array<int, ChainSegment>
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * @param Context $context
     * @return mixed
     */
    public function evaluate(Context $context = null)
    {
        if ($context === null) {
            throw new \Exception('@TODO: Cannot evaluate chain without context');
        }

        $segments = $this->segments;
        $root = array_shift($segments);

        if ($root->getSubject() instanceof Identifier) {
            array_unshift($segments, $root);
            $value = $context;
        } else {
            var_dump($root->getSubject());
            $value = $root->evaluate($context);
        }


        foreach ($segments as $segment) {
            $key = $segment->evaluate($context);
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
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Chain',
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'segments' => $this->segments
        ];
    }
}