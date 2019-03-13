<?php

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Tokenizers\Tokenizer;

class TokenizerBase extends Tokenizer
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
    protected $scopeOpeners = [];
    
    /**
     * A list of tokens that end the scope.
     *
     * This array is just a unique collection of the end tokens
     * from the _scopeOpeners array. The data is duplicated here to
     * save time during parsing of the file.
     *
     * @var array
     */
    protected $endScopeTokens = [];
    
    /**
     * A list of special VBA tokens and their types.
     *
     * @var array
     */
    protected $tokenValues = [];
    
    /**
     * A list string delimiters.
     *
     * @var array
     */
    protected $stringTokens = [];
    
    /**
     * A list tokens that start and end comments.
     *
     * @var array
     */
    protected $commentTokens = [];
    
    /**
     * A list tokens that are two words.
     *
     * @var array
     */
    protected $twoWordTokens = [];
    
    /**
     * Use the child object's tokenValues array to find all tokens
     *
     * @param string $string The string to tokenize.
     *
     * @return array
     */
    public function tokenize($string)
    {
        $this->tokens = tokensFromCharacterStream($string);
    }
    
    /**
     * Performs additional processing after main tokenizing.
     *
     * @return void
     */
    protected function processAdditional()
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START ADDITIONAL " . get_class($this) . " PROCESSING ***".PHP_EOL;
        }
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** END ADDITIONAL " . get_class($this) . " PROCESSING ***".PHP_EOL;
        }
    }
    
    protected function tokensFromCharacterStream($string)
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo "\t*** START " . get_class($this) . " TOKENIZING ***".PHP_EOL;
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
        $inComment       = '';        // The token that started the comment
        $buffer          = '';
        $preStringBuffer = '';
        $cleanBuffer     = false;
        
        $chars    = str_split($string);
        $numChars = count($chars);
        for ($i = 0; $i < $numChars; $i++) {
            $char = $chars[$i];
            
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                $content       = Util\Common::prepareForOutput($char);
                $bufferContent = Util\Common::prepareForOutput($buffer);
                
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
                        'content' => $buffer,
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
                        'content' => $buffer,
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
                        if ($chars[$x] !== $this->escapeCharacter) {
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
                            'content' => $buffer.$char,
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
                        'content' => $buffer,
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
                    'content' => $buffer,
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
                    'content' => $buffer,
                ];
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    $content = Util\Common::prepareForOutput($buffer);
                    echo "\t=> Added token T_WHITESPACE ($content)".PHP_EOL;
                }
            }//end if
        }//end if
        return $tokens;
    }
}
