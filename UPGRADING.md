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

### Changed `TypeSpecifier::create()` and `SpecifiedTypes` constructor parameters

[`PHPStan\Analyser\TypeSpecifier::create()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.TypeSpecifier.html#_create) now accepts (all parameters are required):

* `Expr $expr`
* `Type $type`
* `TypeSpecifierContext $context`
* `Scope $scope`

If you want to change `$overwrite` or `$rootExpr` (previous parameters also used to be accepted by this method), call `setAlwaysOverwriteTypes()` and `setRootExpr()` on [`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) (object returned by `TypeSpecifier::create()`). These methods return a new object (SpecifiedTypes is immutable).

[`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) constructor now accepts:

* `array $sureTypes`
* `array $sureNotTypes`

If you want to change `$overwrite` or `$rootExpr` (previous parameters also used to be accepted by the constructor), call `setAlwaysOverwriteTypes()` and `setRootExpr()`. These methods return a new object (SpecifiedTypes is immutable).

### Changed `TypeSpecifier::specifyTypesInCondition()`

This method now longer accepts `Expr $rootExpr`. If you want to change it, call `setRootExpr()` on [`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) (object returned by `TypeSpecifier::specifyTypesInCondition()`). `setRootExpr()` method returns a new object (SpecifiedTypes is immutable).

### Minor backward compatibility breaks

* Parameter `$callableParameters` of [`MutatingScope::enterAnonymousFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterAnonymousFunction) and [`enterArrowFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterArrowFunction) made required
