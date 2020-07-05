<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Identifier;
use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\ExpressionParser;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Util;

final class Constant implements \JsonSerializable
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

    public static function fromTokenStream(TokenStream $stream): self
    {
        $token = $stream->current();
        $stream->consume(TokenType::MODULE_KEYWORD_CONST());

        $stream->skipWhiteSpaceAndComments();

        $name = Identifier::fromTokenStream($stream);

        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::MODULE_ASSIGNMENT());

        $value = null;
        $brackets = 0;
        while ($value === null) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_ROUND_OPEN():
                    $brackets++;
                    $stream->next();
                    break;
                default:
                    $value = ExpressionParser::parseTerm($stream);
                    break;
            }
        }

        while ($brackets > 0) {
            $stream->skipWhiteSpaceAndComments();
            $stream->consume(TokenType::BRACKETS_ROUND_CLOSE());
            $brackets--;
        }

        if ($value === null) {
            throw ParserFailed::becauseOfUnexpectedEmptyConstant($token);
        }

        return new self($name, $value);
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
        throw new \Exception('@TODO: Constant::jsonSerialize');
    }
}