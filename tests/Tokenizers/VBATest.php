<?php
namespace PHP_CodeSniffer\Tests\Tokenizer;

define('PHP_CODESNIFFER_CBF', false);

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Tokenizers\GenericVBAExtension;

class VBATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testCase     Tests that the tokenizer returns the correct array
     */
    public function testTokenizer()
    {
        $config = new Config(['--extensions=cls/vba', '--standard=src/Standards/VBA']);
        $string = file_get_contents('tests/Test.cls');
        $VBA = new GenericVBAExtension($string, $config, '\r\n');
        $tokens = $VBA->getTokens();
        $this->assertEquals($tokens, $tokens);
    }
}
