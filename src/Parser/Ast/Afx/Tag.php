<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Tag implements Term, Statement, Child, \JsonSerializable
{
    /**
     * @var null|TagName
     */
    private $tagName;

    /**
     * @var array|ParameterAssignment[]
     */
    private $attributes;

    /**
     * @var array|Child[]
     */
    private $children;

    /**
     * @param null|TagName $tagName
     * @param array|ParameterAssignment[] $attributes
     * @param array|Child[] $children
     */
    public function __construct(
        ?TagName $tagName,
        array $attributes,
        array $children
    ) {
        $this->tagName = $tagName;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::expect($stream, TokenType::AFX_TAG_START());
        Util::ensureValid($stream);

        if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
            $tagName = TagName::createFromTokenStream($stream);
        } elseif ($stream->current()->getType() === TokenType::AFX_TAG_END()) {
            $tagName = null;
        } else {
            throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        $attributes = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            switch ($stream->current()->getType()) {
                case TokenType::AFX_TAG_END():
                    $stream->next();
                    break 2;
                case TokenType::IDENTIFIER():
                    $attributes[] = Attribute::createFromTokenStream($stream);
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    if ($lookAhead = $stream->lookAhead(2)) {
                        if ($lookAhead->getType() === TokenType::OPERATOR_SPREAD()) {
                            $stream->next();
                            $attributes[] = Spread::createFromTokenStream($stream);
                            Util::expect($stream, TokenType::AFX_EXPRESSION_END());
                        } else {
                            throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                        }
                    } else {
                        throw new \Exception('@TODO: Unexpected end of file');
                    }
                    break;
                case TokenType::AFX_TAG_CLOSE():
                    $stream->next();
                    Util::expect($stream, TokenType::AFX_TAG_END());
                    return new self($tagName, $attributes, []);
                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        Util::skipWhiteSpaceAndComments($stream);
        Util::ensureValid($stream);

        $children = [];
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::WHITESPACE():
                case TokenType::END_OF_LINE():
                case TokenType::AFX_TAG_CONTENT():
                    $children[] = Content::createFromTokenStream($stream);
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    $stream->next();
                    $child = ExpressionParser::parseTerm($stream);
                    if ($child instanceof Child) {
                        $children[] = $child;
                        Util::expect($stream, TokenType::AFX_EXPRESSION_END());
                    } else {
                        throw new \Exception('@TODO: Unexpected Term: ' . get_class($child));
                    }
                    break;
                case TokenType::AFX_TAG_START():
                    if ($lookAhead = $stream->lookAhead(2)) {
                        if ($lookAhead->getType() === TokenType::AFX_TAG_CLOSE()) {
                            $stream->skip(2);

                            if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                                if ($tagName && $stream->current()->getValue() === $tagName->getValue()) {
                                    $stream->next();
                                    Util::expect($stream, TokenType::AFX_TAG_END());
                                    break 2;
                                } else {
                                    throw new \Exception('@TODO: Unexpected Closing Tag: ' . $stream->current());
                                }
                            } elseif ($stream->current()->getType() === TokenType::AFX_TAG_END()) {
                                if ($tagName === null) {
                                    $stream->next();
                                    break 2;
                                } else {
                                    throw new \Exception('@TODO: Unexpected Closing Tag: ' . $stream->current());
                                }
                            } else {
                                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
                            }
                        }
                    }
                    $children[] = self::createFromTokenStream($stream);
                    break;

                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        return new self($tagName, $attributes, $children);
    }

    /**
     * @return null|TagName
     */
    public function getTagName(): ?TagName
    {
        return $this->tagName;
    }

    /**
     * @return bool
     */
    public function getIsFragment(): bool
    {
        return $this->tagName === null;
    }

    /**
     * @return array|ParameterAssignment[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array|Child[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Tag',
            'tagName' => $this->tagName,
            'attributes' => $this->attributes,
            'children' => $this->children
        ];
    }
}