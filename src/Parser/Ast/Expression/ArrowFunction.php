<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ArrowFunction implements Term, Statement, \JsonSerializable
{
    /**
     * @var array|Identifier[]
     */
    private $parameters;

    /**
     * @var Term
     */
    private $body;

    /**
     * @param array|Identifier[] $parameters
     * @param Term $body
     */
    private function __construct(
        array $parameters,
        Term $body
    ) {
        $this->parameters = $parameters;
        $this->body = $body;
    }

    /**
     * @param null|Identifier $firstParameter
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(
        ?Identifier $firstParameter, 
        TokenStream $stream
    ): self {
        if ($firstParameter === null) {
            $parameters = [];
        } else {
            $parameters = [$firstParameter];
        }

        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
                $stream->skipWhiteSpaceAndComments();
            } elseif ($stream->current()->getType() === TokenType::BRACKETS_ROUND_CLOSE()) {
                $stream->next();
                break;
            } elseif ($stream->current()->getType() === TokenType::ARROW()) {
                $stream->next();
                break;
            }

            switch ($stream->current()->getType()) {
                case TokenType::IDENTIFIER():
                    $parameters[] = Identifier::fromTokenStream($stream);
                    break;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [TokenType::IDENTIFIER()]
                    );
            }
        }

        $stream->skipWhiteSpaceAndComments();

        return new self(
            $parameters, 
            ExpressionParser::parseTerm($stream)
        );
    }

    /**
     * @return array|Identifier[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return Term
     */
    public function getBody(): Term
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