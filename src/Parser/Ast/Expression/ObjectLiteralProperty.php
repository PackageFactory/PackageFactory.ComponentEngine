<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\ContextEvaluatorInterface;

final class ObjectLiteralProperty implements \JsonSerializable, ContextEvaluatorInterface
{
    /**
     * @var null|Identifier|Operand
     */
    private $key;

    /**
     * @var Operand
     */
    private $value;

    private function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public static function createFromTokenStream(TokenStream $stream): self
    {
        $key = null;
        $value = null;

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        switch ($stream->current()->getType()) {                
            case TokenType::IDENTIFIER():
                $key = Identifier::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_SQUARE_OPEN():
                $stream->next();
                $key = Expression::createFromTokenStream($stream);
                Util::skipWhiteSpaceAndComments($stream);
                Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                break;
            case TokenType::OPERATOR_SPREAD():
                $value = Spread::createFromTokenStream($stream);
                return new self(null, $value);

            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::COLON());

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $value = Expression::createFromTokenStream($stream);

        return new self($key, $value);
    }

    /**
     * @return Identifier|Operand
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return Operand
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function evaluateKey(Context $context): string
    {
        if ($this->key instanceof Identifier) {
            return $this->key->getValue();
        } else {
            return $this->key->evaluate($context);
        }
    }

    /**
     * @return \Iterator<mixed>
     */
    public function evaluate(Context $context = null): \Iterator
    {
        if ($this->value instanceof Spread) {
            foreach ($this->value->evaluate($context) as $key => $value) {
                if ($value !== null) {
                    yield $key => $value;
                }
            }
        } else {
            $value = $this->value->evaluate($context);
            if ($value !== null) {
                if ($this->key === null) {
                    var_dump($value); die;
                }
                elseif ($this->key instanceof Identifier) {
                    yield $this->key->getValue() => $value;
                } else {
                    yield $this->key->evaluate($context) => $value;
                }
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}