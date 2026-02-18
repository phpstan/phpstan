---
title: "phpstanPlayground.traitUnused"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

trait MyTrait
{
	public function hello(): string
	{
		return 'Hello';
	}
}
```

## Why is it reported?

PHPStan analyses traits only through the classes that use them. A trait that is declared but never used with a `use` statement in any class is not analysed at all, meaning bugs inside it remain undetected.

This error is specific to the PHPStan Playground, where the analysed code is limited to what is pasted. It warns that the trait will not be covered by the analysis.

Learn more: [How PHPStan Analyses Traits](/blog/how-phpstan-analyses-traits)

## How to fix it

Add a class that uses the trait so PHPStan can analyse it:

```diff-php
 trait MyTrait
 {
 	public function hello(): string
 	{
 		return 'Hello';
 	}
 }

+class MyClass
+{
+	use MyTrait;
+}
```
