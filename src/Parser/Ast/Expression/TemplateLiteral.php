<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Literal;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class TemplateLiteral implements Literal, Term, Statement, Key, Child, \JsonSerializable
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
     * @var array|(string|Term)[]
     */
    private $segments;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|(string|Term)[] $segments
     */
    private function __construct(
        Token $start,
        Token $end,
        array $segments
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->segments = $segments;
    }

    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $stream->current();
        $stream->consume(TokenType::TEMPLATE_LITERAL_START());

        $segments = [];
        $string = '';
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::TEMPLATE_LITERAL_CONTENT():
                    $string .= $stream->current()->getValue();
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_ESCAPE():
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER():
                    $string .= $stream->current()->getValue();
                    $stream->next();
                    break;
                case TokenType::TEMPLATE_LITERAL_INTERPOLATION_START():
                    if (!empty($string)) {
                        $segments[] = $string;
                        $string = '';
                    }
                    $stream->next();
                    $segments[] = ExpressionParser::parseTerm(
                        $stream,
                        ExpressionParser::PRIORITY_TERNARY
                    );
                    $stream->consume(TokenType::TEMPLATE_LITERAL_INTERPOLATION_END());
                    break;

                case TokenType::TEMPLATE_LITERAL_END():
                    if (!empty($string)) {
                        $segments[] = $string;
                        $string = '';
                    }
                    $end = $stream->current();
                    $stream->next();
                    return new self($start, $end, $segments);

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::TEMPLATE_LITERAL_CONTENT(),
                            TokenType::TEMPLATE_LITERAL_ESCAPE(),
                            TokenType::TEMPLATE_LITERAL_ESCAPED_CHARACTER(),
                            TokenType::TEMPLATE_LITERAL_INTERPOLATION_START(),
                            TokenType::TEMPLATE_LITERAL_END()
                        ]
                    );
            }
        }

        throw ParserFailed::becauseOfUnexpectedEndOfFile($stream);
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
     * @return array|(string|Term)[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'TemplateLiteral',
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'segments' => $this->segments
        ];
    }
}