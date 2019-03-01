<?php
namespace PHP_CodeSniffer\Tests\Tokenizer;

use PHP_CodeSniffer\Runner;

class VBATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testCase     Tests that the tokenizer returns the correct array
     */
    public function testTokenizer()
    {
        $_SERVER["argv"] = ["phpcs", "--extensions=cls/vba", "--standards=../../src/Standards/VBA"];
        $string = file_get_contents("tests/Test.cls");
        $runner   = new Runner();
        $exitCode = $runner->runPHPCS();
        $this->assertEquals(0, $exitCode);
    }
}
