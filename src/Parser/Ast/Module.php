<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Module implements \JsonSerializable
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var array<string, Import>
     */
    private $imports;

    /**
     * @var mixed
     */
    private $export;

    /**
     * @param Source $source
     * @param array<string, Import> $imports
     * @param mixed $export
     */
    private function __construct(
        Source $source,
        array $imports,
        $export
    ) {
        $this->source = $source;
        $this->imports = $imports;
        $this->export = $export;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);

        $imports = [];
        while ($stream->valid()) {
            if ($stream->current()->getType() === TokenType::KEYWORD_IMPORT()) {
                $name = null;
                $value = null;
                $stream->next();
                Util::skipWhiteSpaceAndComments($stream);

                if ($stream->current()->getType() === TokenType::IDENTIFIER()) {
                    $name = $stream->current()->getValue();
                    $stream->next();
                    Util::skipWhiteSpaceAndComments($stream);
                }
                else {
                    throw new \Exception('@TODO: Unexpected Token');
                }

                Util::expect($stream, TokenType::KEYWORD_FROM());
                Util::skipWhiteSpaceAndComments($stream);

                Util::expect($stream, TokenType::STRING_START());
                Util::skipWhiteSpaceAndComments($stream);

                if ($stream->current()->getType() === TokenType::STRING_VALUE()) {
                    $value = $stream->current()->getValue();
                    $stream->next();
                    Util::skipWhiteSpaceAndComments($stream);
                } 
                else {
                    throw new \Exception('@TODO: Unexpected Token');
                }

                Util::expect($stream, TokenType::STRING_END());
                Util::skipWhiteSpaceAndComments($stream);

                // @TODO: Actually handle imports
                $imports[$name] = Import::createFromUriString($value);
            }
            else {
                break;
            }
        }

        return new self(
            $stream->getSource(),
            $imports,
            Tag::createFromTokenStream($stream)
        );
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @return array<string, Import>
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @return mixed
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Module',
            'properties' => [
                'source' => $this->source,
                'imports' => $this->imports,
                'export' => $this->export
            ]
        ];
    }
}