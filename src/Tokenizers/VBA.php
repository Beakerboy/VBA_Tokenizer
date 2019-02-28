<?php
/**
 * Tokenizes VBA code.
 *
 * @author    Kevin Nowaczyk
 */
namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Exceptions\TokenizerException;
use PHP_CodeSniffer\Config;

class VBA extends Tokenizer
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
        T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
        T_BREAK               => T_BREAK,
    ];

    /**
     * A list of special VBA tokens and their types.
     *
     * @var array
     */
    protected $tokenValues = [
        'CLASS'     => 'T_CLASS',
        'BEGIN'     => 'T_BEGIN',
        'END'       => 'T_END',
        'Function'  => 'T_FUNCTION',
        'Attribute' => 'T_ATTRIBUTE',
        'Option'    => 'T_OPTION',
        'Implements'=> 'T_IMPLEMENTS',
        'Public'    => 'T_PUBLIC',
        'Private'   => 'T_PRIVATE',
        'Dim'       => 'T_DIM',
        'Set'       => 'T_SET',
        'Let'       => 'T_LET',
        'Property'  => 'T_PROPERTY',
        'End Property'    => 'T_END_PROPERTY',
        'Sub'       => 'T_SUB',
        'Function'  => 'T_FUNCTION',
        'End Sub'   => 'T_END_SUB',
        'End Function'        => 'T_END_FUNCTION',
        'Select Case'     => 'T_SELECT_CASE',
        'End Select'=> 'T_END_SELECT',
        'If'        => 'T_IF',
        'Then'      => 'T_THEN',
        'Else'      => 'T_ELSE',
        'Else If'   => 'T_ELSE_IF',
        'Where'     => 'T_WHERE',
        'Wend'      => 'T_WEND',
        'Do'        => 'T_DO',
        'Loop'      => 'T_LOOP',
        'For'       => 'T_FOR',
        'For Each'  => 'T_FOR_EACH',
        'Next'      => 'T_NEXT',
        'As'        => 'T_AS',
        'Is'        => 'T_IS',
        'Nothing'   => 'T_NOTHING',
        'True'      => 'T_TRUE',
        'False'     => 'T_FALSE',
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
        'Not'       => 'T_BOOLEAN_NOT',
        'Or'        => 'T_BOOLEAN_OR',
        'And'       => 'T_BOOLEAN_AND',
        '='         => 'T_EQUAL',
        ':='        => 'T_ASSIGNMENT',
        '&'         => 'T_CONCATINATE',
        '\''        => 'T_COMMENT',
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
    protected function tokenize($string)
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START VBA TOKENIZING ***".PHP_EOL;
        }
        
        $maxTokenLength = 0;
        foreach ($this->tokenValues as $token => $values) {
            if (strlen($token) > $maxTokenLength) {
                $maxTokenLength = strlen($token);
            }
        }
        
        $tokens          = [];
        $inString        = '';
        $stringChar      = null;
        $inComment       = '';
        $buffer          = '';
        $preStringBuffer = '';
        $cleanBuffer     = false;
        $commentTokenizer = new Comment();
        
        $tokens[] = [
            'code'    => T_OPEN_TAG,
            'type'    => 'T_OPEN_TAG',
            'content' => '',
        ];
        
        // Convert newlines to single characters for ease of
        // processing. We will change them back later.
        $string = str_replace($this->eolChar, "\n", $string);
        
        $chars    = str_split($string);
        $numChars = count($chars);
        for ($i = 0; $i < $numChars; $i++) {
            $char = $chars[$i];
            
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                $content       = Util\Common::prepareForOutput($char);
                $bufferContent = Util\Common::prepareForOutput($buffer);
                
                // Don't know what these do.
                if ($inString !== '') {
                    echo "\t";
                }
                if ($inComment !== '') {
                    echo "\t";
                }
                echo "\tProcess char $i => $content (buffer: $bufferContent)".PHP_EOL;
            }//end if
            
            // We separate the buffer into either strings or whitespace
            if ($inString === '' && $inComment === '' && $buffer !== '') {
                // If the buffer only has whitespace and we are about to
                // add a character, store the whitespace first.
                if (trim($char) !== '' && trim($buffer) === '') {
                    $tokens[] = [
                        'code'    => T_WHITESPACE,
                        'type'    => 'T_WHITESPACE',
                        'content' => str_replace("\n", $this->eolChar, $buffer),
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput($buffer);
                        echo "\t=> Added token T_WHITESPACE ($content)".PHP_EOL;
                    }
                    $buffer = '';
                }
                // If the buffer is not whitespace and we are about to
                // add a whitespace character, store the content first.
                if ($inString === ''
                    && $inComment === ''
                    && trim($char) === ''
                    && trim($buffer) !== ''
                ) {
                    $tokens[] = [
                        'code'    => T_STRING,
                        'type'    => 'T_STRING',
                        'content' => str_replace("\n", $this->eolChar, $buffer),
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput($buffer);
                        echo "\t=> Added token T_STRING ($content)".PHP_EOL;
                    }
                    $buffer = '';
                }
            }//end if
            
            // Process strings.
            if ($inComment === '' && isset($this->stringTokens[$char]) === true) {
                if ($inString === $char) {
                    // This could be the end of the string, but make sure it
                    // is not escaped first.
                    $escapes = 0;
                    for ($x = ($i - 1); $x >= 0; $x--) {
                        if ($chars[$x] !== '\\') {
                            break;
                        }
                        $escapes++;
                    }
                    if ($escapes === 0 || ($escapes % 2) === 0) {
                        // There is an even number escape chars,
                        // so this is not escaped, it is the end of the string.
                        $tokens[] = [
                            'code'    => T_CONSTANT_ENCAPSED_STRING,
                            'type'    => 'T_CONSTANT_ENCAPSED_STRING',
                            'content' => str_replace("\n", $this->eolChar, $buffer).$char,
                        ];
                        if (PHP_CODESNIFFER_VERBOSITY > 1) {
                            echo "\t\t* found end of string *".PHP_EOL;
                            $content = Util\Common::prepareForOutput($buffer.$char);
                            echo "\t=> Added token T_CONSTANT_ENCAPSED_STRING ($content)".PHP_EOL;
                        }
                        $buffer          = '';
                        $preStringBuffer = '';
                        $inString        = '';
                        $stringChar      = null;
                        continue;
                    }//end if
                } elseif ($inString === '') {
                    $inString        = $char;
                    $stringChar      = $i;
                    $preStringBuffer = $buffer;
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo "\t\t* looking for string closer *".PHP_EOL;
                    }
                }//end if
            }//end if
            
            if ($inString !== '' && $char === "\n") {
                // Unless this newline character is escaped, the string did not
                // end before the end of the line, which means it probably
                // wasn't a string at all (maybe a regex).
                if ($chars[($i - 1)] !== '\\') {
                    $i      = $stringChar;
                    $buffer = $preStringBuffer;
                    $preStringBuffer = '';
                    $inString        = '';
                    $stringChar      = null;
                    $char            = $chars[$i];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo "\t\t* found newline before end of string, bailing *".PHP_EOL;
                    }
                }
            }
            
            $buffer .= $char;
            // We don't look for special tokens inside strings,
            // so if we are in a string, we can continue here now
            // that the current char is in the buffer.
            if ($inString !== '') {
                continue;
            }
            
            // Check for known tokens, but ignore tokens found that are not at
            // the end of a string, like FOR and this.FORmat.
            if (isset($this->tokenValues[strtolower($buffer)]) === true
                && (preg_match('|[a-zA-z0-9_]|', $char) === 0
                || isset($chars[($i + 1)]) === false
                || preg_match('|[a-zA-z0-9_]|', $chars[($i + 1)]) === 0)
            ) {
                $matchedToken    = false;
                $lookAheadLength = ($maxTokenLength - strlen($buffer));
                if ($lookAheadLength > 0) {
                    // The buffer contains a token type, but we need
                    // to look ahead at the next chars to see if this is
                    // actually part of a larger token. For example,
                    // FOR and FOREACH.
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo "\t\t* buffer possibly contains token, looking ahead $lookAheadLength chars *".PHP_EOL;
                    }
                    $charBuffer = $buffer;
                    for ($x = 1; $x <= $lookAheadLength; $x++) {
                        if (isset($chars[($i + $x)]) === false) {
                            break;
                        }
                        $charBuffer .= $chars[($i + $x)];
                        if (PHP_CODESNIFFER_VERBOSITY > 1) {
                            $content = Util\Common::prepareForOutput($charBuffer);
                            echo "\t\t=> Looking ahead $x chars => $content".PHP_EOL;
                        }
                        if (isset($this->tokenValues[strtolower($charBuffer)]) === true) {
                            // We've found something larger that matches
                            // so we can ignore this char. Except for 1 very specific
                            // case where a comment like /**/ needs to tokenize as
                            // T_COMMENT and not T_DOC_COMMENT.
                            $oldType = $this->tokenValues[strtolower($buffer)];
                            $newType = $this->tokenValues[strtolower($charBuffer)];
                            if ($oldType === 'T_COMMENT'
                                && $newType === 'T_DOC_COMMENT'
                                && $chars[($i + $x + 1)] === '/'
                            ) {
                                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                                    echo "\t\t* look ahead ignored T_DOC_COMMENT, continuing *".PHP_EOL;
                                }
                            } else {
                                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                                    echo "\t\t* look ahead found more specific token ($newType), ignoring $i *".PHP_EOL;
                                }
                                $matchedToken = true;
                                break;
                            }
                        }//end if
                    }//end for
                }//end if
                
                if ($matchedToken === false) {
                    if (PHP_CODESNIFFER_VERBOSITY > 1 && $lookAheadLength > 0) {
                        echo "\t\t* look ahead found nothing *".PHP_EOL;
                    }
                    $value = $this->tokenValues[strtolower($buffer)];
                    if ($value === 'T_FUNCTION' && $buffer !== 'function') {
                        // The function keyword needs to be all lowercase or else
                        // it is just a function called "Function".
                        $value = 'T_STRING';
                    }
                    $tokens[] = [
                        'code'    => constant($value),
                        'type'    => $value,
                        'content' => $buffer,
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput($buffer);
                        echo "\t=> Added token $value ($content)".PHP_EOL;
                    }
                    $cleanBuffer = true;
                }//end if
            } elseif (isset($this->tokenValues[strtolower($char)]) === true) {
                // No matter what token we end up using, we don't
                // need the content in the buffer any more because we have
                // found a valid token.
                $newContent = substr(str_replace("\n", $this->eolChar, $buffer), 0, -1);
                if ($newContent !== '') {
                    $tokens[] = [
                        'code'    => T_STRING,
                        'type'    => 'T_STRING',
                        'content' => $newContent,
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput(substr($buffer, 0, -1));
                        echo "\t=> Added token T_STRING ($content)".PHP_EOL;
                    }
                }
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo "\t\t* char is token, looking ahead ".($maxTokenLength - 1).' chars *'.PHP_EOL;
                }
                // The char is a token type, but we need to look ahead at the
                // next chars to see if this is actually part of a larger token.
                // For example, = and ===.
                $charBuffer   = $char;
                $matchedToken = false;
                for ($x = 1; $x <= $maxTokenLength; $x++) {
                    if (isset($chars[($i + $x)]) === false) {
                        break;
                    }
                    $charBuffer .= $chars[($i + $x)];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput($charBuffer);
                        echo "\t\t=> Looking ahead $x chars => $content".PHP_EOL;
                    }
                    if (isset($this->tokenValues[strtolower($charBuffer)]) === true) {
                        // We've found something larger that matches
                        // so we can ignore this char.
                        if (PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokenValues[strtolower($charBuffer)];
                            echo "\t\t* look ahead found more specific token ($type), ignoring $i *".PHP_EOL;
                        }
                        $matchedToken = true;
                        break;
                    }
                }//end for
                if ($matchedToken === false) {
                    $value    = $this->tokenValues[strtolower($char)];
                    $tokens[] = [
                        'code'    => constant($value),
                        'type'    => $value,
                        'content' => $char,
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo "\t\t* look ahead found nothing *".PHP_EOL;
                        $content = Util\Common::prepareForOutput($char);
                        echo "\t=> Added token $value ($content)".PHP_EOL;
                    }
                    $cleanBuffer = true;
                } else {
                    $buffer = $char;
                }//end if
            }//end if
            // Keep track of content inside comments.
            if ($inComment === ''
                && array_key_exists($buffer, $this->commentTokens) === true
            ) {
                // This is not really a comment if the content
                // looks like \// (i.e., it is escaped).
                if (isset($chars[($i - 2)]) === true && $chars[($i - 2)] === '\\') {
                    $lastToken   = array_pop($tokens);
                    $lastContent = $lastToken['content'];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $value   = $this->tokenValues[strtolower($lastContent)];
                        $content = Util\Common::prepareForOutput($lastContent);
                        echo "\t=> Removed token $value ($content)".PHP_EOL;
                    }
                    $lastChars    = str_split($lastContent);
                    $lastNumChars = count($lastChars);
                    for ($x = 0; $x < $lastNumChars; $x++) {
                        $lastChar = $lastChars[$x];
                        $value    = $this->tokenValues[strtolower($lastChar)];
                        $tokens[] = [
                            'code'    => constant($value),
                            'type'    => $value,
                            'content' => $lastChar,
                        ];
                        if (PHP_CODESNIFFER_VERBOSITY > 1) {
                            $content = Util\Common::prepareForOutput($lastChar);
                            echo "\t=> Added token $value ($content)".PHP_EOL;
                        }
                    }
                } else {
                    // We have started a comment.
                    $inComment = $buffer;
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo "\t\t* looking for end of comment *".PHP_EOL;
                    }
                }//end if
            } elseif ($inComment !== '') {
                if ($this->commentTokens[$inComment] === null) {
                    // Comment ends at the next newline.
                    if (strpos($buffer, "\n") !== false) {
                        $inComment = '';
                    }
                } else {
                    if ($this->commentTokens[$inComment] === $buffer) {
                        $inComment = '';
                    }
                }
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    if ($inComment === '') {
                        echo "\t\t* found end of comment *".PHP_EOL;
                    }
                }
                if ($inComment === '' && $cleanBuffer === false) {
                    $tokens[] = [
                        'code'    => T_STRING,
                        'type'    => 'T_STRING',
                        'content' => str_replace("\n", $this->eolChar, $buffer),
                    ];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        $content = Util\Common::prepareForOutput($buffer);
                        echo "\t=> Added token T_STRING ($content)".PHP_EOL;
                    }
                    $buffer = '';
                }
            }//end if
            if ($cleanBuffer === true) {
                $buffer      = '';
                $cleanBuffer = false;
            }
        }//end for
        if (empty($buffer) === false) {
            if ($inString !== '') {
                // The string did not end before the end of the file,
                // which means there was probably a syntax error somewhere.
                $tokens[] = [
                    'code'    => T_STRING,
                    'type'    => 'T_STRING',
                    'content' => str_replace("\n", $this->eolChar, $buffer),
                ];
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    $content = Util\Common::prepareForOutput($buffer);
                    echo "\t=> Added token T_STRING ($content)".PHP_EOL;
                }
            } else {
                // Buffer contains whitespace from the end of the file.
                $tokens[] = [
                    'code'    => T_WHITESPACE,
                    'type'    => 'T_WHITESPACE',
                    'content' => str_replace("\n", $this->eolChar, $buffer),
                ];
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    $content = Util\Common::prepareForOutput($buffer);
                    echo "\t=> Added token T_WHITESPACE ($content)".PHP_EOL;
                }
            }//end if
        }//end if
        $tokens[] = [
            'code'    => T_CLOSE_TAG,
            'type'    => 'T_CLOSE_TAG',
            'content' => '',
        ];
        
        /*
            Now that we have done some basic tokenizing, we need to
            modify the tokens to join some together and split some apart
            so they match what the PHP tokenizer does.
        */$finalTokens = [];
        $newStackPtr = 0;
        $numTokens   = count($tokens);
        for ($stackPtr = 0; $stackPtr < $numTokens; $stackPtr++) {
            $token = $tokens[$stackPtr];

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
    }//end tokenize()
}
