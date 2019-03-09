<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once(__DIR__ . '/../../../autoload.php');
}
require_once(__DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php');
include(__DIR__ . '/../vendor/squizlabs/php_codesniffer/src/Util/Tokens.php');
include(__DIR__ . '/../src/Tokenizers/VBANew.php');
