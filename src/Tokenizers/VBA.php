<?php
/**
 * Tokenizes VBA code.
 *
 * @author    Kevin Nowaczyk
 */
namespace PHP_CodeSniffer\Tokenizers;

define('T_THEN', 'PHPCS_T_THEN');
define('T_BEGIN', 'PHPCS_T_BEGIN');
define('T_END', 'PHPCS_T_END');
define('T_ATTRIBUTE', 'PHPCS_T_ATTRIBUTE');
define('T_OPTION', 'PHPCS_T_OPTION');
define('T_LET', 'PHPCS_T_LET');
define('T_SET', 'PHPCS_T_SET');
define('T_SUB', 'PHPCS_T_SUB');
define('T_DIM', 'PHPCS_T_DIM');
define('T_EOL', 'PHPCS_T_EOL');
define('T_IS', 'PHPCS_T_IS');
define('T_NOTHING', 'PHPCS_T_NOTHING');
define('T_NEXT', 'PHPCS_T_NEXT');
define('T_SELECT', 'PHPCS_T_SELECT');
define('T_END_FUNCTION', 'PHPCS_T_END_FUNCTION');
define('T_END_SUB', 'PHPCS_T_END_SUB');
define('T_END_PROPERTY', 'PHPCS_T_END_PROPERTY');
define('T_END_SELECT', 'PHPCS_T_END_SELECT');
define('T_LOOP', 'PHPCS_T_LOOP');
define('T_SELECT_CASE', 'PHPCS_T_SELECT_CASE');
define('T_WEND', 'PHPCS_T_WEND');
define('T_CASE_ELSE', 'PHPCS_T_CASE_ELSE');
define('T_CONCATENATE', 'PHPCS_T_CONCATENATE');

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
        'class'     => 'T_CLASS',
        'begin'     => 'T_BEGIN',
        'end'       => 'T_END',
        'function'  => 'T_FUNCTION',
        'attribute' => 'T_ATTRIBUTE',
        'option'    => 'T_OPTION',
        'implements'=> 'T_IMPLEMENTS',
        'public'    => 'T_PUBLIC',
        'private'   => 'T_PRIVATE',
        'dim'       => 'T_DIM',
        'set'       => 'T_SET',
        'let'       => 'T_LET',
        'property'  => 'T_PROPERTY',
        'end property'    => 'T_END_PROPERTY',
        'sub'       => 'T_SUB',
        'end sub'   => 'T_END_SUB',
        'end function'        => 'T_END_FUNCTION',
        'select case'     => 'T_SELECT_CASE',
        'end select'=> 'T_END_SELECT',
        'if'        => 'T_IF',
        'then'      => 'T_THEN',
        'else'      => 'T_ELSE',
        'else if'   => 'T_ELSE_IF',
        'where'     => 'T_WHERE',
        'wend'      => 'T_WEND',
        'do'        => 'T_DO',
        'loop'      => 'T_LOOP',
        'for'       => 'T_FOR',
        'for each'  => 'T_FOR_EACH',
        'next'      => 'T_NEXT',
        'as'        => 'T_AS',
        'is'        => 'T_IS',
        'nothing'   => 'T_NOTHING',
        'true'      => 'T_TRUE',
        'false'     => 'T_FALSE',
        '('         => 'T_OPEN_PARENTHESIS',
        ')'         => 'T_CLOSE_PARENTHESIS',
        '{'         => 'T_OPEN_CURLY_BRACKET',
        '}'         => 'T_CLOSE_CURLY_BRACKET',
        '['         => 'T_OPEN_SQUARE_BRACKET',
        ']'         => 'T_CLOSE_SQUARE_BRACKET',
        '.'         => 'T_OBJECT_OPERATOR',
        '+'         => 'T_PLUS',
        '-'         => 'T_MINUS',
        '*'         => 'T_MULTIPLY',
        '%'         => 'T_MODULUS',
        '/'         => 'T_DIVIDE',
        '^'         => 'T_EXPONENT',
        ','         => 'T_COMMA',
        ';'         => 'T_SEMICOLON',
        ':'         => 'T_COLON',
        '<'         => 'T_LESS_THAN',
        '>'         => 'T_GREATER_THAN',
        '<='        => 'T_IS_SMALLER_OR_EQUAL',
        '>='        => 'T_IS_GREATER_OR_EQUAL',
        '<>'        => 'T_IS_NOT_EQUAL',
        'not'       => 'T_BOOLEAN_NOT',
        'or'        => 'T_BOOLEAN_OR',
        'and'       => 'T_BOOLEAN_AND',
        '='         => 'T_EQUAL',
        ':='        => 'T_ASSIGNMENT',
        '&'         => 'T_CONCATENATE',
        "'"         => 'T_COMMENT',
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
            $this->combineComments($tokens, $stackPtr);
            $token = $tokens[$stackPtr];
            $finalTokens[$newStackPtr] = $token;
            $newStackPtr++;
            // Convert numbers, including decimals.
            if ($token['code'] === T_STRING
                || $token['code'] === T_OBJECT_OPERATOR
            ) {
                $newContent  = '';
                $oldStackPtr = $stackPtr;
                while (preg_match('|^[0-9\.]+$|', $tokens[$stackPtr]['content']) !== 0) {
                    $newContent .= $tokens[$stackPtr]['content'];
                    $stackPtr++;
                }
                if ($newContent !== '' && $newContent !== '.') {
                    $finalTokens[($newStackPtr - 1)]['content'] = $newContent;
                    if (ctype_digit($newContent) === true) {
                        $finalTokens[($newStackPtr - 1)]['code'] = constant('T_LNUMBER');
                        $finalTokens[($newStackPtr - 1)]['type'] = 'T_LNUMBER';
                    } else {
                        $finalTokens[($newStackPtr - 1)]['code'] = constant('T_DNUMBER');
                        $finalTokens[($newStackPtr - 1)]['type'] = 'T_DNUMBER';
                    }
                    $stackPtr--;
                    continue;
                } else {
                    $stackPtr = $oldStackPtr;
                }
            }//end if
            // Convert the token after an object operator into a string, in most cases.
            if ($token['code'] === T_OBJECT_OPERATOR) {
                for ($i = ($stackPtr + 1); $i < $numTokens; $i++) {
                    if (isset(Util\Tokens::$emptyTokens[$tokens[$i]['code']]) === true) {
                        continue;
                    }
                    if ($tokens[$i]['code']    !== T_LNUMBER
                        && $tokens[$i]['code'] !== T_DNUMBER
                    ) {
                        $tokens[$i]['code'] = T_STRING;
                        $tokens[$i]['type'] = 'T_STRING';
                    }
                    break;
                }
            }
        }//end for
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** END TOKENIZING ***".PHP_EOL;
        }
        return $finalTokens;
    }
}
