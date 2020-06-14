<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Tag implements \JsonSerializable
{
    /**
     * @var Token
     */
    private $start;

    /**
     * @var Token
     */
    private $end;

    /**
     * @var TagName
     */
    private $tagName;

    /**
     * @var array|Attribute[]
     */
    private $attributes;

    /**
     * @var array|(Content|Expression|Tag)[]
     */
    private $children;

    /**
     * @param Token $start
     * @param Token $end
     * @param TagName $tagName
     * @param array|Attribute[] $attributes
     * @param array|(Content|Expression|Tag)[] $children
     */
    public function __construct(
        Token $start,
        Token $end,
        TagName $tagName,
        array $attributes,
        array $children
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->tagName = $tagName;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    /**
     * @param TokenStream $stream
     * @return Tag
     */
    public static function createFromTokenStream(TokenStream $stream): Tag
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        Util::expect($stream, TokenType::TAG_START());

        $tagName = TagName::createFromTokenStream($stream);
        $attributes = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            switch ($stream->current()->getType()) {
                case TokenType::IDENTIFIER():
                    $attribute = Attribute::createFromTokenStream($stream);
                    $attributes[(string) $attribute->getName()] = $attribute;
                break;

                case TokenType::EXPRESSION_START():
                    $expression = Expression::createFromTokenStream($stream);
                    if ($expression->getRoot() !== null) {
                        throw new \Exception('@TODO: Spread expressions are not implemented yet!');
                    }
                break;

                case TokenType::TAG_CLOSE():
                    $stream->next();
                    Util::expect($stream, TokenType::TAG_END());

                    return new self(
                        $start, 
                        $stream->current(), 
                        $tagName, 
                        $attributes, 
                        []
                    );

                case TokenType::TAG_END():
                    break 2;

                default:
                    throw new \Exception('@TODO: Unexpected Token ' . $stream->current());
            }
        }

        Util::expect($stream, TokenType::TAG_END());
        
        $children = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            switch ($stream->current()->getType()) {
                case TokenType::CONTENT():
                    $children[] = Content::createFromTokenStream($stream);
                break;

                case TokenType::EXPRESSION_START():
                    $children[] = Expression::createFromTokenStream($stream);
                break;

                case TokenType::TAG_START():
                    $lookAhead = $stream->lookAhead(2);
                    if ($lookAhead && $lookAhead->getType() === TokenType::TAG_CLOSE()) {
                        $stream->skip(2);
                        
                        if ($stream->current()->getType() === TokenType::TAG_END()) {
                            if ($tagName->getValue() === 'c:fragment') {
                                Util::expect($stream, TokenType::TAG_END());
                            }
                            else {
                                throw new \Exception('@TODO: Unexpected Closed Fragment');
                            }
                        }
                        else {
                            TagName::assert($stream, $tagName);
                            Util::expect($stream, TokenType::TAG_END());
                        }
                        break 2;
                    }
                    else {
                        $children[] = self::createFromTokenStream($stream);
                    }
                break;
            }
        }

        $end = $stream->current();
        $stream->next();

        return new self(
            $start,
            $end,
            $tagName,
            $attributes,
            $children
        );
    }

    /**
     * @return Token
     */
    public function getStart(): Token
    {
        return $this->start;
    }

    /**
     * @return Token
     */
    public function getEnd(): Token
    {
        return $this->end;
    }

    /**
     * @return TagName
     */
    public function getTagName(): TagName
    {
        return $this->tagName;
    }

    /**
     * @return array|Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array|(Content|Expression|Tag)[]
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
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'tagName' => $this->tagName,
                'attributes' => $this->attributes,
                'children' => $this->children
            ]
        ];
    }
}