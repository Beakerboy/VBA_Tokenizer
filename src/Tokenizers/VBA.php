<?php
/**
 * Tokenizes VBA code.
 *
 * @author    Kevin Nowaczyk
 */
namespace PHP_CodeSniffer\Tokenizers;

define('T_ATTRIBUTE', 'PHPCS_T_ATTRIBUTE');
define('T_BEGIN', 'PHPCS_T_BEGIN');
define('T_CASE_ELSE', 'PHPCS_T_CASE_ELSE');
define('T_CONCATENATE', 'PHPCS_T_CONCATENATE');
define('T_DIM', 'PHPCS_T_DIM');
define('T_EACH', 'PHPCS_T_EACH');
define('T_END', 'PHPCS_T_END');
define('T_END_FUNCTION', 'PHPCS_T_END_FUNCTION');
define('T_END_SUB', 'PHPCS_T_END_SUB');
define('T_END_PROPERTY', 'PHPCS_T_END_PROPERTY');
define('T_END_SELECT', 'PHPCS_T_END_SELECT');
define('T_EOL', 'PHPCS_T_EOL');
define('T_IS', 'PHPCS_T_IS');
define('T_LET', 'PHPCS_T_LET');
define('T_LOOP', 'PHPCS_T_LOOP');
define('T_NOTHING', 'PHPCS_T_NOTHING');
define('T_NEXT', 'PHPCS_T_NEXT');
define('T_OPTION', 'PHPCS_T_OPTION');
define('T_SELECT', 'PHPCS_T_SELECT');
define('T_SELECT_CASE', 'PHPCS_T_SELECT_CASE');
define('T_SET', 'PHPCS_T_SET');
define('T_SUB', 'PHPCS_T_SUB');
define('T_THEN', 'PHPCS_T_THEN');
define('T_WEND', 'PHPCS_T_WEND');

use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Exceptions\TokenizerException;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Tokenizers\TokenizerBase;

