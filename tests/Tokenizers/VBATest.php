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
            [T_STRING, 'VERSION'], [T_WHITESPACE, ' '],
            [T_DNUMBER, '1.0'], [T_WHITESPACE, ' '],
            [T_CLASS, 'CLASS'], [T_EOL, "\r\n"],
            [T_BEGIN, 'BEGIN'], [T_EOL, "\r\n"],
            [T_WHITESPACE, "  "], [T_STRING, 'MultiUse'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_MINUS, '-'], [T_LNUMBER, '1'], [T_WHITESPACE, '  '],
            [T_COMMENT, "'True"], [T_EOL, "\r\n"],
            [T_END, 'END'], [T_EOL, "\r\n"],
            [T_ATTRIBUTE, 'Attribute'], [T_WHITESPACE, ' '],
            [T_STRING, 'VB_Name'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"Test"'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_OPTION, 'Option'], [T_WHITESPACE, ' '],
            [T_STRING, 'Explicit'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_COMMENT, "' Class: Test"], [T_EOL, "\r\n"],
            [T_COMMENT, "' A test class."], [T_EOL, "\r\n"],
            [T_IMPLEMENTS, 'Implements'], [T_WHITESPACE, ' '],
            [T_STRING, 'iTest'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'String'], [T_EOL, "\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_STRING, 'oSQL'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'oObject'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_COMMENT, "' Function: Foo"], [T_EOL, "\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_FUNCTION, 'Function'], [T_WHITESPACE, ' '],
            [T_STRING, 'Foo'], [T_OPEN_PARENTHESIS, '('],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Double'], [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'Boolean'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_WHILE, 'While'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_IS_IDENTICAL, '==='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '2'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_PLUS, '+'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '1'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '], [T_WEND, 'Wend'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_SELECT, 'Select'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_CASE, 'Case'], [T_WHITESPACE, ' '],
            [T_STRING, 'iVariable'], [T_WHITESPACE, ' '],
            [T_IS, 'Is'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '3'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '            '],
            [T_STRING, 'Foo'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '3'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_CASE_ELSE, 'Case Else'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '            '],
            [T_STRING, 'Foo'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '4'], [T_WHITESPACE, "\r\n    "],
            [T_END_SELECT, 'End Select'], [T_EOL, "\r\n"],
            [T_END_FUNCTION, 'End Function'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_COMMENT, "' Function: Bar\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_SUB, 'Sub'], [T_WHITESPACE, ' '],
            [T_STRING, 'Bar'], [T_OPEN_PARENTHESIS, '('], [T_STRING, 'Optional'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_AS, 'As'], [T_WHITESPACE, ' '],
            [T_STRING, 'String'], [T_CLOSE_PARENTHESIS, ')'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_IF, 'If'], [T_WHITESPACE, ' '],
            [T_OPEN_PARENTHESIS, '('], [T_WHITESPACE, ' '], [T_BOOLEAN_NOT, '!'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_CONSTANT_ENCAPSED_STRING, '"somevalue"'], [T_WHITESPACE, ' '],
            [T_LOGICAL_AND, 'And'], [T_WHITESPACE, ' '],
            [T_STRING, 'sTest'], [T_WHITESPACE, ' '],
            [T_GREATER_THAN, '>'], [T_WHITESPACE, ' '],
            [T_DNUMBER, '2.6'], [T_WHITESPACE, ' '],
            [T_CLOSE_PARENTHESIS, ')'], [T_WHITESPACE, ' '],
            [T_THEN, 'Then'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_STRING, 'iDoSomething'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '5'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
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
            [T_OPEN_CURLY_BRACKET, '{'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_STRING, 'iDoSomethong'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_WHITESPACE, ' '],
            [T_ELSE, 'Else'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_STRING, 'iDoSomething'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '7'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_ENDIF, 'End If'], [T_EOL, "\r\n"],
            [T_END_SUB, 'End Sub'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_PUBLIC, 'Public'], [T_WHITESPACE, ' '],
            [T_PROPERTY, 'Property'], [T_WHITESPACE, ' '],
            [T_STRING, 'Let'], [T_WHITESPACE, ' '], [T_OPEN_PARENTHESIS, '('],
            [T_STRING, 'Baz'], [T_CLOSE_PARENTHESIS, ')'],[T_WHITESPACE, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_STRING, 'oSQL'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_STRING, 'Baz'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_DO, 'Do'], [T_WHITESPACE, ' '],
            [T_WHILE, 'While'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_WHITESPACE, ' '],
            [T_GREATER_THAN, '>'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '7'], [T_EOL, "\r\n"],
            [T_WHITESPACE, '        '],
            [T_STRING, 'Bar'], [T_OPEN_PARENTHESIS, '('], [T_LNUMBER, '2'],
            [T_CLOSE_PARENTHESIS, ')'],[T_EOL, "\r\n"],
            [T_WHITESPACE, '    '],
            [T_LOOP, 'Loop'], [T_EOL, "\r\n"],
            [T_END_PROPERTY, 'End Property'], [T_EOL, "\r\n"],
            [T_EOL, "\r\n"],
            [T_PRIVATE, 'Private'], [T_WHITESPACE, ' '],
            [T_SUB, 'Sub'], [T_WHITESPACE, ' '],
            [T_STRING, 'pSub'], [T_WHITESPACE, ' '],
            [T_OPEN_PARENTHESIS, '('], [T_CLOSE_PARENTHESIS, ')'], [T_EOL, "\r\n"],
            [T_WHITESPACE, "    "], [T_FOR, 'For'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_WHITESPACE, ' '],
            [T_EQUAL, '='], [T_WHITESPACE, ' '],
            [T_LNUMBER, '1'], [T_WHITESPACE, ' '],
            [T_STRING, 'To'], [T_WHITESPACE, ' '],
            [T_LNUMBER, '6'], [T_EOL, "\r\n"],
            [T_WHITESPACE, "        "], [T_STRING, 'Lib'], [T_OBJECT_OPERATOR, '->'],
            [T_STRING, 'Save'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_EOL, "\r\n"],
            [T_WHITESPACE, "    "], [T_NEXT, 'Next'], [T_WHITESPACE, ' '],
            [T_STRING, 'i'], [T_WHITESPACE, "\r\n    "],
            [T_FOREACH, 'foreach'], [T_WHITESPACE, ' '],
            [T_STRING, 'element'], [T_WHITESPACE, ' '],
            [T_STRING, 'In'], [T_WHITESPACE, ' '],
            [T_STRING, 'vArray'], [T_WHITESPACE, "        "],
            [T_STRING, 'Lib2'], [T_OBJECT_OPERATOR, '->'],
            [T_STRING, 'Read'], [T_EOL, "\r\n"], [T_WHITESPACE, "    "],
            [T_CLOSE_CURLY_BRACKET, '}'], [T_EOL, "\r\n"],
            [T_END_SUB, 'End Sub'], [T_EOL, "\r\n"],
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
