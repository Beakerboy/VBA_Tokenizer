<?php

namespace PHP_CodeSniffer\Standards\VBA\Sniffs\WhiteSpace;

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
}//end class
