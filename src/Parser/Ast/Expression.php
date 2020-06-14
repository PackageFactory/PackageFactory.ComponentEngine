<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use Exception;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Expression implements \JsonSerializable
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
     * @var null|Negation|Chain|ObjectLiteral|StringLiteral|Spread
     */
    private $root;

    /**
     * @param Token $start
     * @param Token $end
     * @param null|Negation|Chain|ObjectLiteral|StringLiteral|Spread $root
     */
    public function __construct(
        Token $start,
        Token $end,
        $root
    ) {
        $this->start = $start;
        $this->end = $end;

        if ($root instanceof Negation) {
            $this->root = $root;
        }
        elseif ($root instanceof Chain) {
            $this->root = $root;
        }
        elseif ($root instanceof ObjectLiteral) {
            $this->root = $root;
        }
        elseif ($root instanceof StringLiteral) {
            $this->root = $root;
        }
        elseif ($root instanceof Spread) {
            $this->root = $root;
        }
        elseif ($root === null) {
            $this->root = $root;
        }
        else {
            var_dump(gettype($root));
            var_dump(get_class($root));
            throw new \Exception('@TODO: Exception');
        }
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $start = $stream->current();
        $end = $stream->current();
        Util::expect($stream, TokenType::EXPRESSION_START());

        Util::skipWhiteSpaceAndComments($stream);

        $root = null;
        switch ($stream->current()->getType()) {
            case TokenType::EXCLAMATION():
                $root = Negation::createFromTokenStream($stream);
            break;
            
            case TokenType::IDENTIFIER():
                $root = Chain::createFromTokenStream($stream);
            break;

            case TokenType::OBJECT_START():
                $root = ObjectLiteral::createFromTokenStream($stream);
            break;

            case TokenType::STRING_START():
                $root = StringLiteral::createFromTokenStream($stream);
            break;
            
            case TokenType::PERIOD():
                $root = Spread::createFromTokenStream($stream);
            break;

            case TokenType::EXPRESSION_END():
                // ignore
            break;

            default:
                throw new Exception('@TODO: Unexpected Token');
        }

        $end = $stream->current();
        Util::expect($stream, TokenType::EXPRESSION_END());

        return new self(
            $start,
            $end,
            $root
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
     * @return null|Negation|Chain|ObjectLiteral|StringLiteral|Spread
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Expression',
            'properties' => [
                'start' => $this->start,
                'end' => $this->end,
                'root' => $this->root
            ]
        ];
    }
}