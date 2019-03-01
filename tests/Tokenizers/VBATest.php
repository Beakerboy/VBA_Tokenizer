<?php
namespace PHP_CodeSniffer\Tests\Tokenizers;

define('PHP_CODESNIFFER_CBF', false);
define('PHP_CODESNIFFER_IN_TESTS', true);
define('PHP_CODESNIFFER_VERBOSITY', 0);

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner;
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
        $VBA = new GenericVBAExtension($string, $config, '\n');
        $VBA->callTokenizer();
        $tokens = $VBA->getTokens();
        $this->assertEquals($expected, $tokens);
    }
    
    public function dataProviderForTokenizer()
    {
        $input1 = "' Function: Foo
Public Function Foo(iVariable As Double) As Boolean
    While iVariable Is 2
        iVariable = iVariable + 1
    Wend
End Function";
        
        $output1 = [
            [T_COMMENT, '// Function: Foo\n', 1],
            [T_PUBLIC, 'Public', 2], [T_WHITESPACE, ' ', 2], 
            [T_FUNCTION, 'Function', 2], [T_WHITESPACE, ' ', 2],
            [T_STRING, 'Foo', 2],
            [T_LEFT_PARENTHESIS, '(', 2], [T_STRING, 'iVariable', 2], [T_WHITESPACE, ' ', 2],
            [T_AS, 'As', 2],[T_WHITESPACE, ' ', 2],
            [T_STRING, 'Double', 2], [T_RIGHT_PARENTHESIS, ')', 2], [T_WHITESPACE, ' ', 2],
            [T_AS], [T_WHITESPACE, ' ', 2],
            [T_STRING, 'Boolean', 2],[T_WHITESPACE, '\n    ', 2],
            [T_WHILE, 'While', 3], [T_WHITESPACE, ' ', 3],
            [T_STRING, 'iVariable', 3], [T_WHITESPACE, ' ', 3],
            [T_IS_IDENTICAL, '===', 3], [T_WHITESPACE, ' ', 3],
            [T_NUMBER, '2', 3], [T_WHITESPACE, '\n        ', 3],
            [T_STRING, 'iVariable', 3], [T_WHITESPACE, ' ', 3],
            [T_EQUALS, '=', 4], [T_WHITESPACE, ' ', 4],
            [T_STRING, 'iVariable', 4], [T_WHITESPACE, ' ', 4],
            [T_PLUS, '+', 4], [T_WHITESPACE, ' ', 4],
            [T_NUMBER, '1', 3], [T_WHITESPACE, '\n    ', 3],
            [T_STATIC, 'static', 5], [T_WHITESPACE, '\n', 5],
            [T_ENDDECLARE, 'enddeclare', 6],
        ];
        return [
            [$input1, $output1],
        ];
    }
}
