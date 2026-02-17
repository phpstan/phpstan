---
title: "property.unused"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	private int $unused;

	public function __construct()
	{
	}
}
```

## Why is it reported?

A private property that is never read and never written is dead code. It occupies space in the class definition and in every instance, but serves no purpose. This usually indicates leftover code from a refactoring or a property that was declared but never integrated into the class logic.

## How to fix it

Remove the unused property if it is no longer needed:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	private int $unused;
-
 	public function __construct()
 	{
 	}
 }
```

Or start using the property if it was intended to be part of the class:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
 	private int $unused;

-	public function __construct()
+	public function __construct(int $value)
 	{
+		$this->unused = $value;
+	}
+
+	public function getValue(): int
+	{
+		return $this->unused;
 	}
 }
```

If the property is read or written through a mechanism not visible to PHPStan (such as reflection, serialization, or a custom magic method), a [custom extension](https://phpstan.org/developing-extensions/always-read-written-properties) can be used to inform PHPStan about the usage.
