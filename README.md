# VBA_Tokenizer
A PHP project to tokenize VBA files.
Copy the files to PHP-CodeSniffer, go to the VBA source directory and type:

    phpcs --extensions=cls/vba,bas/vba --standard=VBA .
    
To run tests on Travis-CI, create a composer.json file with the following:

    {
      "require-dev": {
        "php": ">=7.0.0",
        "squizlabs/php_codesniffer": "2.*"
      },
    }

Then add the following to your .travis.yml file:

