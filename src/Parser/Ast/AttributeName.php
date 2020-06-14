<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class AttributeName implements \JsonSerializable
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
     * @var string
     */
    private $value;

    /**
     * @param Token $start
     * @param Token $end
     * @param string $value
     */
    private function __construct(
        Token $start,
        Token $end,
        string $value
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        
        $start = $stream->current();
        $end = $stream->current();
        
        $value = null;
        if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
            $value = $stream->current()->getValue();
            $stream->next();
        } else {
            throw new \Exception('@TODO: Unexpected Token');
        }

        if ($stream->current()->getType() === TokenType::COLON()) {
            $value .= $stream->current()->getValue();
            $stream->next();

            if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                $value .= $stream->current()->getValue();
                $end = $stream->current();
                $stream->next();
            }
            else {
                throw new \Exception('@TODO: Unexpected Token');
            }
        }

        return new self($start, $end, $value);
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
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getNameSpace(): ?string
    {
        if (strpos($this->value, ':') === false) {
            return null;
        }
        else {
            list($namespace) = explode(':', $this->value);
            return $namespace;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'AttributeName',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'value' => $this->value
            ]
        ];
    }
}