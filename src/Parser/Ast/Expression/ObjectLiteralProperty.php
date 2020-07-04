<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Key;
use PackageFactory\ComponentEngine\Parser\Ast\Statement;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class ObjectLiteralProperty implements \JsonSerializable
{
    /**
     * @var null|Key
     */
    private $key;

    /**
     * @var Statement
     */
    private $value;

    /**
     * @param null|Key $key
     * @param Statement $value
     */
    private function __construct(?Key $key, Statement $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::ensureValid($stream);

        $key = null;
        $value = null;
        switch ($stream->current()->getType()) {                
            case TokenType::IDENTIFIER():
                $key = Identifier::createFromTokenStream($stream);
                break;
            case TokenType::BRACKETS_SQUARE_OPEN():
                $stream->next();
                $key = ExpressionParser::parseTerm($stream);
                if ($key instanceof Key) {
                    Util::skipWhiteSpaceAndComments($stream);
                    Util::expect($stream, TokenType::BRACKETS_SQUARE_CLOSE());
                } else {
                    throw new \Exception('@TODO: Unexpected Term: ' . get_class($key));
                }
                break;
            case TokenType::OPERATOR_SPREAD():
                return new self(
                    null, 
                    ExpressionParser::parseStatement($stream,  ExpressionParser::PRIORITY_LIST)
                );

            default:
                throw new \Exception('@TODO: Unexpected Token: ' . $stream->current());
        }

        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::COLON());

        Util::skipWhiteSpaceAndComments($stream);
        Util::ensureValid($stream);

        return new self(
            $key, 
            ExpressionParser::parseStatement($stream, ExpressionParser::PRIORITY_LIST)
        );
    }

    /**
     * @return null|Term
     */
    public function getKey(): ?Term
    {
        /** @var Term $key */
        $key = $this->key;
        return $key;
    }

    /**
     * @return Statement
     */
    public function getValue(): Statement
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}