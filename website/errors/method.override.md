---
title: "method.override"
ignorable: true
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
