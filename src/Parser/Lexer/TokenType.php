<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer;

final class TokenType
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array<string, TokenType>
     */
    private static $instances = [];

    private function __construct(string $identifier) 
    {
        $this->identifier = $identifier;
    }

    private static function create(string $identifier): self
    {
        if (isset(self::$instances[$identifier])) {
            return self::$instances[$identifier];
        }

        return self::$instances[$identifier] = new TokenType($identifier);
    }

    public function __toString()
    {
        return $this->identifier;
    }

    public static function IDENTIFIER(): TokenType { return self::create('IDENTIFIER'); }

    public static function KEYWORD_IMPORT(): TokenType { return self::create('KEYWORD_IMPORT'); }
    public static function KEYWORD_FROM(): TokenType { return self::create('KEYWORD_FROM'); }

    public static function STRING_START(): TokenType { return self::create('STRING_START'); }
    public static function STRING_VALUE(): TokenType { return self::create('STRING_VALUE'); }
    public static function STRING_ESCAPE(): TokenType { return self::create('STRING_ESCAPE'); }
    public static function STRING_ESCAPED_CHARACTER(): TokenType { return self::create('STRING_ESCAPED_CHARACTER'); }
    public static function STRING_END(): TokenType { return self::create('STRING_END'); }

    public static function TAG_START(): TokenType { return self::create('TAG_START'); }
    public static function TAG_END(): TokenType { return self::create('TAG_END'); }
    public static function TAG_CLOSE(): TokenType { return self::create('TAG_CLOSE'); }

    public static function EXPRESSION_START(): TokenType { return self::create('EXPRESSION_START'); }
    public static function EXPRESSION_END(): TokenType { return self::create('EXPRESSION_END'); }

    public static function OBJECT_START(): TokenType { return self::create('OBJECT_START'); }
    public static function OBJECT_END(): TokenType { return self::create('OBJECT_END'); }
    public static function COMPUTED_KEY_START(): TokenType { return self::create('COMPUTED_KEY_START'); }
    public static function COMPUTED_KEY_END(): TokenType { return self::create('COMPUTED_KEY_END'); }

    public static function COLON(): TokenType { return self::create('COLON'); }
    public static function PERIOD(): TokenType { return self::create('PERIOD'); }
    public static function EXCLAMATION(): TokenType { return self::create('EXCLAMATION'); }
    public static function COMMA(): TokenType { return self::create('COMMA'); }
    public static function ASSIGNMENT(): TokenType { return self::create('ASSIGNMENT'); }

    public static function COMMENT_START(): TokenType { return self::create('COMMENT_START'); }
    public static function COMMENT_CONTENT(): TokenType { return self::create('COMMENT_CONTENT'); }
    public static function COMMENT_END(): TokenType { return self::create('COMMENT_END'); }

    public static function CONTENT(): TokenType { return self::create('CONTENT'); }
    public static function WHITESPACE(): TokenType { return self::create('WHITESPACE'); }
    public static function JSX_DELIMITER(): TokenType { return self::create('JSX_DELIMITER'); }

    public static function END_OF_LINE(): TokenType { return self::create('END_OF_LINE'); }
    public static function END_OF_FILE(): TokenType { return self::create('END_OF_FILE'); }
}