<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Attribute implements \JsonSerializable
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
     * @var AttributeName
     */
    private $name;

    /**
     * @var bool|Expression|StringLiteral
     */
    private $value;

    /**
     * @param Token $start
     * @param Token $end
     * @param AttributeName $name
     * @param bool|Expression|StringLiteral $value
     */
    public function __construct(
        Token $start,
        Token $end,
        AttributeName $name,
        $value
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->name = $name;

        if (is_bool($value)) {
            $this->value = $value;
        }
        elseif ($value instanceof Expression) {
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
        $name = AttributeName::createFromTokenStream($stream);
        $value = true;

        if ($stream->current()->getType() === TokenType::ASSIGNMENT()) {
            $stream->next();

            switch ($stream->current()->getType()) {
                case TokenType::EXPRESSION_START():
                    $value = Expression::createFromTokenStream($stream);
                break;
                
                case TokenType::STRING_START():
                    $value = StringLiteral::createFromTokenStream($stream);
                break;

                default:
                    throw new \Exception('@TODO: Unexpected Token');
            }
        }
    
        return new self(
            $start,
            $stream->current(),
            $name,
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
     * @return AttributeName
     */
    public function getName(): AttributeName
    {
        return $this->name;
    }

    /**
     * @return bool|Expression|StringLiteral
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
            'type' => 'Attribute',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'name' => $this->name,
                'value' => $this->value
            ]
        ];
    }
}