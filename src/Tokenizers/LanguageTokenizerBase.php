<?php

namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Toeknizer;

class LanguageTokenizerBase extends Tokenizer
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
    public function tokenize($string) {
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
       
        
    }
   
    protected funcrion processAdditional() {
        
    }
    
    /**
     * Performs additional processing after main tokenizing.
     *
     * @return void
     */
    abstract protected function processAdditional();
}
