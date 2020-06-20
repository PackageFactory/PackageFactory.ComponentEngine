<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Runtime\Context;

final class ChainSegment implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $isOptional;

    /**
     * @var Operand
     */
    private $subject;

    /**
     * @param boolean $isOptional
     * @param Operand $subject
     */
    private function __construct(
        bool $isOptional,
        $subject
    ) {
        $this->isOptional = $isOptional;
        $this->subject = $subject;
    }

    /**
     * @param boolean $isOptional
     * @param Identifier $identifier
     * @return self
     */
    public static function createFromIdentifier(
        bool $isOptional,
        Identifier $identifier
    ): self {
        return new self($isOptional, $identifier);
    }

    /**
     * @param boolean $isOptional
     * @param Operand $operand
     * @return self
     */
    public static function createFromOperand(
        bool $isOptional,
        $operand
    ): self {
        return new self($isOptional, $operand);
    }

    /**
     * @return bool
     */
    public function getIsOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * @return Operand
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param Context $context
     * @return void
     */
    public function evaluate(Context $context = null)
    {
        if ($context === null) {
            throw new \Exception('@TODO: Cannot evaluate chain segment without context');
        }

        if ($this->subject instanceof Identifier) {
            return (string) $this->subject;
        } else {
            return $this->subject->evaluate($context);
        }
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'ChainSegment',
            'isOptional' => $this->isOptional,
            'subject' => $this->subject
        ];
    }
} 