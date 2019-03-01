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
        $VBA = new GenericVBAExtension($string, $config, '\n');
        $VBA->callTokenizer();
        $tokens = $VBA->getTokens();
        $this->assertEquals($expected, $tokens);
    }
    
    public function dataProviderForTokenizer()
    {
        $input1 = '\' Function: Foo\nPublic Function Foo(iVariable As Double) As Boolean\n    While iVariable Is 2\n        iVariable = iVariable + 1\n    Wend\nEnd Function';
        
        $output1 = [
            [T_OPEN_TAG, '<?php '],
            [T_COMMENT, '// Function: Foo\n'],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'Function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Foo'],
            [T_OPEN_PARENTHESIS, '('], [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'],[T_WHITESPACE, ' '],
            [T_STRING, 'Double'], [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Boolean'], [T_WHITESPACE, '\n    '],
            [T_WHILE, 'While'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_IS_IDENTICAL, '==='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '2'], [T_WHITESPACE, '\n        '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_PLUS, '+'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '1'], [T_WHITESPACE, '\n    '],
            [T_STATIC, 'static'], [T_WHITESPACE, '\n'],
            [T_ENDDECLARE, 'enddeclare'],
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
