<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Value;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Util;

final class NumberLiteral implements Value, Literal, Term, Statement, Key, Child, \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var float
     */
    private $number;

    /**
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;

        $value = $token->getValue();
        switch (mb_substr($value, 0, 2)) {
            case '0b':
            case '0B':
                $this->number = bindec(mb_substr($value, 2));
                break;

            case '0o':
                $this->number = octdec(mb_substr($value, 2));
                break;

            case '0x':
                $this->number = hexdec(mb_substr($value, 2));
                break;
            
            default:
                $this->number = floatval($value);
                break;
        }
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        Util::ensureValid($stream);

        $value = $stream->current();
        if ($value->getType() === TokenType::NUMBER()) {
            $stream->next();
            return new self($value);
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::NUMBER()]
            );
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->token->getValue();
    }
    
    /**
     * @return float
     */
    public function getNumber(): float
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->token->getValue();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'NumberLiteral',
            'offset' => [
                $this->token->getStart()->getIndex(),
                $this->token->getEnd()->getIndex()
            ],
            'value' => $this->token->getValue()
        ];
    }
}