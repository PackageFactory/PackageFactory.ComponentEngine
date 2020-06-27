<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

final class ChainSegment implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $isOptional;

    /**
     * @var Operand|Call
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
     * @param Operand|Call $operandOrCall
     * @return self
     */
    public static function createFromOperandOrCall(
        bool $isOptional,
        $operandOrCall
    ): self {
        return new self($isOptional, $operandOrCall);
    }

    /**
     * @return bool
     */
    public function getIsOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * @return Operand|Call
     */
    public function getSubject()
    {
        return $this->subject;
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