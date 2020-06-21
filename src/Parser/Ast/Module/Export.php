<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Pragma\AfxPragmaInterface;
use PackageFactory\ComponentEngine\Runtime\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\ContextEvaluatorInterface;

final class Export implements \JsonSerializable, AfxEvaluatorInterface
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

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }
        Util::expect($stream, TokenType::MODULE_KEYWORD_EXPORT());

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        switch ($stream->current()->getType()) {
            case TokenType::MODULE_KEYWORD_CONST():
                return self::createFromConstant(
                    Constant::createFromTokenStream($stream)
                );
            case TokenType::MODULE_KEYWORD_DEFAULT():
                $name = Identifier::createFromToken($stream->current());
                $stream->next();
                break;
            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

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
     * @param Constant $constant
     * @return self
     */
    public static function createFromConstant(Constant $constant): self
    {
        throw new \Exception('@TODO: Export::createFromTokenStream');
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
    public function evaluate(AfxPragmaInterface $pragma, Context $context)
    {
        if ($this->value instanceof AfxEvaluatorInterface) {
            return $this->value->evaluate($pragma, $context);
        } elseif ($this->value instanceof ContextEvaluatorInterface) {
            return $this->value->evaluate($context);
        } else {
            throw new \Exception('@TODO: Export::evaluate');
        }
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        throw new \Exception('@TODO: Export::jsonSerialize');
    }
}