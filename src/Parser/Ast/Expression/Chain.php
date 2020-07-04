<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Spreadable;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Scope\Expression;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Chain implements Spreadable, Term, Statement, Key, Child, \JsonSerializable
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
     * @var Term
     */
    private $root;

    /**
     * @var array|ChainSegment[]
     */
    private $segments;

    /**
     * @param Token $start
     * @param Token $end
     * @param array|ChainSegment[] $segments
     */
    private function __construct(
        Token $start,
        Token $end,
        Term $root,
        array $segments
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->root = $root;
        $this->segments = $segments;
    }

    /**
     * @param Term $root
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(
        Term $root,
        TokenStream $stream
    ): self {
        $start = $stream->current();
        $end = $start;
        
        $segments = [];
        $optional = false;
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                break;
            }

            switch ($stream->current()->getType()) {
                case TokenType::OPERATOR_OPTCHAIN():
                        $end = $stream->current();
                        $optional = true;
                        $stream->next();
                    break;
                case TokenType::PERIOD():
                    $end = $stream->current();
                    $optional = false;
                    $stream->next();
                    break;
                case TokenType::BRACKETS_SQUARE_OPEN():
                case TokenType::BRACKETS_ROUND_OPEN():
                    $optional = false;
                    break;
                default:
                    break 2;
            }

            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_SQUARE_OPEN():
                    $end = $stream->current();
                    $stream->next();
                    $key = ExpressionParser::parseTerm($stream);
                    if ($key instanceof Key) {
                        $segments[] = ChainSegment::createFromKey($optional, $key);
                        Util::skipWhiteSpaceAndComments($stream);
                        Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                    } else {
                        throw new \Exception('@TODO: Unexpected Term: ' . get_class($key));
                    }
                    break;
                
                case TokenType::BRACKETS_ROUND_OPEN():
                    $end = $stream->current();
                    $segment = array_pop($segments);
                    $segments[] = $segment->withCall(
                        Call::createFromTokenStream($stream)
                    );
                    break;

                case TokenType::IDENTIFIER():
                    $end = $stream->current();
                    $segments[] = ChainSegment::createFromKey(
                        $optional, 
                        Identifier::createFromTokenStream($stream)
                    );
                    break;
                
                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        return new self($start, $end, $root, $segments);
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
     * @return Term
     */
    public function getRoot(): Term
    {
        return $this->root;
    }

    /**
     * @return array|ChainSegment[]
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
            'type' => 'Chain',
            'offset' => [
                $this->start->getStart()->getIndex(),
                $this->end->getEnd()->getIndex()
            ],
            'root' => $this->root,
            'segments' => $this->segments
        ];
    }
}