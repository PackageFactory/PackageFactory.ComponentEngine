<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Content implements \JsonSerializable
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
        $value = '';
        $whitespace = false;
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::AFX_TAG_CONTENT():
                    if ($whitespace) {
                        $value .= ' ';
                    }
                    $whitespace = false;
                    $value .= $stream->current()->getValue();
                    $stream->next();
                    break;
                case TokenType::WHITESPACE():
                case TokenType::END_OF_LINE():
                    if (!$whitespace) {
                        $whitespace = true;
                    }
                    $stream->next();
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    if ($whitespace && !empty($value)) {
                        $value .= ' ';
                    }
                    break 2;
                case TokenType::AFX_TAG_START():
                    break 2;

                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        return new self($value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}