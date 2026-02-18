---
title: "parameter.deprecatedTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewHelper instead */
trait OldHelper
{
}

class Foo
{
	use OldHelper;
}

function process(Foo $foo): void
{
	// ...
}
```

## Why is it reported?

A function or method parameter has a type declaration that references a class using a deprecated trait. The trait has been marked with a `@deprecated` PHPDoc tag, indicating it is scheduled for removal or replacement.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) package.

## How to fix it

Replace the deprecated trait with its recommended replacement in the class:

```diff-php
 class Foo
 {
-	use OldHelper;
+	use NewHelper;
 }
```

Or update the parameter type to use a class that does not depend on the deprecated trait:

```diff-php
-function process(Foo $foo): void
+function process(Bar $bar): void
 {
 	// ...
 }
```
