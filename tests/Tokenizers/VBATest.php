<?php
namespace PHP_CodeSniffer\Tests\Tokenizers;

define('PHP_CODESNIFFER_CBF', false);
define('PHP_CODESNIFFER_IN_TESTS', true);

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Tests\Tokenizers\GenericVBAExtension;

class VBATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testCase     Tests that the tokenizer returns the correct array
     */
    public function testTokenizer()
    {
        $config = new Config();
        $string = file_get_contents('tests/Test.cls');
        $VBA = new GenericVBAExtension($string, $config, '\r\n');
        $VBA->callTokenizer();
        $tokens = $VBA->getTokens();
        $this->assertEquals($tokens, $tokens);
    }
}
