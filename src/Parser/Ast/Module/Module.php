<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Module;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Util;

final class Module implements \JsonSerializable
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var array|Import[]
     */
    private $imports;

    /**
     * @var array|Export[]
     */
    private $exports;

    /**
     * @var array|Constant[]
     */
    private $constants;

    /**
     * @param array|Import[] $imports
     * @param array|Export[] $exports
     * @param array|Constant[] $constants
     */
    private function __construct(
        Source $source,
        array $imports,
        array $exports,
        array $constants
    ) {
        $this->source = $source;
        $this->imports = $imports;
        $this->exports = $exports;
        $this->constants = $constants;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        if (!$stream->valid()) {
            throw new \Exception('@TODO: Unexpected end of file');
        }

        $source = $stream->current()->getSource();
        Util::skipWhiteSpaceAndComments($stream);

        $imports = [];
        $exports = [];
        $constants = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);
            if (!$stream->valid()) {
                throw new \Exception('@TODO: Unexpected end of file');
            }

            switch ($stream->current()->getType()) {
                case TokenType::MODULE_KEYWORD_IMPORT():
                    foreach (Import::createFromTokenStream($stream) as $import) {
                        $imports[(string) $import->getDomesticName()] = $import;
                    }
                    break;
                case TokenType::MODULE_KEYWORD_EXPORT():
                    $export = Export::createFromTokenStream($stream);
                    $exports[(string) $export->getName()] = $export;
                    break;
                case TokenType::MODULE_KEYWORD_CONST():
                    $constants[] = Constant::createFromTokenStream($stream);
                    break;
                default:
                    throw new \Exception('@TODO: Unexpected Token: ' . $stream->current()->getType());
            }
        }

        return new self($source, $imports, $exports, $constants);
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @return array|Import[]
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @return array|Export[]
     */
    public function getExports(): array
    {
        return $this->exports;
    }

    /**
     * @param string $name
     * @return Export
     */
    public function getExport(string $name): Export
    {
        if (isset($this->exports[$name])) {
            return $this->exports[$name];
        }

        throw new \Exception('@TODO: Export does not exist: ' . $name);
    }

    /**
     * @return array|Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        throw new \Exception('@TODO: Module::jsonSerialize');
    }
}