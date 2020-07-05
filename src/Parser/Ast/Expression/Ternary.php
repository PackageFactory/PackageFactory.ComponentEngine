<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Ternary implements Spreadable, Term, Statement, Child, \JsonSerializable
{
    /**
     * @var Term
     */
    private $condition;

    /**
     * @var Term
     */
    private $trueBranch;

    /**
     * @var Term
     */
    private $falseBranch;

    /**
     * @param Term $condition
     * @param Term $trueBranch
     * @param Term $falseBranch
     */
    private function __construct(
        Term $condition,
        Term $trueBranch,
        Term $falseBranch
    ) {
        $this->condition = $condition;
        $this->trueBranch = $trueBranch;
        $this->falseBranch = $falseBranch;
    }

    /**
     * @param Term $condition
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(Term $condition, TokenStream $stream): self
    {
        $stream->consume(TokenType::QUESTIONMARK());
        $stream->skipWhiteSpaceAndComments();

        $trueBranch = ExpressionParser::parseTerm($stream);

        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::COLON());
        $stream->skipWhiteSpaceAndComments();

        $falseBranch = ExpressionParser::parseTerm($stream);

        return new self($condition, $trueBranch, $falseBranch);
    }

    /**
     * @return Term
     */
    public function getCondition(): Term
    {
        return $this->condition;
    }

    /**
     * @return Term
     */
    public function getTrueBranch(): Term
    {
        return $this->trueBranch;
    }

    /**
     * @return Term
     */
    public function getFalseBranch(): Term
    {
        return $this->falseBranch;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Ternary',
            'condition' => $this->condition,
            'trueBranch' => $this->trueBranch,
            'falseBranch' => $this->falseBranch
        ];
    }
}