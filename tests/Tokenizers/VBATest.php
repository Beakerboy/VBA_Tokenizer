<?php
namespace PHP_CodeSniffer\Tests\Tokenizers;

define('PHP_CODESNIFFER_CBF', false);
define('PHP_CODESNIFFER_IN_TESTS', true);
define('PHP_CODESNIFFER_VERBOSITY', 0);

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Tests\Tokenizers\GenericVBAExtension;

class VBATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testCase     Tests that the tokenizer returns the correct array
     * @dataProvider dataProviderForTokenizer
     */
    public function testTokenizer($string, $expected)
    {
        $config = new Config(['--extensions=cls/vba']);
        $VBA = new GenericVBAExtension($string, $config, '\r\n');
        $VBA->callTokenizer();
        $tokens = $VBA->getTokens();
        $this->assertEquals($expected, $tokens);
    }
    
    public function dataProviderForTokenizer()
    {
        $input1 = "VERSION 1.0 CLASS\r\n" .
            "  BEGIN\r\n" .
            //"MultiUse = -1  'True\r\n"
            "END\r\n" .
            //"Attribute VB_Name = \"Test\"\r\n" .
             "Option Explicit\r\n" .
             "\r\n" .
             "' Class: Test\r\n" .
             "' A test class.\r\n" .
             "Implements iTest\r\n" .
             "\r\n" .
            "Public Function Foo(iVariable As Double) As Boolean\r\n" .
            "End Function";
        $output1 = [
            [T_OPEN_TAG, '<?php '],
            [T_STRING, 'VERSION'], [T_WHITESPACE, ' '],
            [T_DNUMBER, '1.0'], [T_WHITESPACE, ' '],
            [T_CLASS, 'CLASS'], [T_WHITESPACE, "\r\n  "],
            [T_ABSTRACT, 'abstract'], [T_WHITESPACE, "\r\n"],
            [T_CLONE, 'clone'], [T_WHITESPACE, "\r\n"],
            [T_STRING, 'Option'], [T_WHITESPACE, ' '], [T_STRING, 'Explicit'], [T_WHITESPACE, "\r\n\r\n"],
            [T_COMMENT, "// Class: Test\r\n"],
            [T_COMMENT, "// A test class.\r\n"],
            [T_IMPLEMENTS, 'Implements'], [T_WHITESPACE, ' '], [T_STRING, 'iTest'], [T_WHITESPACE, "\r\n\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'Function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Foo'],
            [T_OPEN_PARENTHESIS, '('], [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'],[T_WHITESPACE, ' '],
            [T_STRING, 'Double'], [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Boolean'], [T_WHITESPACE, "\r\n"],
            [T_ENDDECLARE, 'enddeclare'], [T_WHITESPACE, "\r\n"],
        ];
        return [
            [$input1, $this->expandArray($output1)],
        ];
    }
    
    public function expandArray($input)
    {
        $output = [];
        foreach ($input as $token) {
            $output[] = [
                'code' => $token[0],
                'type' => Tokens::tokenName($token[0]),
                'content' => $token[1],
            ];
        }
        return $output;
    }
}
