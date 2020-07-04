<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

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

    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::expect($stream, TokenType::MODULE_KEYWORD_CONST());

        Util::skipWhiteSpaceAndComments($stream);
        Util::ensureValid($stream);

        $name = Identifier::createFromTokenStream($stream);

        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::MODULE_ASSIGNMENT());

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
            throw new \Exception('@TODO: Unexpected empty constant');
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