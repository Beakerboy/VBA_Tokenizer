<?php

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Tokenizers\Tokenizer;
use PHP_CodeSniffer\Util;

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
     * A list of special tokens and their types.
     * The necessary tokens are T_STRING, T_WHITESPACE,
     * T_CONSTANT_ENCAPSED_STRING, and T_COMMENT.
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
            $content       = Util\Common::prepareForOutput($char);
            $bufferContent = Util\Common::prepareForOutput($buffer);
            $tabs = str_repeat("\t", count(array_filter([$inString !== '', $inComment !== ''])));
            $this->verboseOutput($tabs . "\tProcess char $i => $content (buffer: $bufferContent)");
            // We separate the buffer into either strings or whitespace
            if ($inString === '' && $inComment === '' && $buffer !== '') {
                // If the buffer only has whitespace and we are about to
                // add a character, store the whitespace first.
                if (!$this->isWhitespace($char) && $this->isWhitespace($buffer)) {
                    $tokens[] = $this->simpleToken('T_WHITESPACE', $buffer);
                    $buffer = '';
                } elseif (!$this->isString($char) && $this->isString($buffer)) {
                    // If the buffer is not whitespace and we are about to
                    // add a whitespace character, store the content first.
                    $tokens[] = $this->simpleToken('T_STRING', $buffer);
                    $buffer = '';
                } elseif (!$this->isEol($char) && $this->isEol($buffer)) {
                    $tokens[] = $this->simpleToken('T_EOL', $buffer);
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
                        $this->verboseOutput("\t\t* found end of string *");
                        $tokens[] = $this->simpleToken('T_CONSTANT_ENCAPSED_STRING', $buffer.$char);

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
                    $this->verboseOutput("\t\t* looking for string closer *");
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
                    $this->verboseOutput("\t\t* found newline before end of string, bailing *");
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
                    $string = "\t\t* buffer possibly contains token, looking ahead $lookAheadLength chars *";
                    $this->verboseOutput($string);
                    $charBuffer = $buffer;
                    for ($x = 1; $x <= $lookAheadLength; $x++) {
                        if (isset($chars[($i + $x)]) === false) {
                            break;
                        }
                        $charBuffer .= $chars[($i + $x)];
                        $content = Util\Common::prepareForOutput($charBuffer);
                        $this->verboseOutput("\t\t=> Looking ahead $x chars => $content");
                        if (isset($this->tokenValues[strtolower($charBuffer)]) === true) {
                            // We've found something larger that matches
                            // so we can ignore this char.
                            // Need to check for cases like /**/ that can be an open comment and close comment
                            // or an open doc_comment and a slash. Bigger is not always more correct.
                            $oldType = $this->tokenValues[strtolower($buffer)];
                            $newType = $this->tokenValues[strtolower($charBuffer)];
                            $string = "\t\t* look ahead found more specific token ($newType), ignoring $i *";
                            $this->verboseOutput($string);
                            $matchedToken = true;
                            break;
                        }//end if
                    }//end for
                }//end if
                
                if ($matchedToken === false) {
                    if ($lookAheadLength > 0) {
                        $this->verboseOutput("\t\t* look ahead found nothing *");
                    }
                    $value = $this->tokenValues[strtolower($buffer)];
                    $tokens[] = $this->simpleToken($value, $buffer);
                    $cleanBuffer = true;
                }//end if
            } elseif (isset($this->tokenValues[strtolower($char)]) === true) {
                // No matter what token we end up using, we don't
                // need the content in the buffer any more because we have
                // found a valid token.
                $newContent = substr($buffer, 0, -1);
                if ($newContent !== '') {
                    $tokens[] = $this->simpleToken('T_STRING', $newContent);
                }
                $this->verboseOutput("\t\t* char is token, looking ahead ".($maxTokenLength - 1).' chars *');
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
                    $content = Util\Common::prepareForOutput($charBuffer);
                    $this->verboseOutput("\t\t=> Looking ahead $x chars => $content");
                    if (isset($this->tokenValues[strtolower($charBuffer)]) === true) {
                        // We've found something larger that matches
                        // so we can ignore this char.
                        $type = $this->tokenValues[strtolower($charBuffer)];
                        $this->verboseOutput("\t\t* look ahead found more specific token ($type), ignoring $i *");
                        $matchedToken = true;
                        break;
                    }
                }//end for
                if ($matchedToken === false) {
                    $value    = $this->tokenValues[strtolower($char)];
                    $this->verboseOutput("\t\t* look ahead found nothing *");
                    $tokens[] = $this->simpleToken($value, $char);
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
                    $value   = $this->tokenValues[strtolower($lastContent)];
                    $content = Util\Common::prepareForOutput($lastContent);
                    $this->verboseOutput("\t=> Removed token $value ($content)");
                    $lastChars    = str_split($lastContent);
                    $lastNumChars = count($lastChars);
                    for ($x = 0; $x < $lastNumChars; $x++) {
                        $lastChar = $lastChars[$x];
                        $value    = $this->tokenValues[strtolower($lastChar)];
                        $tokens[] = simpleToken($value, $lastChar);
                    }
                } else {
                    // We have started a comment.
                    $inComment = $buffer;
                    $this->verboseOutput("\t\t* looking for end of comment *");
                }//end if
            } elseif ($inComment !== '') {
                if ($this->commentTokens[$inComment] === null) {
                    // Comment ends at the next newline.
                    if (strpos($buffer, "\n") !== false) {
                        $inComment = '';
                    }
                } elseif ($this->commentTokens[$inComment] === $buffer) {
                    $inComment = '';
                }
                if ($inComment === '') {
                    $this->verboseOutput("\t\t* found end of comment *");
                }
                if ($inComment === '' && $cleanBuffer === false) {
                    $tokens[] = $this->simpleToken('T_STRING', $buffer);
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
                $tokens[] = $this->simpleToken('T_STRING', $buffer);
            } else {
                // Buffer contains whitespace from the end of the file.
                $tokens[] = $this->simpleToken('T_WHITESPACE', $buffer);
            }//end if
        }//end if
        return $tokens;
    }
    
    protected function combineComments($tokens, &$stackPtr)
    {
        $token = $tokens[$stackPtr];
        /*
            Look for comments and join the tokens together.
        */
        if ($token['code'] === T_COMMENT) {
            $newContent   = '';
            $tokenContent = $token['content'];
            $endContent = null;
            if (isset($this->commentTokens[$tokenContent]) === true) {
                $endContent = $this->commentTokens[$tokenContent];
            }
            while ($tokenContent !== $endContent) {
                if ($endContent === null
                    && $this->hasEolChar($tokenContent)
                ) {
                    // A null end token means the comment ends at the end of
                    // the line so we look for newlines and split the token.
                    $tokens[$stackPtr]['content'] = substr(
                        $tokenContent,
                        (strpos($tokenContent, $this->eolChar) + strlen($this->eolChar))
                    );
                    $tokenContent = substr(
                        $tokenContent,
                        0,
                        (strpos($tokenContent, $this->eolChar) + strlen($this->eolChar))
                    );
                    // If the substr failed, skip the token as the content
                    // will now be blank.
                    if ($tokens[$stackPtr]['content'] !== false
                        && $tokens[$stackPtr]['content'] !== ''
                    ) {
                        $stackPtr--;
                    }
                    break;
                }//end if
                $stackPtr++;
                $newContent .= $tokenContent;
                if (isset($tokens[$stackPtr]) === false) {
                    break;
                }
                $tokenContent = $tokens[$stackPtr]['content'];
            }//end while
            // Save the new content in the current token so
            // the code below can chop it up on newlines.
            $token['content'] = $newContent.$tokenContent;
        }//end if
        return $token;
    }

    /**
     * Create a simple token.
     *
     * @param string $type The token type.
     * @param string $content The token text.
     *
     * @return array
     */
    protected function simpleToken($type, $content)
    {
        $output = Util\Common::prepareForOutput($content);
        $this->verboseOutput("\t=> Added token $type ($output)");

        return [
            'code'    => constant($type),
            'type'    => $type,
            'content' => $content,
        ];
    }
    
    protected function verboseOutput($string)
    {
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo $string.PHP_EOL;
        }
    }
    
    private function isWhitespace($char)
    {
        return trim($char, " \t\0\x0B") === '';
    }
    
    private function isEol($char)
    {
        return trim($char, "\r\n") === '';
    }
    
    private function isString($char)
    {
        return trim($char, " \t\n\r\0\x0B") !== '';
    }
    
    private function hasEolChar($string)
    {
        return strpos($string, "\r") !== false || strpos($string, "\n") !== false;
    }
}
