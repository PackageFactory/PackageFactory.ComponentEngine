<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Call implements \JsonSerializable
{
    /**
     * @var array|Term[]
     */
    private $arguments;

    /**
     * @param array|Term[] $arguments
     */
    private function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        Util::expect($stream, TokenType::BRACKETS_ROUND_OPEN());

        $arguments = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);
            
            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_ROUND_CLOSE():
                    $stream->next();
                    return new self($arguments);
                    
                default:
                    $arguments[] = ExpressionParser::parseTerm($stream);
                    break;
            }

            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
            } else {
                Util::skipWhiteSpaceAndComments($stream);
                Util::expect($stream, TokenType::BRACKETS_ROUND_CLOSE());
                break;
            }
        }

        return new self($arguments);
    }

    /**
     * @return array|Term[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Call',
            'arguments' => $this->arguments
        ];
    }
}