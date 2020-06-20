<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Util;

final class NumberLiteral implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var float
     */
    private $number;

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

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $value = $stream->current();
        if ($value->getType() === TokenType::NUMBER()) {
            $stream->next();
            return new self($value);
        } else {
            throw new \Exception('@TODO: Unexpected Token: ' . $value);
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
    public function evaluate(): float
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