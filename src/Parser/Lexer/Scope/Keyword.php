<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Source\Fragment;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Keyword
{
    /**
     * @param SourceIterator $iterator
     * @param string $keyword
     * @return null|Fragment
     */
    public static function extract(SourceIterator $iterator, string $keyword): ?Fragment
    {
        $value = $iterator->current()->getValue();

        if ($value === $keyword[0]) {
            $length = mb_strlen($keyword);

            if ($fragment = $iterator->lookAhead($length)) {
                if ($fragment->getValue() === $keyword) {
                    if ($lookAhead = $iterator->lookAhead($length + 1)) {
                        $lookAheadValue = $lookAhead->getValue();
                        if (!Identifier::is(mb_substr($lookAheadValue, $length))) {
                            $iterator->skip($length);
                            return $fragment;
                        }
                    } else {
                        $iterator->skip($length);
                        return $fragment;
                    }
                }
            }
        }

        return null;
    }
}