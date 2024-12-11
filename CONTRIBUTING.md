# Contributing

## Developing on the RobloxAPI extension

### Installing dependencies

1. install nodejs, npm, and PHP composer
2. change to the extension's directory
3. `npm install`
4. `composer install`

### Running tests

#### PHP unit tests

0. If you are running a MediaWiki docker container, go to the container's directory and run
   `docker compose exec mediawiki bash` to get a shell in the container.
2. Run one of the following commands:
    - `composer phpunit:entrypoint -- --group RobloxAPI` to run all tests for the extension
    - `composer phpunit:entrypoint -- extensions/RobloxAPI/tests/phpunit/unit/<file>` to run all tests in a specific
      file
    - `composer phpunit:entrypoint -- extensions/RobloxAPI/tests/phpunit/unit/<folder>` to run all tests in a specific
      folder

#### Parser tests

0. If you are running a MediaWiki docker container, go to the container's directory and run
   `docker compose exec mediawiki bash` to get a shell in the container.
1. Run `php tests/parser/parserTests.php --file=extensions/RobloxAPI/tests/parser/parserTests.txt` to run the parser
   tests.

#### JavaScript tests

Running `npm test`will run automated code checks.

### Code Style

The PHP part of this project follows
the [MediaWiki coding conventions](https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP). The code formatting is
enforced by the CI pipeline which runs phan before a PR can be merged.
