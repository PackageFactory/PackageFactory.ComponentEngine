<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Pragma\AfxPragmaInterface;
use PackageFactory\ComponentEngine\Runtime\Context;

final class TagName implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        $value = $stream->current();
        if ($value->getType() === TokenType::IDENTIFIER()) {
            $stream->next();
            return new self($value->getValue());
        } else {
            throw new \Exception('@TODO: Unexpected Token: ' . $value);
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function evaluate()
    {
        throw new \Exception('@TODO: TAG NAME->evaluate()');
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}