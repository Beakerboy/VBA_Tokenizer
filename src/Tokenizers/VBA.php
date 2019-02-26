<?php
/**
 * Tokenizes VBA code.
 *
 * @author    Kevin Nowaczyk
 * @copyright 
 * @license   
 */

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Util;
use PHP_CodeSniffer\Tokenizers\PHP;

class VBA extends PHP
{

    protected function convertFile($string)
    {
        $string_array = explode("\r\n", $string);
        $new_string = "<?php\r\n";
        foreach ($string_array as $line) {
            $line_tokens = token_get_all("<?php\r\n" . $line);
            array_shift($line_tokens);
            foreach ($line_tokens as $key=>&$token) {
                if ($token[0] === T_ENCAPSED_AND_WHITESPACE) {
                    $token[1] = '//' . substr($token[1], 1);      
                }
                // Turn Subs into Functions
                else if ($token[0] === T_STRING && ($token[1] == "Sub" || $token[1] == "Property")) {
                    $token[1] = "Function";
                }
                else if ($token[0] == T_BITWISE_AND) {
                    $token[1] = '.';
                }
                else if ($token[0] === T_STRING && $token[1] == "NOT") {
                    $token[1] = '!';
                }
                else if ($token[0] === T_STRING && $token[1] == "AND") {
                    $token[1] = '&&';
                }
                else if ($token[0] === T_STRING && $token[1] == "OR") {
                    $token[1] = '||';
                }
                else if ($token[0] === T_STRING && $token[1] == "Then") {
                    $token[1] = 'static';
                }
                else if ($token == ".") {
                    $token = [T_STRING, '->'];
                }
                // If a string with the value "End" is found, change it to a special enddeclare if it is
                // followed by Property, Function, or Sub.
                // If it is follow by "if", change it to "endif"
                else if ($token[0] === T_STRING && $token[1] === "End") {
                    $next_tag =  $line_tokens[$key + 2][1];
                    if ($next_tag == "Function" || $next_tag == "Sub" || $next_tag == "Property") { 
                        $token[1] = "enddeclare";
                        next($line_tokens);
                        $next_key = key($line_tokens);
                        next($line_tokens);
                        $second_key = key($line_tokens);
                        unset($line_tokens[$next_key]);
                        unset($line_tokens[$second_key]);
                    } elseif ($next_tag == 'If') {
                        $token[1] = "endif";
                        next($line_tokens);
                        $next_key = key($line_tokens);
                        next($line_tokens);
                        $second_key = key($line_tokens);
                        unset($line_tokens[$next_key]);
                        unset($line_tokens[$second_key]);
                    }
                }
                else if ($token[0] == T_FOR) {
                    $next_tag =  $line_tokens[$key + 2][1];
                    if ($next_tag == "Each") { 
                        $token[1] = 'foreach';
                        next($line_tokens);
                        $next_key = key($line_tokens);
                        next($line_tokens);
                        $second_key = key($line_tokens);
                        unset($line_tokens[$next_key]);
                        unset($line_tokens[$second_key]);
                        next($line_tokens);
                    }
                }
                // A for loop ends with Next i while a foreach ends with Next
                else if ($token[0] == T_STRING && $token[1] == "Next") {
                        $token[1] = '}';
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
            'start'  => [T_STRING=> T_STRING],
            'end'    => [T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET],
            'strict' => true,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_IF] = 
        [
            'start'  => [T_STATIC, T_STATIC],  // Should be T_THEN
            'end'    => [
                T_ENDIF               => T_ENDIF,
                T_ELSE                => T_ELSE,
                T_ELSEIF              => T_ELSEIF,
            ],
            'strict' => false,
            'shared' => false,
            'with'   => [
                T_ELSE   => T_ELSE,
                T_ELSEIF => T_ELSEIF,
            ],
        ];
        // T_ELSE
        // T_ELSEIF
        $this->scopeOpeners[T_FUNCTION] = 
        [
            'start'  => [T_CLOSE_PARENTHESIS => T_CLOSE_PARENTHESIS],  //Should be newline
            'end'    => [T_ENDDECLARE => T_ENDDECLARE],
            'strict' => false,
            'shared' => false,
            'with'   => [],
        ];
        $this->scopeOpeners[T_FOREACH] = 
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
        $string = $this->convertFile($string);
        return parent::tokenize($string);
    }

}

