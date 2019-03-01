<?php
namespace PHP_CodeSniffer\Tests\Tokenizer;

use PHPCodeSniffer\Tokenizers\VBA;

class VBATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testCase     Tests that the tokenizer returns the correct array
     */
    public function testTokenizer()
    {
        global $argv;
        $argv = ["phpcs", "--extensions=cls/vba", "--standards=../../src/Standards/VBA"];
        $string = file_get_contents("tests/Test.cls");
        $runner   = new PHP_CodeSniffer\Runner();
        $exitCode = $runner->runPHPCS();
        $this->assertEquals(0, $exit_code);
    }
}
