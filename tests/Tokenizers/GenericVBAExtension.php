<?php

namespace PHP_CodeSniffer\Tests\Tokenizers;

use PHP_CodeSniffer\Tokenizers\VBA;

class GenericVBAExtension extends VBA
{

    protected $content;

    public function __construct($content, $config, $eolChar = '\n')
    {
        $this->content = $content;
    }
    
    public function callTokenizer()
    {
        $this->tokens = parent::tokenize($this->content);
    }
}