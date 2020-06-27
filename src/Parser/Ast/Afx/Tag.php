<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;
use PackageFactory\ComponentEngine\Pragma\AfxPragmaInterface;
use PackageFactory\ComponentEngine\Runtime\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Runtime\Context;

final class Tag implements \JsonSerializable, AfxEvaluatorInterface
{
    /**
     * @var null|TagName
     */
    private $tagName;

    /**
     * @var array|(Attribute|Spread)[]
     */
    private $attributes;

    /**
     * @var array|(Content|Tag|Operand)[]
     */
    private $children;

    /**
     * @param null|TagName $tagName
     * @param array|(Attribute|Spread)[] $attributes
     * @param array|(Content|Operand)[] $children
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
        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::AFX_TAG_START());
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

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
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            }

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

                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        }

        Util::skipWhiteSpaceAndComments($stream);
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

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
                    $children[] = Expression::createFromTokenStream(
                        $stream, 
                        Expression::PRIORITY_TERNARY,
                        TokenType::AFX_EXPRESSION_END()
                    );
                    break;
                case TokenType::AFX_TAG_START():
                    if ($lookAhead = $stream->lookAhead(2)) {
                        if ($lookAhead->getType() === TokenType::AFX_TAG_CLOSE()) {
                            $stream->skip(2);

                            if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                                if ($stream->current()->getValue() === $tagName->getValue()) {
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
                                    throw new \Exception('@TODO: Unexpected Closing Fragment: ' . $stream->current());
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
     * @return array|(Attribute|Spread)[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array|(Content|Operand)[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return mixed
     */
    public function evaluate(AfxPragmaInterface $pragma, Context $context = null)
    {
        $mapTag = $this->tagName === '$map';
        $mapAttribute = $mapTag ? [] : null;
        $ifAttribute = null;
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof Spread) {
                foreach ($attribute->evaluate($context) as $key => $value) {
                    if ($key !== '$map' && $key !== '$if') {
                        if ($mapTag) {
                            $mapAttribute[$key] = $value;
                        } else {
                            $attributes[$key] = $value;
                        }
                    }
                }
            } elseif ($attribute instanceof Attribute) {
                foreach ($attribute->evaluate($context) as $key => $value) {
                    if ($key === '$map') {
                        $mapAttribute = (array) $value;
                    } elseif ($key === '$if') {
                        $ifAttribute = $value;
                    } else {
                        $attributes[$key] = $value;
                    }
                }
            } else {
                throw new \Exception('@TODO: Invalid Attribute');
            }
        }

        if ($ifAttribute !== null && !$ifAttribute) {
            return null;
        }

        $children = [];
        if ($mapAttribute !== null) {
            $items = $mapAttribute['items'] ?? [];
            $itemName = $mapAttribute['as'] ?? 'item';
            $keyName = $mapAttribute['key'] ?? 'key';
            $iteratorName = $mapAttribute['it'] ?? 'it';
            $index = 0;
            $isFirst = true;
            $count = is_countable($items) ? count($items) : null;

            foreach ($items as $key => $item) {
                $iterationContext = [];
                $iterationContext[$keyName] = $key;
                $iterationContext[$itemName] = $item;
                $iterationContext[$iteratorName]['index'] = $index;
                $iterationContext[$iteratorName]['isFirst'] = $isFirst;
                $iterationContext[$iteratorName]['isEven'] = (($index + 1) % 2) === 0;
                $iterationContext[$iteratorName]['isOdd'] = (($index + 1) % 2) === 1;

                if ($count !== null) {
                    $iterationContext[$iteratorName]['count'] = $count;
                    $iterationContext[$iteratorName]['isLast'] = $index === $count - 1;
                }

                $subContext = $context->withMergedProperties($iterationContext);

                foreach ($this->children as $child) {
                    if ($child instanceof Content) {
                        $children[] = $child->getValue();
                    } elseif ($child instanceof Tag) {
                        $children[] = $child->evaluate($pragma, $subContext);
                    } else {
                        // @TODO: This is not safe yet!
                        $children[] = $child->evaluate($subContext);
                    }
                }
            }
        } else {
            foreach ($this->children as $child) {
                if ($child instanceof Content) {
                    $children[] = $child->getValue();
                } elseif ($child instanceof Tag) {
                    $children[] = $child->evaluate($pragma, $context);
                } else {
                    // @TODO: This is not safe yet!
                    $children[] = $child->evaluate($context);
                }
            }
        }


        if ($this->getIsFragment() || $mapTag) {
            return $pragma->createFragment($children);
        } elseif (ctype_lower($this->tagName->getValue()[0])) {
            return $pragma->createElement(
                $this->tagName->getValue(),
                $attributes,
                $children
            );
        } elseif ($this->tagName->getValue() === '$tag') {
            return $pragma->createElement(
                $attributes['tagName'] ?? 'div',
                (array) $attributes['attributes'] ?? [],
                $children
            );
        } elseif ($context->hasProperty($this->tagName->getValue())) {
            $constructor = $context->getProperty($this->tagName->getValue());

            if ($constructor instanceof AfxEvaluatorInterface) {
                return $constructor->evaluate(
                    $pragma, 
                    Context::createFromArray(['props' => $attributes])
                );
            } else {
                throw new \RuntimeException('@TODO: Invalid constructor');
            }
        } else {
            throw new \RuntimeException('@TODO: Invalid tagName');
        }
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