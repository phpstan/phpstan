---
title: "method.missingOverride"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function doFoo(): void
	{
	}
}

class Bar extends Foo
{
	public function doFoo(): void // ERROR: Method Bar::doFoo() overrides method Foo::doFoo() but is missing the #[\Override] attribute.
	{
	}
}
```

## Why is it reported?

A method overrides a parent method but does not have the `#[\Override]` attribute. The `#[\Override]` attribute (introduced in PHP 8.3) explicitly marks methods that are intended to override a parent method. When required, it ensures that overriding methods are clearly documented and helps catch accidental breakage if the parent method is renamed or removed.

This check is controlled by the `checkMissingOverrideMethodAttribute` configuration option.

## How to fix it

Add the `#[\Override]` attribute to the method:

```diff-php
 <?php declare(strict_types = 1);

 class Bar extends Foo
 {
+	#[\Override]
 	public function doFoo(): void
 	{
 	}
 }
```
