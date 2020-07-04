<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Ast\ParameterAssignment;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Attribute implements ParameterAssignment, \JsonSerializable
{
    /**
     * @var AttributeName
     */
    private $attributeName;

    /**
     * @var null|Term
     */
    private $value;

    /**
     * @param AttributeName $attributeName
     * @param null|Term $value
     */
    private function __construct(
        AttributeName $attributeName,
        ?Term $value
    ) {
        $this->attributeName = $attributeName;
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        $attributeName = AttributeName::createFromTokenStream($stream);
        Util::ensureValid($stream);

        if ($stream->current()->getType() === TokenType::AFX_ATTRIBUTE_ASSIGNMENT()) {
            $stream->next();
            Util::ensureValid($stream);
        } else {
            return new self($attributeName, null);
        }

        switch ($stream->current()->getType()) {
            case TokenType::STRING_LITERAL_START():
                $value = StringLiteral::createFromTokenStream($stream);
                break;
            case TokenType::AFX_EXPRESSION_START():
                $stream->next();
                $value = ExpressionParser::parseTerm($stream);
                Util::expect($stream, TokenType::AFX_EXPRESSION_END());
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::STRING_LITERAL_START(),
                        TokenType::AFX_EXPRESSION_START()
                    ]
                );
        }

        return new self($attributeName, $value);
    }

    /**
     * @return AttributeName
     */
    public function getAttributeName(): AttributeName
    {
        return $this->attributeName;
    }

    /**
     * @return null|Term
     */
    public function getValue(): ?Term
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->attributeName,
            'value' => $this->value
        ];
    }
}