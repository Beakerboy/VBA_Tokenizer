<?php
namespace PHP_CodeSniffer\Tests\Tokenizers;

use PHP_CodeSniffer\Tokenizers\VBANew;

class NewGenericVBAExtension extends VBANew
{
    protected $content;
    public function __construct($content, $config, $eolChar = '\n')
    {
        $this->eolChar = $eolChar;
        $this->config = $config;
        $this->content = $content;
    }
    
    public function callTokenizer()
    {
        $this->tokens = parent::tokenize($this->content);
    }
}
