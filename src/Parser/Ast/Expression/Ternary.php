<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Ternary implements \JsonSerializable
{
    /**
     * @var Operand
     */
    private $condition;

    /**
     * @var Operand
     */
    private $trueBranch;

    /**
     * @var Operand
     */
    private $falseBranch;

    /**
     * @param Operand $condition
     * @param Operand $trueBranch
     * @param Operand $falseBranch
     */
    private function __construct(
        $condition,
        $trueBranch,
        $falseBranch
    ) {
        $this->condition = $condition;
        $this->trueBranch = $trueBranch;
        $this->falseBranch = $falseBranch;
    }

    /**
     * @param Operand $condition
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream($condition, TokenStream $stream): self
    {
        Util::expect($stream, TokenType::QUESTIONMARK());
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $trueBranch = Expression::createFromTokenStream(
            $stream, 
            Expression::PRIORITY_TERNARY,
            TokenType::COLON()
        );

        $falseBranch = Expression::createFromTokenStream($stream);

        return new self($condition, $trueBranch, $falseBranch);
    }

    /**
     * @return Operand
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return Operand
     */
    public function getTrueBranch()
    {
        return $this->trueBranch;
    }

    /**
     * @return Operand
     */
    public function getFalseBranch()
    {
        return $this->falseBranch;
    }

    /**
     * @param Context $context
     * @return mixed
     */
    public function evaluate(Context $context = null)
    {
        $condition = $this->condition->evaluate($context);
        
        if ($condition) {
            return $this->trueBranch->evaluate($context);
        } else {
            return $this->falseBranch->evaluate($context);
        }
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