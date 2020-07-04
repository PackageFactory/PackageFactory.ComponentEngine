<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\Position;

final class Token
{
    /**
     * @var TokenType
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @var Position
     */
    private $start;

    /**
     * @var Position
     */
    private $end;

    /**
     * @var Source
     */
    private $source;

    /**
     * @param TokenType $type
     * @param string $value
     * @param Position $start
     * @param Position $end
     * @param Source $source
     */
    private function __construct(
        TokenType $type,
        string $value,
        Position $start,
        Position $end,
        Source $source
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->start = $start;
        $this->end = $end;
        $this->source = $source;
    }

    /**
     * @param TokenType $type
     * @param Fragment $fragment
     * @return Token
     */
    public static function fromFragment(
        TokenType $type,
        Fragment $fragment
    ): Token {
        return new Token(
            $type,
            $fragment->getValue(),
            $fragment->getStart(),
            $fragment->getEnd(),
            $fragment->getSource()
        );
    }

    /**
     * @return TokenType
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return Position
     */
    public function getStart(): Position
    {
        return $this->start;
    }

    /**
     * @return Position
     */
    public function getEnd(): Position
    {
        return $this->end;
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    public function __toString()
    {
        return $this->value;
    }
}