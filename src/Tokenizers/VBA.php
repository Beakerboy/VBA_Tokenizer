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
        T_FUNCTION      => [
            'start'  => "",
            'end'    => "",
            'strict' => true,
            'shared' => false,
            'with'   => [],
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
    ];

    /**
     * Known lengths of tokens.
     *
     * @var array<int, int>
     */
    public $knownLengths = [
    ];
    
    /**
     * A cache of different token types, resolved into arrays.
     *
     * @var array
     * @see standardiseToken()
     */
    private static $resolveTokenCache = [];

    protected function convertFile($string)
    {
        $string_array = explode("\r\n", $string);
        $new_string = "<?php\r\n";
        foreach ($string_array as $line) {
            $line_tokens = token_get_all("<?php\r\n" . $line);
            array_shift($line_tokens);
            foreach ($line_tokens as $key=>$token) {
                if ($token[0] === T_ENCAPSED_AND_WHITESPACE) {
                    $token[1] = '//' . substr($token[1], 1);      
                }
                // If a string with the value "End" is found, change it to a special enddeclare if it is
                // followed by Property, Function, or Sub.
                // If it is follow by "if", change it to "endif"
                if ($token[0] === T_STRING && $token[1] === "End") {
                    $next_tag =  $line_tokens[$key + 2][1];
                    if ($next_tag == "Function" || $next_tag == "Sub" || $next_tag == "Property") { 
                        $token[1] = "enddeclare";
                    } elseif ($next_tag == 'If') {
                        $token[1] = "endif";
                        unset($line_tokens[$key+1]);
                        unset($line_tokens[$key+2]);
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
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START VBA TOKENIZING ***".PHP_EOL;
            $isWin = false;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $isWin = true;
            }
        }
        $string = $this->convertFile($string);
        $tokens      = @token_get_all($string);
        array_shift($tokens);
        $finalTokens = [];
        $newStackPtr       = 0;
        $numTokens         = count($tokens);
        $lastNotEmptyToken = 0;
        $insideInlineIf = [];
        $insideUseGroup = false;
        $commentTokenizer = new Comment();

        for ($stackPtr = 0; $stackPtr < $numTokens; $stackPtr++) {
            // Special case for tokens we have needed to blank out.
            if ($tokens[$stackPtr] === null) {
                continue;
            }
            $token        = (array) $tokens[$stackPtr];
            $tokenIsArray = isset($token[1]);
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                if ($tokenIsArray === true) {
                    $type    = Util\Tokens::tokenName($token[0]);
                    $content = Util\Common::prepareForOutput($token[1]);
                } else {
                    $newToken = self::resolveSimpleToken($token[0]);
                    $type     = $newToken['type'];
                    $content  = Util\Common::prepareForOutput($token[0]);
                }
                echo "\tProcess token ";
                if ($tokenIsArray === true) {
                    echo "[$stackPtr]";
                } else {
                    echo " $stackPtr ";
                }
                echo ": $type => $content";
            }//end if

            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo PHP_EOL;
            }
        }//end for

        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** END PHP TOKENIZING ***".PHP_EOL;
        }

        return $finalTokens;
    }

    /**
     * Performs additional processing after main tokenizing.
     *
     * This additional processing checks for CASE statements that are using curly
     * braces for scope openers and closers. It also turns some T_FUNCTION tokens
     * into T_CLOSURE when they are not standard function definitions. It also
     * detects short array syntax and converts those square brackets into new tokens.
     * It also corrects some usage of the static and class keywords. It also
     * assigns tokens to function return types.
     *
     * @return void
     */
    protected function processAdditional()
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START ADDITIONAL VBA PROCESSING ***".PHP_EOL;
        }
    }

    /**
     * Converts simple tokens into a format that conforms to complex tokens
     * produced by token_get_all().
     *
     * Simple tokens are tokens that are not in array form when produced from
     * token_get_all().
     *
     * @param string $token The simple token to convert.
     *
     * @return array The new token in array format.
     */
    public static function resolveSimpleToken($token)
    {
        $newToken = [];

        switch ($token) {
        case '{':
            $newToken['type'] = 'T_OPEN_CURLY_BRACKET';
            break;
        case '}':
            $newToken['type'] = 'T_CLOSE_CURLY_BRACKET';
            break;
        case '[':
            $newToken['type'] = 'T_OPEN_SQUARE_BRACKET';
            break;
        case ']':
            $newToken['type'] = 'T_CLOSE_SQUARE_BRACKET';
            break;
        case '(':
            $newToken['type'] = 'T_OPEN_PARENTHESIS';
            break;
        case ')':
            $newToken['type'] = 'T_CLOSE_PARENTHESIS';
            break;
        case ':':
            $newToken['type'] = 'T_COLON';
            break;
        case '.':
            $newToken['type'] = 'T_STRING_CONCAT';
            break;
        case ';':
            $newToken['type'] = 'T_SEMICOLON';
            break;
        case '=':
            $newToken['type'] = 'T_EQUAL';
            break;
        case '*':
            $newToken['type'] = 'T_MULTIPLY';
            break;
        case '/':
            $newToken['type'] = 'T_DIVIDE';
            break;
        case '+':
            $newToken['type'] = 'T_PLUS';
            break;
        case '-':
            $newToken['type'] = 'T_MINUS';
            break;
        case '%':
            $newToken['type'] = 'T_MODULUS';
            break;
        case '^':
            $newToken['type'] = 'T_BITWISE_XOR';
            break;
        case '&':
            $newToken['type'] = 'T_BITWISE_AND';
            break;
        case '|':
            $newToken['type'] = 'T_BITWISE_OR';
            break;
        case '~':
            $newToken['type'] = 'T_BITWISE_NOT';
            break;
        case '<':
            $newToken['type'] = 'T_LESS_THAN';
            break;
        case '>':
            $newToken['type'] = 'T_GREATER_THAN';
            break;
        case '!':
            $newToken['type'] = 'T_BOOLEAN_NOT';
            break;
        case ',':
            $newToken['type'] = 'T_COMMA';
            break;
        case '@':
            $newToken['type'] = 'T_ASPERAND';
            break;
        case '$':
            $newToken['type'] = 'T_DOLLAR';
            break;
        case '`':
            $newToken['type'] = 'T_BACKTICK';
            break;
        default:
            $newToken['type'] = 'T_NONE';
            break;
        }//end switch

        $newToken['code']    = constant($newToken['type']);
        $newToken['content'] = $token;

        self::$resolveTokenCache[$token] = $newToken;
        return $newToken;

    }//end resolveSimpleToken()
}

