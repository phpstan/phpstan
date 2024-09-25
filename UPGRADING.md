This document is a work in progress.

Upgrading from PHPStan 1.x to 2.0
=================================

## PHP version requirements

PHPStan now requires PHP 7.4 or newer to run.

## Upgrading guide for end users

The best way do get ready for upgrade to PHPStan 2.0 is to update to the **latest PHPStan 1.12 release**
and enable [**Bleeding Edge**](https://phpstan.org/blog/what-is-bleeding-edge). This will enable the new rules and behaviours that 2.0 turns on for all users.

Also make sure to install and enable [`phpstan/phpstan-deprecation-rules`](https://github.com/phpstan/phpstan-deprecation-rules).

Once you get to a green build with no deprecations showed on latest PHPStan 1.12.x with Bleeding Edge enabled, you can update all your related PHPStan dependencies to 2.0 in `composer.json`:

```json
"require-dev": {
    "phpstan/phpstan": "^2.0",
    "phpstan/phpstan-deprecation-rules": "^2.0",
    "phpstan/phpstan-doctrine": "^2.0",
    "phpstan/phpstan-nette": "^2.0",
    "phpstan/phpstan-phpunit": "^2.0",
    "phpstan/phpstan-strict-rules": "^2.0",
    "phpstan/phpstan-symfony": "^2.0",
    "phpstan/phpstan-webmozart-assert": "^2.0",
    ...
}
```

Don't forget to update [3rd party PHPStan extensions](https://phpstan.org/user-guide/extension-library) as well.

After changing your `composer.json`, run `composer update 'phpstan/*' -W`.

It's up to you whether you go through the new reported errors or if you just put them all to the [baseline](https://phpstan.org/user-guide/baseline) ;) Everyone who's on PHPStan 1.12 should be able to upgrade to PHPStan 2.0.

### Removed option `checkMissingIterableValueType`

It's strongly recommended to add the missing array typehints.

If you want to continue ignoring missing typehints from arrays, add `missingType.iterableValue` error identifier to your `ignoreErrors`:

```neon
parameters:
	ignoreErrors:
		-
			identifier: missingType.iterableValue
```

### Removed option `checkGenericClassInNonGenericObjectType`

It's strongly recommended to add the missing generic typehints.

If you want to continue ignoring missing typehints from generics, add `missingType.generics` error identifier to your `ignoreErrors`:

```neon
parameters:
	ignoreErrors:
		-
			identifier: missingType.generics
```

### Removed `checkAlwaysTrue*` options

These options have been removed because PHPStan now always behaves as if these were set to `true`:

* `checkAlwaysTrueCheckTypeFunctionCall`
* `checkAlwaysTrueInstanceof`
* `checkAlwaysTrueStrictComparison`
* `checkAlwaysTrueLooseComparison`

### Removed option `excludes_analyse`

It has been replaced with [`excludePaths`](https://phpstan.org/user-guide/ignoring-errors#excluding-whole-files).

### Paths in `excludePaths` and `ignoreErrors` have to be a valid file path or a fnmatch pattern

If you are excluding a file path that might not exist but you still want to have it in `excludePaths`, append `(?)`:

```neon
parameters:
	excludePaths:
		- tests/*/data/*
		- src/broken
		- node_modules (?) # optional path, might not exist
```

If you have the same situation in `ignoreErrors` (ignoring an error in a path that might not exist), use `reportUnmatchedIgnoredErrors: false`.

```neon
parameters:
	reportUnmatchedIgnoredErrors: false
```

Appending `(?)` in `ignoreErrors` is not supported.

### Minor backward compatibility breaks

* Removed unused config parameter `cache.nodesByFileCountMax`
* Removed unused config parameter `memoryLimitFile`
* Removed unused feature toggle `disableRuntimeReflectionProvider`

## Upgrading guide for extension developers

### PHPStan now uses nikic/php-parser v5

See [UPGRADING](https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md) guide for PHP-Parser.

The most notable change is how `throw` statement is represented. Previously, `throw` statements like `throw $e;` were represented using the `Stmt\Throw_` class, while uses inside other expressions (such as `$x ?? throw $e`) used the `Expr\Throw_` class.

Now, `throw $e;` is represented as a `Stmt\Expression` that contains an `Expr\Throw_`. The
`Stmt\Throw_` class has been removed.

### PHPStan now uses phpstan/phpdoc-parser v2

See [UPGRADING](https://github.com/phpstan/phpdoc-parser/blob/2.0.x/UPGRADING.md) guide for phpstan/phpdoc-parser.

### Returning plain strings as errors no longer supported, use RuleErrorBuilder

Identifiers are also required in custom rules.

Learn more: [Using RuleErrorBuilder to enrich reported errors in custom rules](https://phpstan.org/blog/using-rule-error-builder)

Before:

```php
return ['My error'];
```

After:

```php
return [
    RuleErrorBuilder::mesage('My error')
        ->identifier('my.error')
        ->build(),
];
```

### Deprecate various `instanceof *Type` in favour of new methods on `Type` interface

Learn more: [Why Is instanceof *Type Wrong and Getting Deprecated?](https://phpstan.org/blog/why-is-instanceof-type-wrong-and-getting-deprecated)

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

### Node attributes `parent`, `previous`, `next` are no longer available

Learn more: https://phpstan.org/blog/preprocessing-ast-for-custom-rules

### Removed config parameter `scopeClass`

As a replacement you can implement [`PHPStan\Type\ExpressionTypeResolverExtension`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.ExpressionTypeResolverExtension.html) interface instead and register it as a service.

### Minor backward compatibility breaks

* Parameter `$callableParameters` of [`MutatingScope::enterAnonymousFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterAnonymousFunction) and [`enterArrowFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterArrowFunction) made required
* Parameter `StatementContext $context` of [`NodeScopeResolver::processStmtNodes()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.NodeScopeResolver.html#_processStmtNodes) made required
* ClassPropertiesNode - remove `$extensions` parameter from [`getUninitializedProperties()`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.ClassPropertiesNode.html#_getUninitializedProperties)
* `Type::getSmallerType()`, `Type::getSmallerOrEqualType()`, `Type::getGreaterType()`, `Type::getGreaterOrEqualType()`, `Type::isSmallerThan()`, `Type::isSmallerThanOrEqual()` now require [`PhpVersion`](https://apiref.phpstan.org/2.0.x/PHPStan.Php.PhpVersion.html) as argument.
* `CompoundType::isGreaterThan()`, `CompoundType::isGreaterThanOrEqual()` now require [`PhpVersion`](https://apiref.phpstan.org/2.0.x/PHPStan.Php.PhpVersion.html) as argument.