class VBA extends TokenizerBase
{
    /**
     * A list of tokens that are allowed to open a scope.
     *
     * This array also contains information about what kind of token the scope
     * opener uses to open and close the scope, if the token strictly requires
     * an opener, if the token can share a scope closer, and who it can be shared
     * with. An example of a token that shares a scope closer is a CASE scope.
     *
     * @var array
     */
    public $scopeOpeners = [
        T_IF       => [
            'start'  => [T_THEN => T_THEN],
            'end'    => [T_ENDIF => T_ENDIF],
            'strict' => false,
            'shared' => true,
            'with'   => [
                T_ELSE => T_ELSE,
                T_ELSEIF => T_ELSEIF,
            ],
        ],
        T_ELSE     => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [T_ENDIF => T_ENDIF],
            'strict' => false,
            'shared' => true,
            'with'   => [
                T_ELSE => T_ELSE,
                T_ELSEIF => T_ELSEIF,
            ],
        ],
        T_FOR      => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [T_NEXT => T_NEXT],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_FUNCTION => [
            'start'  => [T_EOL=> T_EOL],
            'end'    => [T_END_FUNCTION => T_END_FUNCTION],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_WHILE    => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [T_WEND => T_WEND],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_DO       => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [T_LOOP => T_LOOP],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ],
        T_SELECT_CASE   => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [T_END_SELECT => T_END_SELECT],
            'strict' => true,
            'shared' => true,
            'with'   => [T_CASE => T_CASE],
        ],
        T_CASE     => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [
                T_END_SELECT    => T_END_SELECT,
            ],
            'strict' => true,
            'shared' => true,
            'with'   => [
                T_ELSE => T_ELSE,
                T_END_SELECT    => T_END_SELECT,
            ],
        ],
        T_CASE_ELSE     => [
            'start'  => [T_EOL => T_EOL],
            'end'    => [
                T_END_SELECT    => T_END_SELECT,
            ],
            'strict' => true,
            'shared' => true,
            'with'   => [
                T_ELSE => T_ELSE,
                T_END_SELECT    => T_END_SELECT,
            ],
        ],
    ];
    /**
     * A list of tokens that end the scope.
     *
     * This array is just a unique collection of the end tokens
     * from the _scopeOpeners array. The data is duplicated here to
     * save time during parsing of the file.
     *
     * @var array
     */
    public $endScopeTokens = [
        T_WEND         => T_WEND,
        T_BREAK        => T_BREAK,
        T_ELSE         => T_ELSE,
        T_END_SELECT   => T_END_SELECT,
        T_END_FUNCTION => T_END_FUNCTION,
        T_END_SUB      => T_END_SUB,
        T_END_PROPERTY => T_END_PROPERTY,
        T_LOOP         => T_LOOP,
    ];
    /**
     * A list of special VBA tokens and their types.
     *
     * @var array
     */
    protected $tokenValues = [
        'and'          => 'T_BOOLEAN_AND',
        'as'           => 'T_AS',
        'attribute'    => 'T_ATTRIBUTE',
        'begin'        => 'T_BEGIN',
        'class'        => 'T_CLASS',
        'dim'          => 'T_DIM',
        'do'           => 'T_DO',
        'each'         => 'T_EACH',
        'else'         => 'T_ELSE',
        'elseif'       => 'T_ELSEIF',
        'end'          => 'T_END',
        'end function' => 'T_END_FUNCTION',
        'end property' => 'T_END_PROPERTY',
        'end select'   => 'T_END_SELECT',
        'end sub'      => 'T_END_SUB',
        'false'        => 'T_FALSE',
        'for'          => 'T_FOR',
        'for each'     => 'T_FOR_EACH',
        'function'     => 'T_FUNCTION',
        'option'       => 'T_OPTION',
        'if'           => 'T_IF',
        'implements'   => 'T_IMPLEMENTS',
        'is'           => 'T_IS',
        'let'          => 'T_LET',
        'loop'         => 'T_LOOP',
        'next'         => 'T_NEXT',
        'not'          => 'T_BOOLEAN_NOT',
        'nothing'      => 'T_NOTHING',
        'or'           => 'T_BOOLEAN_OR',
        'public'       => 'T_PUBLIC',
        'private'      => 'T_PRIVATE',
        'set'          => 'T_SET',
        'property'     => 'T_PROPERTY',
        'sub'          => 'T_SUB',
        'select case'  => 'T_SELECT_CASE',
        'then'         => 'T_THEN',
        'true'         => 'T_TRUE',
        'wend'         => 'T_WEND',
        'where'        => 'T_WHERE',
        '('            => 'T_OPEN_PARENTHESIS',
        ')'            => 'T_CLOSE_PARENTHESIS',
        '{'            => 'T_OPEN_CURLY_BRACKET',
        '}'            => 'T_CLOSE_CURLY_BRACKET',
        '['            => 'T_OPEN_SQUARE_BRACKET',
        ']'            => 'T_CLOSE_SQUARE_BRACKET',
        '.'            => 'T_OBJECT_OPERATOR',
        '+'            => 'T_PLUS',
        '-'            => 'T_MINUS',
        '*'            => 'T_MULTIPLY',
        '%'            => 'T_MODULUS',
        '/'            => 'T_DIVIDE',
        '^'            => 'T_EXPONENT',
        ','            => 'T_COMMA',
        ';'            => 'T_SEMICOLON',
        ':'            => 'T_COLON',
        '<'            => 'T_LESS_THAN',
        '>'            => 'T_GREATER_THAN',
        '<='           => 'T_IS_SMALLER_OR_EQUAL',
        '>='           => 'T_IS_GREATER_OR_EQUAL',
        '<>'           => 'T_IS_NOT_EQUAL',
        '='            => 'T_EQUAL',
        ':='           => 'T_ASSIGNMENT',
        '&'            => 'T_CONCATENATE',
    ];
    /**
     * A list string delimiters.
     *
     * @var array
     */
    protected $stringTokens = [
        '"'  => '"',
    ];
    /**
     * A list tokens that start and end comments.
     *
     * @var array
     */
    protected $commentTokens = [
        '\''  => null,
    ];
    
    protected $escapeCharacter = '"';

    protected $multiToken = [
        'T_END' => [
            'T_SELECT',
            'T_PROPERTY',
            'T_FUNCTION',
            'T_SUB',
        ],
        'T_FOR' => [
            'T_EACH',
        ],
        'T_SELECT' => [
            'T_CASE',
        ],
    ];
    /**
     * Creates an array of tokens when given some VBA code.
     *
     * Starts by using token_get_all() but does a lot of extra processing
     * to insert information about the context of the token.
     *
     * @param string $string The string to tokenize.
     *
     * @return array
     */
    public function tokenize($string)
    {
        $tokens = $this->tokensFromCharacterStream($string);

        /*
            Now that we have done some basic tokenizing, we need to
            modify the tokens to join some together and split some apart
            so they match what the PHP tokenizer does.
        */
        $finalTokens = [];
        $newStackPtr = 0;
        $numTokens   = count($tokens);
        for ($stackPtr = 0; $stackPtr < $numTokens; $stackPtr++) {
            $token = $tokens[$stackPtr];
            // Convert multi-word tokens from token-whitespace-token
            // to the correct single token.
            if (isset($multiToken[$token['type']])
                && $tokens[$stackPtr + 1]['content'] === ' '
                && in_array($tokens[$stackPtr + 2]['type'], $multiToken[$token['type']])
            ) {
                $content = $token['content'] . ' ' . $tokens[$stackPtr + 2]['content'];
                $finalTokens[$newStackPtr] = $this->simpleToken($tokenValues[strtolower($content)], $content);
                $stackPtr += 2;
                $newStackPtr++;
            } else {
                $finalTokens[$newStackPtr] = $token;
                $newStackPtr++;
            }
            
        }//end for
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** END TOKENIZING ***".PHP_EOL;
        }
        return $finalTokens;
    }
}
