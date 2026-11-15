---
title: "method.override"
shortDescription: "Method has the #[Override] attribute but does not override any parent method."
ignorable: false
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	#[\Override]
	public function doSomething(): void
	{
	}
}
```

## Why is it reported?

A method has the `#[\Override]` attribute, but it does not actually override any method from a parent class or implemented interface. The `#[\Override]` attribute (introduced in PHP 8.3) is meant to signal that a method is intentionally overriding a parent method. If no parent method exists, the attribute is incorrect and the error is reported to help catch typos in method names or incorrect class hierarchies.

The same check applies to property hooks (PHP 8.4+). A `get` or `set` hook with the `#[\Override]` attribute has to override a hook of the same kind from a parent class or an interface. A property with storage inherited from the parent class counts as having a `get` hook, and also a `set` hook unless it is `readonly`:

```php
<?php declare(strict_types = 1);

class Foo
{
	public int $prop {
		#[\Override]
		get => 1;
	}
}
```

## How to fix it

Remove the `#[\Override]` attribute if the method is not intended to override a parent method, or fix the class hierarchy so that the parent method exists.

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	#[\Override]
 	public function doSomething(): void
 	{
 	}
 }
```

Or ensure the class extends the correct parent:

```diff-php
 <?php declare(strict_types = 1);

-class Foo
+class Foo extends Bar
 {
 	#[\Override]
 	public function doSomething(): void
 	{
 	}
 }
```
