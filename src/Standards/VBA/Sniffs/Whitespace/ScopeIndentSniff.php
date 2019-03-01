<?php

namespace VBA_Tokenizer\Standards\VBA\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace\ScopeIndentSniff as GenericScopeIndentSniff;

class ScopeIndentSniff extends GenericScopeIndentSniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'VBA',
    ];

    /**
     * Any scope openers that should not cause an indent.
     *
     * @var int[]
     */
    protected $nonIndentingScopes = [T_SWITCH];
}//end class
