<?php

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Child;
use PackageFactory\ComponentEngine\Parser\Ast\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Tag implements Term, Statement, Child, \JsonSerializable
{
    private ?TagName $tagName;

    /**
     * @var array|ParameterAssignment[]
     */
    private array $attributes;

    /**
     * @var array|Child[]
     */
    private array $children;

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
    public static function fromTokenStream(TokenStream $stream): self
    {
        $start = $end = $stream->current();
        $stream->consume(TokenType::AFX_TAG_START());

        if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
            $tagName = TagName::fromTokenStream($stream);
        } elseif ($stream->current()->getType() === TokenType::AFX_TAG_END()) {
            $tagName = null;
        } else {
            throw ParserFailed::becauseOfUnexpectedToken(
                $stream->current(),
                [TokenType::IDENTIFIER()]
            );
        }

        $attributes = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->getType()) {
                case TokenType::AFX_TAG_END():
                    $stream->next();
                    break 2;
                case TokenType::IDENTIFIER():
                    $attributes[] = Attribute::fromTokenStream($stream);
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    if ($lookAhead = $stream->lookAhead(2)) {
                        if ($lookAhead->getType() === TokenType::OPERATOR_SPREAD()) {
                            $stream->next();
                            $attributes[] = Spread::fromTokenStream($stream);
                            $stream->consume(TokenType::AFX_EXPRESSION_END());
                        } else {
                            throw ParserFailed::becauseOfUnexpectedToken(
                                $stream->current(),
                                [TokenType::OPERATOR_SPREAD()]
                            );
                        }
                    } else {
                        throw ParserFailed::becauseOfUnexpectedEndOfFile($stream);
                    }
                    break;
                case TokenType::AFX_TAG_CLOSE():
                    $stream->next();
                    $end = $stream->consume(TokenType::AFX_TAG_END());
                    return new self($tagName, $attributes, []);
                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::AFX_TAG_END(),
                            TokenType::IDENTIFIER(),
                            TokenType::AFX_EXPRESSION_START(),
                            TokenType::AFX_TAG_CLOSE()
                        ]
                    );
            }
        }

        $stream->skipWhiteSpaceAndComments();

        $children = [];
        while ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::WHITESPACE():
                case TokenType::END_OF_LINE():
                case TokenType::AFX_TAG_CONTENT():
                    $children[] = Content::fromTokenStream($stream);
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    $stream->next();

                    $token = $stream->current();
                    $child = ExpressionParser::parseTerm($stream);
                    if ($child instanceof Child) {
                        $children[] = $child;
                        $stream->consume(TokenType::AFX_EXPRESSION_END());
                    } else {
                        throw ParserFailed::becauseOfUnexpectedTerm(
                            $token,
                            $child,
                            [
                                Content::class,
                                Tag::class,
                                Expression\ArrayLiteral::class,
                                Expression\TemplateLiteral::class,
                                Expression\NullLiteral::class,
                                Expression\NumberLiteral::class,
                                Expression\StringLiteral::class,
                                Expression\Chain::class,
                                Expression\Conjunction::class,
                                Expression\DashOperation::class,
                                Expression\Disjunction::class,
                                Expression\Identifier::class,
                                Expression\Ternary::class,
                            ]
                        );
                    }
                    break;
                case TokenType::AFX_TAG_START():
                    if ($lookAhead = $stream->lookAhead(2)) {
                        if ($lookAhead->getType() === TokenType::AFX_TAG_CLOSE()) {
                            $stream->skip(2);

                            if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                                if ($tagName && $stream->current()->getValue() === $tagName->getValue()) {
                                    $stream->next();
                                    $end = $stream->consume(TokenType::AFX_TAG_END());
                                    break 2;
                                } else {
                                    throw ParserFailed::becauseOfUnexpectedClosingTag($stream->current());
                                }
                            } elseif ($stream->current()->getType() === TokenType::AFX_TAG_END()) {
                                if ($tagName === null) {
                                    $stream->next();
                                    break 2;
                                } else {
                                    throw ParserFailed::becauseOfUnexpectedClosingTag($stream->current());
                                }
                            } else {
                                throw ParserFailed::becauseOfUnexpectedToken(
                                    $stream->current(),
                                    [
                                        TokenType::IDENTIFIER(),
                                        TokenType::AFX_TAG_END()
                                    ]
                                );
                            }
                        }
                    }
                    $children[] = self::fromTokenStream($stream);
                    break;

                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::WHITESPACE(),
                            TokenType::END_OF_LINE(),
                            TokenType::AFX_TAG_CONTENT(),
                            TokenType::AFX_EXPRESSION_START(),
                            TokenType::AFX_TAG_START()
                        ]
                    );
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
