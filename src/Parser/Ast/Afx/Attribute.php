<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Afx;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class Attribute implements \JsonSerializable
{
    /**
     * @var AttributeName
     */
    private $attributeName;

    /**
     * @var Operand
     */
    private $value;

    /**
     * @param AttributeName $attributeName
     * @param Operand $value
     */
    private function __construct(
        AttributeName $attributeName,
        $value
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
        if ($stream->valid()) {
            if ($stream->current()->getType() === TokenType::AFX_ATTRIBUTE_ASSIGNMENT()) {
                $stream->next();
            } else {
                return new self($attributeName, true);
            }
        } else {
            throw new \Exception('@TODO: Unexpected end of file');
        }
        if ($stream->valid()) {
            switch ($stream->current()->getType()) {
                case TokenType::STRING_LITERAL_START():
                    $value = StringLiteral::createFromTokenStream($stream);
                    break;
                case TokenType::AFX_EXPRESSION_START():
                    $stream->next();
                    $value = Expression::createFromTokenStream(
                        $stream, 
                        Expression::PRIORITY_TERNARY,
                        TokenType::AFX_EXPRESSION_END()
                    );
                    break;
                
                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
            }
        } else {
            throw new \Exception('@TODO: Unexpected end of file');
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
     * @return Operand
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
        return [
            'name' => $this->attributeName,
            'value' => $this->value
        ];
    }
}