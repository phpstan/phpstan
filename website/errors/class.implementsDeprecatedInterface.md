---
title: "class.implementsDeprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface
{
	public function doSomething(): void;
}

class Foo implements OldInterface
{
	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

A class implements an interface that has been marked as `@deprecated`. Implementing deprecated interfaces ties your class to contracts that are planned for removal.

In the example above, class `Foo` implements `OldInterface`, which is deprecated.

## How to fix it

Replace the deprecated interface with its recommended replacement:

```diff-php
 <?php declare(strict_types = 1);

-class Foo implements OldInterface
+class Foo implements NewInterface
 {
 	public function doSomething(): void
 	{
 	}
 }
```
