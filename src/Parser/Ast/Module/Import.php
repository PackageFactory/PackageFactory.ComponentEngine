<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Exception\ParserFailed;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Util;

final class Import implements \JsonSerializable
{
    /**
     * @var string
     */
    private $domesticName;

    /**
     * @var string
     */
    private $foreignName;

    /**
     * @var string
     */
    private $target;

    /**
     * @param string $domesticName
     * @param string $foreignName
     * @param string $target
     */
    private function __construct(
        string $domesticName,
        string $foreignName,
        string $target
    ) {
        $this->domesticName = $domesticName;
        $this->foreignName = $foreignName;
        $this->target = $target;
    }

    /**
     * @param TokenStream $stream
     * @return \Iterator<mixed, self>
     */
    public static function fromTokenStream(TokenStream $stream): \Iterator
    {
        $stream->skipWhiteSpaceAndComments();
        $stream->consume(TokenType::MODULE_KEYWORD_IMPORT());
        $stream->skipWhiteSpaceAndComments();

        $importMap = [];
        while ($stream->valid()) {
            $stream->skipWhiteSpaceAndComments();

            switch ($stream->current()->getType()) {
                case TokenType::IDENTIFIER():
                    $importMap[$stream->current()->getValue()] = 'default';
                    $stream->next();
                    break;
                case TokenType::MODULE_KEYWORD_FROM():
                    $stream->next();
                    break 2;
                default:
                    throw ParserFailed::becauseOfUnexpectedToken(
                        $stream->current(),
                        [
                            TokenType::IDENTIFIER(),
                            TokenType::MODULE_KEYWORD_FROM()
                        ]
                    );
            }
        }

        $stream->skipWhiteSpaceAndComments();

        $target = StringLiteral::fromTokenStream($stream);

        foreach ($importMap as $domesticName => $foreignName) {
            yield $domesticName => new self($domesticName, $foreignName, $target->getValue());
        }
    }

    /**
     * @return string
     */
    public function getDomesticName(): string
    {
        return $this->domesticName;
    }

    /**
     * @return string
     */
    public function getForeignName(): string
    {
        return $this->foreignName;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        throw new \Exception('@TODO: Import::jsonSerialize');
    }
}