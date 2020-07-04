<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Debug\Cli;

use PackageFactory\ComponentEngine\Parser\Lexer\LineIterator;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Source\Source;

final class SourcePrinter
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var null|Token
     */
    private $token;

    /**
     * @var null|int
     */
    private $fromRow;

    /**
     * @var null|int
     */
    private $toRow;

    /**
     * @param Source $source
     * @param null|Token $token
     * @param integer $fromRow
     * @param integer $toRow
     */
    private function __construct(
        Source $source,
        ?Token $token,
        ?int $fromRow,
        ?int $toRow
    ) {
        $this->source = $source;
        $this->token = $token;
        $this->fromRow = $fromRow;
        $this->toRow = $toRow;
    }

    /**
     * @param Source $source
     * @return self
     */
    public static function fromSource(Source $source): self
    {
        return new self($source, null, null, null);
    }

    /**
     * @param Token $token
     * @return self
     */
    public static function fromToken(Token $token): self
    {
        return new self(
            $token->getSource(), 
            $token, 
            $token->getStart()->getRowIndex() - 2,
            $token->getStart()->getRowIndex() + 2
        );
    }

    /**
     * @param int $fromRow
     * @return self
     */
    public function withFromRow(int $fromRow): self
    {
        return new self(
            $this->source, 
            $this->token, 
            $fromRow,
            $this->toRow
        );
    }

    /**
     * @param int $toRow
     * @return self
     */
    public function withToRow(int $toRow): self
    {
        return new self(
            $this->source, 
            $this->token, 
            $this->fromRow,
            $toRow
        );
    }

    /**
     * @return void
     */
    public function print(): void
    {
        foreach (LineIterator::fromSource($this->source) as $line) {
            if (
                ($this->fromRow === null || $line->getNumber() - 1 >= $this->fromRow)
                && ($this->toRow === null || $line->getNumber() - 1 <= $this->toRow)
            ) {
                print(str_pad((string) $line->getNumber(), 4, ' ', STR_PAD_LEFT) . ' | ');

                foreach ($line as $token) {
                    if ($this->token !== null && $this->token->equals($token)) {
                        print("\033[91m" . $token->getValue() . "\033[0m");
                    } else {
                        print($token->getValue());
                    }
                }

                print(PHP_EOL);
            }
        }
    }
}