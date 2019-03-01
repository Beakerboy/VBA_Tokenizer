# VBA_Tokenizer
A VBA Tokenizer and Coding Standard for PHP_CodeSniffer

[![Build Status](https://travis-ci.org/Beakerboy/VBA_Tokenizer.svg?branch=master)](https://travis-ci.org/Beakerboy/VBA_Tokenizer)
[![Coverage Status](https://coveralls.io/repos/github/Beakerboy/VBA_Tokenizer/badge.svg?branch=master)](https://coveralls.io/github/Beakerboy/VBA_Tokenizer?branch=master)

## Installation
Copy the project files to the corresponding PHP-CodeSniffer directories

## Running
Go to the VBA source directory and type:

    phpcs --extensions=cls/vba,bas/vba --standard=VBA .

## Travis-CI Instructions
To run tests on Travis-CI, create a composer.json file with the following:

    {
      "require-dev": {
        "php": ">=7.0.0",
        "squizlabs/php_codesniffer": "2.*"
      },
    }

Then add the following to your .travis.yml file:

    language: vba
    install:
      - composer install
    script:
      - vendor/bin/phpcs --extensions=cls/vba,bas/vba --standard=vendor/beakerboy/vba_tokenizer/src/Standards/VBA $TRAVIS_BUILD_DIR/src
