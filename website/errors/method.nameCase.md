---
title: "method.nameCase"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public function fooBar(): void
	{
	}
}

function (Foo $foo): void {
	$foo->foobar(); // error: Call to method Foo::fooBar() with incorrect case: foobar
};
```

## Why is it reported?

While PHP method names are case-insensitive and `foobar()` will work at runtime, using a different case than the method declaration is inconsistent and makes code harder to read and maintain. This check helps enforce consistent casing across the codebase.

This error is reported only when the [`checkFunctionNameCase`](/config-reference#checkfunctionnamecase) option is enabled.

## How to fix it

Use the exact same casing as in the method declaration.

```diff-php
 function (Foo $foo): void {
-	$foo->foobar();
+	$foo->fooBar();
 };
```
