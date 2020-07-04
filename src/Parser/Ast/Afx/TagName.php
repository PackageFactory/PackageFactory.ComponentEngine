<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class TagName implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->getType() === TokenType::IDENTIFIER()) {
            $stream->next();
            return new self($value->getValue());
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::IDENTIFIER()]
            );
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}