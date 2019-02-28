# VBA_Tokenizer
A VBA Tokenizer and Coding Standard for PHP_CodeSniffer

[![Build Status](https://travis-ci.org/Beakerboy/VBA_Tokenizer.svg?branch=master)](https://travis-ci.org/Beakerboy/VBA_Tokenizer)

## Installation
Copy the project files and move them to the corresponding PHP-CodeSniffer directories. Then sniff your files with:

## Running
Go to the VBA source directory and type:

    phpcs --extensions=cls/vba,bas/vba --standard=VBA .

## Travis-CI Instructions
To run tests on Travis-CI, create a composer.json file with the following:

    {
      "require-dev": {
        "beakerboy/vba_tokenizer": "dev-master"
      },
      "repositories": [
        {
            "type": "git",
            "url": "https://github.com/Beakerboy/VBA_Tokenizer"
        }
    }

Then add the following to your .travis.yml file:

    language: vba
    install:
      - composer install
    script:
      - vendor/bin/phpcs --extensions=cls/vba,bas/vba --standard=vendor/beakerboy/vba_tokenizer/src/Standards/VBA $TRAVIS_BUILD_DIR/src
