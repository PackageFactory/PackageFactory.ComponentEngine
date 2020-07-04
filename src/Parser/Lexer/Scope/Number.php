<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Capture;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Number
{
    const DIGITS_BIN = ['0', '1'];
    const DIGITS_OCT = ['0', '1', '2', '3', '4', '5', '6', '7'];
    const DIGITS_DEC = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const DIGITS_HEX = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];

    public static function is(string $char): bool
    {
        return ctype_digit($char);
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        if ($iterator->current()->getValue() === '0') {
            if ($lookAhead = $iterator->lookAhead(2)) {
                $lookAhead = $lookAhead->getValue();
            } else {
                $lookAhead = '';
            }

            if ($lookAhead === '0b' || $lookAhead === '0B') {
                yield from self::tokenizeBinary($iterator);
            } elseif ($lookAhead === '0o') {
                yield from self::tokenizeOctal($iterator);
            } elseif ($lookAhead === '0x') {
                yield from self::tokenizeHexadecimal($iterator);
            } else {
                yield from self::tokenizeDecimal($iterator);
            }
        } else {
            yield from self::tokenizeDecimal($iterator);
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeBinary(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();

            if (in_array($value, self::DIGITS_BIN)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER());
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeOctal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();
            if (in_array($value, self::DIGITS_OCT)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER());
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeHexadecimal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::fromFragment($iterator->current());
        $iterator->next();
        $capture->append($iterator->current());
        $iterator->next();

        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();
            if (in_array($value, self::DIGITS_HEX)) {
                $capture->append($iterator->current());
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER());
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeDecimal(SourceIterator $iterator): \Iterator
    {
        $capture = Capture::createEmpty();
        $floatingPoint = false;
        $exponentiation = false;

        while ($iterator->valid()) {
            $value = $iterator->current()->getValue();
            if (in_array($value, self::DIGITS_DEC)) {
                $capture->append($iterator->current());
            } elseif ($value === '.' && !$floatingPoint && !$exponentiation) {
                $capture->append($iterator->current());
                $floatingPoint = true;
            } elseif (($value === 'e' || $value === 'E') && !$exponentiation) {
                $capture->append($iterator->current());
                $exponentiation = true;
            } else {
                break;
            }
            $iterator->next();
        }

        yield from $capture->flush(TokenType::NUMBER());
    }
}