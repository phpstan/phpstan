---
title: "consistentConstructor.private"
shortDescription: "Class with @phpstan-consistent-constructor has a private constructor that cannot be overridden."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @phpstan-consistent-constructor */
class Foo
{
	private function __construct()
	{
	}
}
```

## Why is it reported?

The `@phpstan-consistent-constructor` PHPDoc tag tells PHPStan to enforce that all child classes maintain a compatible constructor signature with the parent class. However, a `private` constructor cannot be overridden by child classes at all -- it prevents inheritance of the constructor entirely. Marking a private constructor as consistent is contradictory because child classes cannot call or match the parent constructor.

If the class is also `final`, this error is not reported because final classes cannot have child classes.

## How to fix it

Change the constructor visibility to `protected` or `public` so child classes can inherit and override it:

```diff-php
 <?php declare(strict_types = 1);

 /** @phpstan-consistent-constructor */
 class Foo
 {
-	private function __construct()
+	protected function __construct()
 	{
 	}
 }
```

Alternatively, if the class is not meant to be extended, make it `final` and remove the `@phpstan-consistent-constructor` tag:

```diff-php
 <?php declare(strict_types = 1);

-/** @phpstan-consistent-constructor */
-class Foo
+final class Foo
 {
 	private function __construct()
 	{
 	}
 }
```
