<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Constant implements \JsonSerializable
{
    /**
     * @var Identifier
     */
    private $name;

    /**
     * @var Tag|Operand
     */
    private $value;

    /**
     * @param Identifier $name
     * @param Tag|Operand $value
     */
    private function __construct(
        Identifier $name,
        $value
    ) {
        $this->name = $name;
        $this->value = $value;
    }

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }
        Util::expect($stream, TokenType::MODULE_KEYWORD_CONST());
        
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }
        $name = Identifier::createFromTokenStream($stream);
        
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }
        Util::expect($stream, TokenType::MODULE_ASSIGNMENT());

        $value = null;
        $brackets = 0;
        while ($value === null) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            }

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_ROUND_OPEN():
                    $brackets++;
                    $stream->next();
                    break;
                case TokenType::AFX_TAG_START():
                    $value = Tag::createFromTokenStream($stream);
                    break;
                default:
                    $value = Expression::createFromTokenStream($stream);
                    break;
            }
        }

        while ($brackets > 0) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            }

            Util::expect($stream, TokenType::BRACKETS_ROUND_CLOSE());
            $brackets--;
        }

        return new self($name, $value);
    }

    /**
     * @return Identifier
     */
    public function getName(): Identifier
    {
        return $this->name;
    }

    /**
     * @return Tag|Operand
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        throw new \Exception('@TODO: Constant::jsonSerialize');
    }
}