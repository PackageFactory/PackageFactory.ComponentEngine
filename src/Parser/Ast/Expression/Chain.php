<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Chain implements \JsonSerializable
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
     * @var array<int, ChainSegment>
     */
    private $segments;

    /**
     * @param Token $start
     * @param Token $end
     * @param array<int, ChainSegment> $segments
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

    /**
     * @param Operand $root
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(
        $root,
        TokenStream $stream
    ): self {
        Util::skipWhiteSpaceAndComments($stream);
        $start = $stream->current();
        $end = $start;
        
        $segments = [];
        $operandOrCall = $root;
        $append = false;
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                break;
            }

            switch ($stream->current()->getType()) {
                case TokenType::OPERATOR_OPTCHAIN():
                    if ($operandOrCall !== null) {
                        $end = $stream->current();
                        $segments[] = ChainSegment::createFromOperandOrCall(true, $operandOrCall);
                        $stream->next();
                        $operandOrCall = null;
                        $append = true;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                case TokenType::PERIOD():
                    if ($operandOrCall !== null) {
                        $end = $stream->current();
                        $segments[] = ChainSegment::createFromOperandOrCall(false, $operandOrCall);
                        $stream->next();
                        $operandOrCall = null;
                        $append = false;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                case TokenType::BRACKETS_SQUARE_OPEN():
                    if ($operandOrCall !== null) {
                        $segments[] = ChainSegment::createFromOperandOrCall(false, $operandOrCall);
                    } else if (!$append) {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    $end = $stream->current();
                    $stream->next();
                    $operandOrCall = Expression::createFromTokenStream($stream);
                    Util::skipWhiteSpaceAndComments($stream);
                    Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                    $append = true;
                    break;
                
                case TokenType::BRACKETS_ROUND_OPEN():
                    if ($operandOrCall !== null) {
                        $segments[] = ChainSegment::createFromOperandOrCall(false, $operandOrCall);
                    } else if (!$append) {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    $end = $stream->current();
                    $operandOrCall = Call::createFromTokenStream($stream);
                    $append = true;
                    break;

                case TokenType::IDENTIFIER():
                    if ($operandOrCall === null) {
                        $end = $stream->current();
                        $operandOrCall = Identifier::createFromTokenStream($stream);
                        $append = true;
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
                
                default:
                    if ($operandOrCall !== null) {
                        $segments[] = ChainSegment::createFromOperandOrCall(false, $operandOrCall);
                        return new self($start, $end, $segments);
                    } else {
                        throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                    }
                    break;
            }
        }

        if ($operandOrCall !== null) {
            $segments[] = ChainSegment::createFromOperandOrCall(false, $operandOrCall);
        }

        return new self($start, $end, $segments);
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
     * @return array<int, ChainSegment>
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
            'segments' => $this->segments
        ];
    }
}