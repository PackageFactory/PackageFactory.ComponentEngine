<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Exception;

use PackageFactory\ComponentEngine\Parser\Ast\Term;
use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;

final class ParserFailed extends \Exception
{
    /**
     * @var null|Token
     */
    private $token;

    /**
     * @param null|Token $token
     * @param string $message
     */
    private function __construct(?Token $token, string $message)
    {
        parent::__construct('Parser failed: ' . $message);

        $this->token = $token;
    }

    /**
     * @return null|Token
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }

    /**
     * @param Token $token
     * @param array|TokenType[] $expectedTypes
     * @return self
     */
    public static function becauseOfUnexpectedToken(
        Token $token, 
        array $expectedTypes = []
    ): self {
        $message = sprintf(
            'Encountered unexpected token "%s" of type %s.',
            $token->getValue(),
            $token->getType()
        );

        if ($count = count($expectedTypes)) {
            if ($count > 1) {
                $last = array_pop($expectedTypes);

                $message .= sprintf(
                    ' Expected one of %s or %s.',
                    join(', ', $expectedTypes),
                    $last
                );
            } else {
                $message .= sprintf(
                    ' Expected %s.',
                    $expectedTypes[0]
                );
            }
        }

        return new self($token, $message);
    }

    /**
     * @param Token $token
     * @param Term $term
     * @param array<int, class-string> $expectedTypes
     * @return self
     */
    public static function becauseOfUnexpectedTerm(
        Token $token,
        Term $term,
        array $expectedTypes = []
    ): self {
        $message = sprintf(
            'Encountered unexpected term of type %s.',
            (new \ReflectionClass($term))->getShortName()
        );

        if ($count = count($expectedTypes)) {
            if ($count > 1) {
                $last = array_pop($expectedTypes);

                $message .= sprintf(
                    ' Expected one of %s or %s.',
                    join(', ', array_map(
                        function (string $type) {
                            /** @var class-string $type */
                            return (new \ReflectionClass($type))->getShortName();
                        }, 
                        $expectedTypes
                    )),
                    $last
                );
            } else {
                $message .= sprintf(
                    ' Expected %s.',
                    (new \ReflectionClass($expectedTypes[0]))->getShortName()
                );
            }
        }

        return new self($token, $message);
    }

    public static function becauseOfUnexpectedClosingTag(Token $token): self
    {
        return new self($token, 'Encountered unexpected closing tag.');
    }

    public static function becauseOfUnexpectedEndOfFile(TokenStream $stream): self
    {
        return new self($stream->getLast(), 'Encountered unexpected end of file.');
    }

    public static function becauseOfUnknownOperator(Token $token): self
    {
        return new self(
            $token, 
            sprintf(
                'Encountered unknown operator "%s".',
                $token->getValue()
            )
        );
    }
}