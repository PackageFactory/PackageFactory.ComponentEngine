<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ObjectLiteralProperty implements \JsonSerializable
{
    /**
     * @var null|Identifier|Operand
     */
    private $key;

    /**
     * @var Operand
     */
    private $value;

    /**
     * @param null|Identifier|Operand $key
     * @param Operand $value
     */
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
        if ($value === null) {
            throw new \Exception('@TODO: Unexpected empty value');
        }

        return new self($key, $value);
    }

    /**
     * @return null|Identifier|Operand
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