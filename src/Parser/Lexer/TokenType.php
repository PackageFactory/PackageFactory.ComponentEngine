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

    // IDENTIFIER
    public static function IDENTIFIER(): TokenType { return self::create('IDENTIFIER'); }

    // NUMBER
    public static function NUMBER(): TokenType { return self::create('NUMBER'); }

    // MODULE
    public static function MODULE_KEYWORD_IMPORT(): TokenType { return self::create('MODULE_KEYWORD_IMPORT'); }
    public static function MODULE_KEYWORD_FROM(): TokenType { return self::create('MODULE_KEYWORD_FROM'); }
    public static function MODULE_KEYWORD_AS(): TokenType { return self::create('MODULE_KEYWORD_AS'); }
    public static function MODULE_KEYWORD_CONST(): TokenType { return self::create('MODULE_KEYWORD_CONST'); }
    public static function MODULE_KEYWORD_EXPORT(): TokenType { return self::create('MODULE_KEYWORD_EXPORT'); }
    public static function MODULE_KEYWORD_DEFAULT(): TokenType { return self::create('MODULE_KEYWORD_DEFAULT'); }
    public static function MODULE_ASSIGNMENT(): TokenType { return self::create('MODULE_ASSIGNMENT'); }
    public static function MODULE_WILDCARD(): TokenType { return self::create('MODULE_WILDCARD'); }
    public static function MODULE_AFX_START(): TokenType { return self::create('MODULE_AFX_START'); }
    public static function MODULE_AFX_END(): TokenType { return self::create('MODULE_AFX_END'); }

    // AFX
    public static function AFX_TAG_START(): TokenType { return self::create('AFX_TAG_START'); }
    public static function AFX_TAG_END(): TokenType { return self::create('AFX_TAG_END'); }
    public static function AFX_TAG_CLOSE(): TokenType { return self::create('AFX_TAG_CLOSE'); }
    public static function AFX_TAG_CONTENT(): TokenType { return self::create('AFX_TAG_CONTENT'); }
    public static function AFX_ATTRIBUTE_ASSIGNMENT(): TokenType { return self::create('AFX_ATTRIBUTE_ASSIGNMENT'); }
    public static function AFX_EXPRESSION_START(): TokenType { return self::create('AFX_EXPRESSION_START'); }
    public static function AFX_EXPRESSION_END(): TokenType { return self::create('AFX_EXPRESSION_END'); }
    public static function AFX_EMBED_START(): TokenType { return self::create('AFX_EMBED_START'); }
    public static function AFX_EMBED_END(): TokenType { return self::create('AFX_EMBED_END'); }

    // EXPRESSION
    public static function KEYWORD_TRUE(): TokenType { return self::create('KEYWORD_TRUE'); }
    public static function KEYWORD_FALSE(): TokenType { return self::create('KEYWORD_FALSE'); }
    public static function KEYWORD_NULL(): TokenType { return self::create('KEYWORD_NULL'); }
    public static function OPERATOR_LOGICAL_NOT(): TokenType { return self::create('OPERATOR_LOGICAL_NOT'); }
    public static function OPERATOR_LOGICAL_AND(): TokenType { return self::create('OPERATOR_LOGICAL_AND'); }
    public static function OPERATOR_LOGICAL_OR(): TokenType { return self::create('OPERATOR_LOGICAL_OR'); }
    public static function OPERATOR_SPREAD(): TokenType { return self::create('OPERATOR_SPREAD'); }
    public static function OPERATOR_ADD(): TokenType { return self::create('OPERATOR_ADD'); }
    public static function OPERATOR_SUBTRACT(): TokenType { return self::create('OPERATOR_SUBTRACT'); }
    public static function OPERATOR_MULTIPLY(): TokenType { return self::create('OPERATOR_MULTIPLY'); }
    public static function OPERATOR_DIVIDE(): TokenType { return self::create('OPERATOR_DIVIDE'); }
    public static function OPERATOR_MODULO(): TokenType { return self::create('OPERATOR_MODULO'); }
    public static function OPERATOR_OPTCHAIN(): TokenType { return self::create('OPERATOR_OPTCHAIN'); }
    public static function OPERATOR_NULLISH_COALESCE(): TokenType { return self::create('OPERATOR_NULLISH_COALESCE'); }
    public static function COMPARATOR_EQ(): TokenType { return self::create('COMPARATOR_EQ'); }
    public static function COMPARATOR_GT(): TokenType { return self::create('COMPARATOR_GT'); }
    public static function COMPARATOR_GTE(): TokenType { return self::create('COMPARATOR_GTE'); }
    public static function COMPARATOR_LT(): TokenType { return self::create('COMPARATOR_LT'); }
    public static function COMPARATOR_LTE(): TokenType { return self::create('COMPARATOR_LTE'); }
    public static function ARROW(): TokenType { return self::create('ARROW'); }

    // STRING LITERALS
    public static function STRING_LITERAL_START(): TokenType { return self::create('STRING_LITERAL_START'); }
    public static function STRING_LITERAL_CONTENT(): TokenType { return self::create('STRING_LITERAL_CONTENT'); }
    public static function STRING_LITERAL_ESCAPE(): TokenType { return self::create('STRING_LITERAL_ESCAPE'); }
    public static function STRING_LITERAL_ESCAPED_CHARACTER(): TokenType { return self::create('STRING_LITERAL_ESCAPED_CHARACTER'); }
    public static function STRING_LITERAL_END(): TokenType { return self::create('STRING_LITERAL_END'); }

    // TEMPLATE LITERALS
    public static function TEMPLATE_LITERAL_START(): TokenType { return self::create('TEMPLATE_LITERAL_START'); }
    public static function TEMPLATE_LITERAL_CONTENT(): TokenType { return self::create('TEMPLATE_LITERAL_CONTENT'); }
    public static function TEMPLATE_LITERAL_ESCAPE(): TokenType { return self::create('TEMPLATE_LITERAL_ESCAPE'); }
    public static function TEMPLATE_LITERAL_ESCAPED_CHARACTER(): TokenType { return self::create('TEMPLATE_LITERAL_ESCAPED_CHARACTER'); }
    public static function TEMPLATE_LITERAL_INTERPOLATION_START(): TokenType { return self::create('TEMPLATE_LITERAL_INTERPOLATION_START'); }
    public static function TEMPLATE_LITERAL_INTERPOLATION_END(): TokenType { return self::create('TEMPLATE_LITERAL_INTERPOLATION_END'); }
    public static function TEMPLATE_LITERAL_END(): TokenType { return self::create('TEMPLATE_LITERAL_END'); }

    // BRACKETS
    public static function BRACKETS_ROUND_OPEN(): TokenType { return self::create('BRACKETS_ROUND_OPEN'); }
    public static function BRACKETS_ROUND_CLOSE(): TokenType { return self::create('BRACKETS_ROUND_CLOSE'); }
    public static function BRACKETS_SQUARE_OPEN(): TokenType { return self::create('BRACKETS_SQUARE_OPEN'); }
    public static function BRACKETS_SQUARE_CLOSE(): TokenType { return self::create('BRACKETS_SQUARE_CLOSE'); }
    public static function BRACKETS_CURLY_OPEN(): TokenType { return self::create('BRACKETS_CURLY_OPEN'); }
    public static function BRACKETS_CURLY_CLOSE(): TokenType { return self::create('BRACKETS_CURLY_CLOSE'); }

    // AMBIGUOUS PUNCTUATION
    public static function PERIOD(): TokenType { return self::create('PERIOD'); }
    public static function COLON(): TokenType { return self::create('COLON'); }
    public static function QUESTIONMARK(): TokenType { return self::create('QUESTIONMARK'); }
    public static function COMMA(): TokenType { return self::create('COMMA'); }

    // COMMENT
    public static function COMMENT_START(): TokenType { return self::create('COMMENT_START'); }
    public static function COMMENT_CONTENT(): TokenType { return self::create('COMMENT_CONTENT'); }
    public static function COMMENT_END(): TokenType { return self::create('COMMENT_END'); }

    // WHITESPACE
    public static function WHITESPACE(): TokenType { return self::create('WHITESPACE'); }
    public static function END_OF_LINE(): TokenType { return self::create('END_OF_LINE'); }
}