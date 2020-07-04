<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Expression;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Util;

final class Export implements \JsonSerializable
{
    /**
     * @var Identifier
     */
    private $name;

    /**
     * @var Term
     */
    private $value;

    /**
     * @param Identifier $name
     * @param Term $value
     */
    private function __construct(
        Identifier $name,
        Term $value
    ) {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function fromTokenStream(TokenStream $stream): self
    {
        $token = $stream->current();
        Util::expect($stream, TokenType::MODULE_KEYWORD_EXPORT());

        Util::skipWhiteSpaceAndComments($stream);
        Util::ensureValid($stream);

        switch ($stream->current()->getType()) {
            case TokenType::MODULE_KEYWORD_CONST():
                return self::fromConstant(
                    Constant::fromTokenStream($stream)
                );
            case TokenType::MODULE_KEYWORD_DEFAULT():
                $name = Identifier::fromToken($stream->current());
                $stream->next();
                break;
            default:
                throw ParserFailed::becauseOfUnexpectedToken(
                    $stream->current(),
                    [
                        TokenType::MODULE_KEYWORD_CONST(),
                        TokenType::MODULE_KEYWORD_DEFAULT()
                    ]
                );
        }

        $value = null;
        $brackets = 0;
        while ($value === null) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::ensureValid($stream);

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_ROUND_OPEN():
                    $brackets++;
                    $stream->next();
                    break;
                case TokenType::AFX_TAG_START():
                    $value = Tag::fromTokenStream($stream);
                    break;
                default:
                    $value = ExpressionParser::parseTerm($stream);
                    break;
            }
        }

        while ($brackets > 0) {
            Util::skipWhiteSpaceAndComments($stream);
            Util::expect($stream, TokenType::BRACKETS_ROUND_CLOSE());
            $brackets--;
        }

        if ($value === null) {
            throw ParserFailed::becauseOfUnexpectedEmptyExport($token);
        }

        return new self($name, $value);
    }

    /**
     * @param Constant $constant
     * @return self
     */
    public static function fromConstant(Constant $constant): self
    {
        throw new \Exception('@TODO: Export::fromTokenStream');
    }

    /**
     * @return Identifier
     */
    public function getName(): Identifier
    {
        return $this->name;
    }

    /**
     * @return Term
     */
    public function getValue(): Term
    {
        return $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        throw new \Exception('@TODO: Export::jsonSerialize');
    }
}