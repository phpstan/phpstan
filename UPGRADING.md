This document is a work in progress.

Upgrading from PHPStan 1.x to 2.0
=================================

## PHP version requirements

PHPStan now requires PHP 7.4 or newer to run.

## Upgrading guide for end users

TODO

## Upgrading guide for extension developers

### PHPStan now uses nikic/php-parser v5

See [UPGRADING](https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md) guide for PHP-Parser.

The most notable change is how `throw` statement is represented. Previously, `throw` statements like `throw $e;` were represented using the `Stmt\Throw_` class, while uses inside other expressions (such as `$x ?? throw $e`) used the `Expr\Throw_` class.

Now, `throw $e;` is represented as a `Stmt\Expression` that contains an `Expr\Throw_`. The
`Stmt\Throw_` class has been removed.

### PHPStan now uses phpstan/phpdoc-parser v2

See [UPGRADING](https://github.com/phpstan/phpdoc-parser/blob/2.0.x/UPGRADING.md) guide for phpstan/phpdoc-parser.

TODO
