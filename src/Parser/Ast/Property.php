<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Property implements \JsonSerializable
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
     * @var Identifier|Chain|StringLiteral
     */
    private $key;

    /**
     * @var Negation|Chain|ObjectLiteral|StringLiteral
     */
    private $value;

    /**
     * @param Token $start
     * @param Token $end
     * @param Identifier|Chain|StringLiteral $key
     * @param Negation|Chain|ObjectLiteral|StringLiteral $value
     */
    public function __construct(
        Token $start,
        Token $end,
        $key,
        $value
    ) {
        $this->start = $start;
        $this->end = $end;

        if ($key instanceof Identifier) {
            $this->key = $key;
        }
        elseif ($key instanceof Chain) {
            $this->key = $key;
        }
        elseif ($key instanceof StringLiteral) {
            $this->key = $key;
        }
        else {
            var_dump(gettype($key));
            var_dump(get_class($key));
            throw new \Exception('@TODO: Exception');
        }

        if ($value instanceof Negation) {
            $this->value = $value;
        }
        elseif ($value instanceof Chain) {
            $this->value = $value;
        }
        elseif ($value instanceof ObjectLiteral) {
            $this->value = $value;
        }
        elseif ($value instanceof StringLiteral) {
            $this->value = $value;
        }
        else {
            throw new \Exception('@TODO: Exception');
        }
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        $key = null;
        switch ($stream->current()->getType()) {
            case TokenType::IDENTIFIER():
                $key = Identifier::createFromTokenStream($stream);
            break;

            case TokenType::STRING_START():
                $key = StringLiteral::createFromTokenStream($stream);
            break;

            case TokenType::COMPUTED_KEY_START():
                $stream->next();
                $key = Chain::createFromTokenStream($stream);
                Util::expect($stream, TokenType::COMPUTED_KEY_END());
            break;

            default:
                throw new \Exception('@TODO: Unexpected Token');
        }

        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::ASSIGNMENT());
        Util::skipWhiteSpaceAndComments($stream);

        $value = null;
        switch ($stream->current()->getType()) {
            case TokenType::EXCLAMATION():
                $value = Negation::createFromTokenStream($stream);
            break;

            case TokenType::IDENTIFIER():
                $value = Chain::createFromTokenStream($stream);
            break;

            case TokenType::OBJECT_START():
                $value = ObjectLiteral::createFromTokenStream($stream);
            break;

            case TokenType::STRING_START():
                $value = StringLiteral::createFromTokenStream($stream);
            break;

            default:
                throw new \Exception('@TODO: Unexpected Token');
        }

        return new self(
            $start,
            $stream->current(),
            $key,
            $value
        );
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
     * @return Identifier|Chain|StringLiteral
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return Negation|Chain|ObjectLiteral|StringLiteral
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
            'type' => 'Property',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'key' => $this->key,
                'value' => $this->value
            ]
        ];
    }
}