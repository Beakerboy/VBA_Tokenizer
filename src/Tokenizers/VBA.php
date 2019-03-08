<?php

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Tokenizers\PHP;

class VBA extends PHP
{
    public $scopeOpeners = [
        T_IF => [
            'start'  => [T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [
                T_ELSE   => T_ELSE,
                T_ELSEIF => T_ELSEIF,
            ],
        ],
        T_ELSE => [
            'start'  => [T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [
            ],
        ],
        T_ELSEIF => [
            'start'  => [T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [
            ],
        ],
        T_FUNCTION => [
            'start'  => [T_CLOSE_PARENTHESIS => T_CLOSE_PARENTHESIS],  //Should be newline
            'end'    => [T_ENDDECLARE => T_ENDDECLARE],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ],
        T_WHILE => [
            'start'  => [T_WHITESPACE => T_WHITESPACE],  //Should be newline
            'end'    => [
                T_ENDWHILE => T_ENDWHILE,
                T_TRAIT  => T_TRAIT,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ],
        T_FOREACH => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_FOR => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE,
            ],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_ABSTRACT => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_CLONE => T_CLONE,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ],
        T_SWITCH => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_ENDSWITCH => T_ENDSWITCH,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ],
        T_CASE => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_BREAK => T_BREAK,
            ],
            'strict' => true,
            'shared' => true,
            'with'   => [
                T_CASE   => T_CASE,
                T_SWITCH => T_SWITCH,
                T_DEFAULT => T_DEFAULT,
            ],
        ],
        T_DEFAULT => [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_BREAK => T_BREAK,
            ],
            'strict' => true,
            'shared' => true,
            'with'   => [
                T_CASE   => T_CASE,
                T_SWITCH => T_SWITCH,
            ],
        ],
    ];

  /**
     * A list of tokens that end the scope.
     *
     * This array is just a unique collection of the end tokens
     * from the scopeOpeners array. The data is duplicated here to
     * save time during parsing of the file.
     *
     * @var array
     */
    public $endScopeTokens = [
        T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
        T_ENDIF               => T_ENDIF,
        T_ENDFOR              => T_ENDFOR,
        T_ENDFOREACH          => T_ENDFOREACH,
        T_ENDWHILE            => T_ENDWHILE,
        T_ENDSWITCH           => T_ENDSWITCH,
    ];

    protected function convertFile($string)
    {
        $string_array = explode("\r\n", $string);
        $new_string = "<?php ";
        foreach ($string_array as $line) {
            $line_tokens = token_get_all('<?php ' . $line);
            array_shift($line_tokens);
            foreach ($line_tokens as $key => &$token) {
                if ($token[0] === T_ENCAPSED_AND_WHITESPACE) {
                    $token[1] = '//' . substr($token[1], 1);
                } elseif ($token[0] === T_STRING) {
                    if ($token[1] == 'Sub' || $token[1] == 'Property') {
                        // Turn Subs into Functions
                        $token[1] = 'function';
                    } elseif ($token[1] == 'BEGIN') {
                        $token[1] = 'abstract';
                    } elseif ($token[1] == 'Select') {
                        $token[1] = 'switch';
                        unset($line_tokens[$key + 1]);
                        unset($line_tokens[$key + 2]);
                    } elseif ($token[1] == 'Not') {
                        $token[1] = '!';
                    } elseif ($token[1] == 'Then') {
                        $token = [T_STRING, ') {'];
                    } elseif ($token[1] == 'Wend') {
                        $token[1] = 'endwhile';
                    } elseif ($token[1] == 'Loop') {
                        $token[1] = 'trait';
                    } elseif ($token[1] == 'Is') {
                        $token = [T_STRING, '==='];
                    } elseif ($token[1] == 'END') {
                        $token[1] = 'clone';
                    } elseif ($token[1] == 'Next') {
                        // A for loop ends with Next i while a foreach ends with Next
                        $token[1] = '}';
                    } elseif ($token[1] === 'End') {
                        // If a string with the value "End" is found, change it to a special enddeclare if it is
                        // followed by Property, Function, or Sub.
                        // If it is follow by "if", change it to "endif"
                        $next_tag =  $line_tokens[$key + 2][1];
                        if ($next_tag == 'Function' || $next_tag == 'Sub' || $next_tag == 'Property') {
                            $token[1] = 'enddeclare';
                            unset($line_tokens[$key + 1]);
                            unset($line_tokens[$key + 2]);
                        } elseif ($next_tag == 'If') {
                            $token[1] = '}';
                            unset($line_tokens[$key + 1]);
                            unset($line_tokens[$key + 2]);
                        } elseif ($next_tag == 'Select') {
                            $token[1] = 'endswitch';
                            unset($line_tokens[$key + 1]);
                            unset($line_tokens[$key + 2]);
                        }
                    }
                } elseif ($token[0] === T_IF) {
                    $token = [T_STRING, 'if ('];
                } elseif ($token[0] === T_ELSE) {
                    $token = [T_STRING, '} else {'];
                } elseif ($token[0] === T_ELSEIF) {
                    $token = [T_STRING, '} elseif ('];
                } elseif ($token[0] === T_CASE) {
                    if ($line_tokens[$key + 2][0] === T_ELSE) {
                        $token[1] = 'default';
                        unset($line_tokens[$key + 1]);
                        unset($line_tokens[$key + 2]);
                    }
                } elseif ($token == '.') {
                    $token = [T_STRING, '->'];
                } elseif ($token[0] == T_FOR) {
                    $next_tag =  $line_tokens[$key + 2][1];
                    if ($next_tag == 'Each') {
                        $token[1] = 'foreach';
                        unset($line_tokens[$key + 1]);
                        unset($line_tokens[$key + 2]);
                    }
                }
                // Write the token value back to the new string
                if (isset($token[1])) {
                    $new_string .= $token[1];
                } else {
                    $new_string .= $token;
                }
            }
            $new_string .= "\r\n";
        }
        // Remove the last line ending.
        return substr($new_string, 0, -2);
    }

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
        $new_string = $this->convertFile($string);
        
        return parent::tokenize($new_string);
    }
}
