<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ArrowFunction implements \JsonSerializable
{
    /**
     * @var array|Identifier[]
     */
    private $parameters;

    /**
     * @var Operand
     */
    private $body;

    /**
     * @param array $parameters
     * @param Operand $body
     */
    private function __construct(
        array $parameters,
        $body
    ) {
        $this->parameters = $parameters;
        $this->body = $body;
    }

    /**
     * @param null|Identifier
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(?Identifier $firstParameter, TokenStream $stream): self
    {
        if ($firstParameter === null) {
            $parameters = [];
        } else {
            $parameters = [$firstParameter];
        }

        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            } elseif ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
                Util::skipWhiteSpaceAndComments($stream);
            } elseif ($stream->current()->getType() === TokenType::BRACKETS_ROUND_CLOSE()) {
                $stream->next();
                break;
            } elseif ($stream->current()->getType() === TokenType::ARROW()) {
                $stream->next();
                break;
            }

            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            }

            switch ($stream->current()->getType()) {
                case TokenType::IDENTIFIER():
                    $parameters[] = Identifier::createFromTokenStream($stream);
                    break;

                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $body = Expression::createFromTokenStream($stream);

        return new self($parameters, $body);
    }

    /**
     * @return array|Identifier[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return Operand
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ArrowFunction',
            'parameters' => $this->parameters,
            'body' => $this->body
        ];
    }
}