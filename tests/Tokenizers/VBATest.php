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
        $input1 = file_get_contents('tests/Test.cls');
        $output1 = [
            [T_OPEN_TAG, '<?php '],
            [T_STRING, 'VERSION'], [T_WHITESPACE, ' '],
            [T_DNUMBER, '1.0'], [T_WHITESPACE, ' '],
            [T_CLASS, 'CLASS'], [T_WHITESPACE, "\r\n"],
            [T_ABSTRACT, 'abstract'], [T_WHITESPACE, "\r\n  "],
            [T_STRING, 'MultiUse'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_MINUS, '-'], [T_LNUMBER, '1'], [T_WHITESPACE, '  '],
            [T_COMMENT, "//True\r\n"],
            [T_CLONE, 'clone'], [T_WHITESPACE, "\r\n"],
            [T_STRING, 'Attribute'], [T_WHITESPACE, ' '],
            [T_STRING, 'VB_Name'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"Test"'], [T_WHITESPACE, "\r\n\r\n"],
            [T_STRING, 'Option'], [T_WHITESPACE, ' '],
            [T_STRING, 'Explicit'], [T_WHITESPACE, "\r\n\r\n"],
            [T_COMMENT, "// Class: Test\r\n"],
            [T_COMMENT, "// A test class.\r\n"],
            [T_IMPLEMENTS, 'Implements'], [T_WHITESPACE, ' '],
            [T_STRING, 'iTest'], [T_WHITESPACE, "\r\n\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'String'], [T_WHITESPACE, "\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_STRING, 'oSQL'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'oObject'], [T_WHITESPACE, "\r\n\r\n"],
            [T_COMMENT, "// Function: Foo\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'Function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Foo'], [T_OPEN_PARENTHESIS, '('],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Double'], [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Boolean'], [T_WHITESPACE, "\r\n    "],
            [T_WHILE, 'While'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_IS_IDENTICAL, '==='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '2'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_PLUS, '+'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '1'], [T_WHITESPACE, "\r\n    "],
            [T_STATIC, 'static'], [T_WHITESPACE, "\r\n    "],
            [T_SWITCH, 'switch'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, "\r\n        "],
       //     [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_CASE, 'Case'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_IS_IDENTICAL, '==='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '3'], [T_WHITESPACE, "\r\n            "],
            [T_STRING, 'Foo'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '3'], [T_WHITESPACE, "\r\n        "],
    //        [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_DEFAULT, 'default'], [T_WHITESPACE, ' '],
     //       [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
       //     [T_ELSE, 'else'], [T_WHITESPACE, ' '],
       //     [T_OPEN_CURLY_BRACKET, '{'], [T_WHITESPACE, "\r\n            "],
            [T_STRING, 'Foo'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '4'], [T_WHITESPACE, "\r\n    "],
            [T_YIELD, 'yield'], [T_WHITESPACE, "\r\n"],
            [T_ENDDECLARE, 'enddeclare'], [T_WHITESPACE, "\r\n\r\n"],
            [T_COMMENT, "// Function: Bar\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Bar'], [T_OPEN_PARENTHESIS, '('], [T_STRING, 'Optional'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'String'], [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, "\r\n    "],
            [T_IF, 'if'], [T_WHITESPACE, ' '],
            [T_OPEN_PARENTHESIS, '('], [T_WHITESPACE, ' '], [T_BOOLEAN_NOT, '!'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"somevalue"'], [T_WHITESPACE, ' '],
            [T_LOGICAL_AND, 'And'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_GREATER_THAN, '>'], [T_WHITESPACE, ' '],
            [T_DNUMBER, '2.6'], [T_WHITESPACE, ' '],
            [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_OPEN_CURLY_BRACKET, '{'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'iDoSomething'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '5'], [T_WHITESPACE, "\r\n    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_ELSEIF, 'elseif'], [T_WHITESPACE, ' '],
            [T_OPEN_PARENTHESIS, '('], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"something else"'], [T_WHITESPACE, ' '],
            [T_LOGICAL_OR, 'Or'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"Something Else"'], [T_WHITESPACE, ' '],
            [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_OPEN_CURLY_BRACKET, '{'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'iDoSomethong'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_WHITESPACE, "\r\n    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_ELSE, 'else'], [T_WHITESPACE, ' '],
            [T_OPEN_CURLY_BRACKET, '{'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'iDoSomething'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '7'], [T_WHITESPACE, "\r\n    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, "\r\n"],
            [T_ENDDECLARE, 'enddeclare'], [T_WHITESPACE, "\r\n\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Let'], [T_WHITESPACE, ' '], [T_OPEN_PARENTHESIS, '('],
            [T_STRING, 'Baz'], [T_CLOSE_PARENTHESIS, ')'],[T_WHITESPACE, "\r\n    "],
            [T_STRING, 'oSQL'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_STRING, 'Baz'], [T_WHITESPACE, "\r\n    "],
            [T_DO, 'Do'], [T_WHITESPACE, ' '],
            [T_WHILE, 'While'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_WHITESPACE, ' '],
            [T_GREATER_THAN, '>'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '7'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'Bar'], [T_OPEN_PARENTHESIS, '('], [T_LNUMBER, '2'],
            [T_CLOSE_PARENTHESIS, ')'],[T_WHITESPACE, "\r\n    "],
            [T_TRAIT, 'trait'], [T_WHITESPACE, "\r\n"],
            [T_ENDDECLARE, 'enddeclare'], [T_WHITESPACE, "\r\n\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'function'], [T_WHITESPACE, ' '],
            [T_STRING, 'pSub'], [T_WHITESPACE, ' '],
            [T_OPEN_PARENTHESIS, '('], [T_CLOSE_PARENTHESIS, ')'],[T_WHITESPACE, "\r\n    "],
            [T_FOR, 'For'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '1'], [T_WHITESPACE, ' '],
            [T_STRING, 'To'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'Lib'], [T_OBJECT_OPERATOR, '->'],
            [T_STRING, 'Save'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_WHITESPACE, "\r\n    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_WHITESPACE, "\r\n    "],
            [T_FOREACH, 'foreach'], [T_WHITESPACE, ' '],
            [T_STRING, 'element'], [T_WHITESPACE, ' '],
            [T_STRING, 'In'], [T_WHITESPACE, ' '],
            [T_STRING, 'vArray'], [T_WHITESPACE, "\r\n        "],
            [T_STRING, 'Lib2'], [T_OBJECT_OPERATOR, '->'],
            [T_STRING, 'Read'], [T_WHITESPACE, "\r\n    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, "\r\n"],
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
