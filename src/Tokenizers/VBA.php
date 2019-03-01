<?php

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Tokenizers\PHP;

class VBA extends PHP
{

    protected function convertFile($string)
    {
        $string_array = explode("\r\n", $string);
        $new_string = "<?php ";
        foreach ($string_array as $line) {
            $line_tokens = token_get_all("<?php " . $line);
            array_shift($line_tokens);
            foreach ($line_tokens as $key => &$token) {
                if ($token[0] === T_ENCAPSED_AND_WHITESPACE) {
                    $token[1] = '//' . substr($token[1], 1);
                } elseif ($token[0] === T_STRING) {
                    if ($token[1] == "Sub" || $token[1] == "Property") {
                        // Turn Subs into Functions
                        $token[1] = "function";
                    } elseif ($token[1] == 'BEGIN') {
                        $token[1] = 'abstract';
                    } elseif ($token[1] == "Not") {
                        $token[1] = '!';
                    } elseif ($token[1] == "And") {
                        $token[1] = '&&';
                    } elseif ($token[1] == "Or") {
                        $token[1] = '||';
                    } elseif ($token[1] == "Then") {
                        $token = [T_STRING, ') {'];
                    } elseif ($token[1] == "Wend") {
                        $token[1] = 'static';
                    } elseif ($token[1] == "Loop") {
                        $token[1] = 'trait';
                    } elseif ($token[1] == "Is") {
                        $token = [T_STRING, '==='];
                    } elseif ($token[1] == 'END') {
                        $token[1] = 'clone';
                    } elseif ($token[1] == "Next") {
                        // A for loop ends with Next i while a foreach ends with Next
                        $token[1] = '}';
                    } elseif ($token[1] === "End") {
                        // If a string with the value "End" is found, change it to a special enddeclare if it is
                        // followed by Property, Function, or Sub.
                        // If it is follow by "if", change it to "endif"
                        $next_tag =  $line_tokens[$key + 2][1];
                        if ($next_tag == "Function" || $next_tag == "Sub" || $next_tag == "Property") {
                            $token[1] = "enddeclare";
                            unset($line_tokens[$key + 1]);
                            unset($line_tokens[$key + 2]);
                        } elseif ($next_tag == 'If') {
                            $token[1] = "}";
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
          //      } elseif ($token[0] == T_BITWISE_AND) {
          //          $token[1] = '.';
                } elseif ($token == ".") {
                    $token = [T_STRING, '->'];
                } elseif ($token[0] == T_FOR) {
                    $next_tag =  $line_tokens[$key + 2][1];
                    if ($next_tag == "Each") {
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
        return $new_string;
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
        $this->scopeOpeners[T_CLASS] =
        [
            'start'  => [T_STRING => T_STRING],
            'end'    => [T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_IF] =
        [
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
        ];
        $this->scopeOpeners[T_ELSE] =
        [
            'start'  => [T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [
            ],
        ];
        $this->scopeOpeners[T_ELSEIF] =
        [
            'start'  => [T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => true,
            'shared' => false,
            'with'   => [
            ],
        ];
        $this->scopeOpeners[T_FUNCTION] =
        [
            'start'  => [T_CLOSE_PARENTHESIS => T_CLOSE_PARENTHESIS],  //Should be newline
            'end'    => [T_ENDDECLARE => T_ENDDECLARE],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_WHILE] =
        [
            'start'  => [T_WHITESPACE => T_WHITESPACE],  //Should be newline
            'end'    => [T_STATIC => T_STATIC],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_FOREACH] =
        [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_FOR] =
        [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE,
            ],
            'end'    => [
                T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_ABSTRACT] =
        [
            'start'  => [
                T_WHITESPACE=> T_WHITESPACE, //Should be line ending
            ],
            'end'    => [
                T_CLONE => T_CLONE,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ];
        $new_string = $this->convertFile($string);
        return parent::tokenize($new_string);
    }
}
